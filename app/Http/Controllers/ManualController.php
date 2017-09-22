<?php

namespace App\Http\Controllers;

use App\InfraItem;
use DB;
use TCPDF;
use SnappyImage;
use Carbon\Carbon;
use App\ManualSale;
use App\OrderDogAPI;
use Illuminate\Http\Request;
use App\Jobs\ApplySalePrice;

class ManualController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->clearFormCookies();

        $filter = session('filter');

        if(empty($filter)) {
            $filter = 'f_all';
        }

        $items = ManualSale::orderBy('brand', 'asc')
                           ->orderBy('upc', 'asc');
//f_img_bw
        switch($filter) {
            case 'f_all':
                $items = $items->where('expires', '>=', Carbon::now());
                break;
            case 'f_processed':
                $items = $items->where('processed', true)
                               ->where('expires', '>=', Carbon::now());;
                break;
            case 'f_ready_to_print':
                $items = $items->where('imaged', true)
                               ->where('printed', false)
                               ->where('expires', '>=', Carbon::now());;
                break;
            case 'f_img_bw':
                $items = $items->where('imaged', true)
                               ->where('color', false)
                               ->where('expires', '>=', Carbon::now());;
                break;
            case 'f_img_color':
                $items = $items->where('imaged', true)
                               ->where('color', true)
                               ->where('expires', '>=', Carbon::now());;
                break;
            case 'f_printed':
                $items = $items->where('printed', true)
                               ->where('expires', '>=', Carbon::now());;
                break;
            case 'f_flagged':
                $items = $items->where('flags', '!=', false)
                               ->where('expires', '>=', Carbon::now());;
                break;
            case 'f_expired':
                $items = $items->where('expires', '<', Carbon::now());
                break;
        }

        $items = $items->paginate(100);

        $jobCounts['processing'] = DB::table('jobs')->where('queue', 'processing')->count();
        $jobCounts['imaging']    = DB::table('jobs')->where('queue', 'imaging')->count();

        return view('manual.index', compact('items', 'filter', 'jobCounts'));
    }

    public function process(Request $request)
    {
        if($request->filter) {
            session(['filter' => $request->filter]);
        }
        switch($request->process) {
            case 'add':
                return redirect()->route('manual.create');
                break;
            case 'print':
                $this->printSelectedItems($request);
                break;
            case 'printallbw':
                $this->printAllBWItems($request);
                break;
            case 'printallcolor':
                $this->printAllColorItems($request);
                break;
            case 'delete':
                $this->deleteItems($request);
                break;
        }

        return redirect()->route('manual.index');
    }

    public function create()
    {
        $data['brand']    = session('manual_sale_brand');
        $data['sale_cat'] = session('manual_sale_cat');
        $data['begin']    = session('manual_sale_begin');
        $data['end']      = session('manual_sale_end');

        if(session('manual_sale_color') == 'radioColor') {
            $data['color'] = true;
        } else {
            $data['color'] = false;
        }
        if(session('manual_sale_od_update') == 'radioODNo') {
            $data['ODUpdate'] = false;
        } else {
            $data['ODUpdate'] = true;
        }

        return view('manual.create', compact('data'));
    }

    public function store(Request $request)
    {
        if($request->radioBWColor == 'radioColor') {
            $colorBW = true;
        } else {
            $colorBW = false;
        }
        if($request->radioODUpdate == 'radioODYes') {
            $ODUpdate = true;
        } else {
            $ODUpdate = false;
        }

        $expires = new Carbon($request->sale_end);
        $expires = $expires->addDay();

        $sale = ManualSale::create(['upc'             => $request->previewInputUPC,
                                    'brand'           => $request->previewInputBrand,
                                    'desc'            => $request->previewInputDesc,
                                    'sale_price'      => $request->previewInputSalePrice,
                                    'disp_sale_price' => $request->previewInputDispPrice,
                                    'reg_price'       => $request->previewInputRegPrice,
                                    'savings'         => $request->previewInputSavings,
                                    'sale_cat'        => $request->previewInputSaleCat,
                                    'color'           => $colorBW,
                                    'od_update'       => $ODUpdate,
                                    'processed'       => false,
                                    'imaged'          => false,
                                    'printed'         => false,
                                    'sale_begin'      => $request->sale_begin,
                                    'sale_end'        => $request->sale_end,
                                    'expires'         => $expires]);
        dispatch((new ApplySalePrice($sale))->onQueue('processing'));

        if(isset($request->submitContinue)) {
            session(['manual_sale_brand'     => $request->previewInputBrand]);
            session(['manual_sale_cat'       => $request->previewInputSaleCat]);
            session(['manual_sale_od_update' => $request->radioODUpdate]);
            session(['manual_sale_color'     => $request->radioBWColor]);
            session(['manual_sale_begin'     => $request->sale_begin]);
            session(['manual_sale_end'       => $request->sale_end]);

            return redirect()->route('manual.create');
        } else {
            return redirect()->route('manual.index');
        }
    }

    protected function deleteItems(Request $request)
    {
        foreach($request->checked as $item) {
            ManualSale::find($item)->delete();
        }

        flash()->success('The selected items have been deleted.');
    }

    public function ODQuery($upc)
    {
        $api = new OrderDogAPI;

        $result = $api->quickQuery($upc);

        if($result !== false) {

            return $result;
        }
    }

    public function clearFormCookies()
    {
        session()->forget('manual_sale_brand');
        session()->forget('manual_sale_cat');
        session()->forget('manual_sale_od_update');
        session()->forget('manual_sale_color');
        session()->forget('manual_sale_begin');
        session()->forget('manual_sale_end');
    }

    /**
     * Prepare and print a collection of items.
     *
     * @param \Illuminate\Database\Eloquent\Collection $items
     *
     * @return bool
     */
    protected function printItems($items)
    {
        if(count($items) == 0) {
            flash()->warning('No items are ready to be printed.');

            return false;
        }

        foreach($items as $item) {
            $images[] = storage_path("app/images/manual/$item->id.png");
        }

        $this->printSheet($images);

        foreach($items as $item) {
            $item->print();
        }

        return true;
    }

    /**
     * Loop through and print user selected INFRA items.
     *
     * @param Request $request
     */
    protected function printSelectedItems(Request $request)
    {
        if(!isset($request->checked)) {
            flash()->warning('No items were selected.');
            return;
        }

        $items = ManualSale::whereIn('id', $request->checked)
                           ->where('imaged', true)
                           ->get();

        if($this->printItems($items)) {

            flash()->success('The selected items have been printed.');
        }
    }

    /**
     * Loop through and print all B&W items that are ready to be printed.
     *
     * @param Request $request
     */
    protected function printAllBWItems(Request $request)
    {
        $items = ManualSale::where('imaged', true)
                           ->where('printed', false)
                           ->where('color', false)
                           ->orderBy('brand', 'asc')
                           ->orderBy('id', 'asc')
                           ->get();

        if($this->printItems($items)) {

            flash()->success('All B&amp;W items that were ready to print have been printed.');
        }
    }

    /**
     * Loop through and print all Color items that are ready to be printed.
     *
     * @param Request $request
     */
    protected function printAllColorItems(Request $request)
    {
        $items = ManualSale::where('imaged', true)
                           ->where('printed', false)
                           ->where('color', true)
                           ->orderBy('brand', 'asc')
                           ->orderBy('id', 'asc')
                           ->get();

        if($this->printItems($items)) {

            flash()->success('All color items that were ready to print have been printed.');
        }
    }

    /**
     * Compiles an array of images into a PDF document ready for printing.
     *
     * @param array $images
     */
    protected function printSheet($images)
    {
        $user   = auth()->user();
        $author = $user->name;

        $layout = array(8.5, 11);

        $pdf = new TCPDF('P', 'in', $layout, false, 'UTF-8', false, false);

        $pdf->SetCreator('Suzanne\'s Sales Manager');
        $pdf->SetAuthor($author);
        $pdf->SetTitle('Suzanne\'s Sales Test');

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->SetMargins(0.25, 0.5, 0.25, true);

        $pdf->SetAutoPageBreak(true, 0.5);

        $pdf->setImageScale(300);

        $count = count($images);

        $i = 0;

        while(true) {

            $pdf->AddPage();

            $pdf = $this->addAllCropMarks($pdf);

            $x = 0.25;
            $y = 0.5;

            for($row = 1; $row <= 3; $row++) {

                for($col = 1; $col <= 4; $col++) {

                    if($i <= ($count - 1)) {

                        $pdf->Image($images[$i], $x, $y, 2, 3, 'PNG', '', '', false, 300, '', false, false, 0, false, false, false);

                        $x += 2.0;

                        $i++;
                    } else {

                        break;
                    }
                }

                $x = 0.25;
                $y += 3.5;
            }

            if($i > ($count - 1)) {

                break;
            }
        }

        $now = Carbon::now('America/Chicago');

        $outputName = $now->format('Y-m-d-H-i-s');

        $pdf->Output(($outputName . '.pdf'), 'D');
    }

    /**
     * Adds all crop marks needed for a full page of sale tags.
     *
     * @param TCPDF $pdf
     *
     * @return TCPDF
     */
    protected function addAllCropMarks(TCPDF $pdf)
    {
        $pdf->cropMark(0.25, 0.5, 0.2, 0.2, 'TL');
        $pdf->cropMark(2.25, 0.5, 0.2, 0.2, 'TOP');
        $pdf->cropMark(4.25, 0.5, 0.2, 0.2, 'TOP');
        $pdf->cropMark(6.25, 0.5, 0.2, 0.2, 'TOP');
        $pdf->cropMark(8.25, 0.5, 0.2, 0.2, 'TR');
        $pdf->cropMark(0.25, 3.5, 0.2, 0.2, 'LEFT');
        $pdf->cropMark(0.25, 4.0, 0.2, 0.2, 'LEFT');
        $pdf->cropMark(2.25, 3.75, 0.2, 0.2, 'TOP,BOTTOM');
        $pdf->cropMark(4.25, 3.75, 0.2, 0.2, 'TOP,BOTTOM');
        $pdf->cropMark(6.25, 3.75, 0.2, 0.2, 'TOP,BOTTOM');
        $pdf->cropMark(8.25, 3.5, 0.2, 0.2, 'RIGHT');
        $pdf->cropMark(8.25, 4.0, 0.2, 0.2, 'RIGHT');
        $pdf->cropMark(0.25, 7.0, 0.2, 0.2, 'LEFT');
        $pdf->cropMark(0.25, 7.5, 0.2, 0.2, 'LEFT');
        $pdf->cropMark(2.25, 7.25, 0.2, 0.2, 'TOP,BOTTOM');
        $pdf->cropMark(4.25, 7.25, 0.2, 0.2, 'TOP,BOTTOM');
        $pdf->cropMark(6.25, 7.25, 0.2, 0.2, 'TOP,BOTTOM');
        $pdf->cropMark(8.25, 7.0, 0.2, 0.2, 'RIGHT');
        $pdf->cropMark(8.25, 7.5, 0.2, 0.2, 'RIGHT');
        $pdf->cropMark(0.25, 10.5, 0.2, 0.2, 'BL');
        $pdf->cropMark(2.25, 10.5, 0.2, 0.2, 'BOTTOM');
        $pdf->cropMark(4.25, 10.5, 0.2, 0.2, 'BOTTOM');
        $pdf->cropMark(6.25, 10.5, 0.2, 0.2, 'BOTTOM');
        $pdf->cropMark(8.25, 10.5, 0.2, 0.2, 'BR');

        return $pdf;
    }
}
