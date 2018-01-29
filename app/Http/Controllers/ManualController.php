<?php

namespace App\Http\Controllers;

use DB;
use POS;
use TCPDF;
use SnappyImage;
use Carbon\Carbon;
use App\InfraItem;
use App\ManualSale;
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

        switch($filter) {
            case 'f_all':
                $items = $items->where('expires', '>=', Carbon::now());
                break;
            case 'f_processed':
                $items = $items->where('processed', true)
                               ->where('expires', '>=', Carbon::now());;
                break;
            case 'f_queued':
                $items = $items->where('queued', true)
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
                $items = $items->whereNotNull('flags')
                               ->where('expires', '>=', Carbon::now());;
                break;
            case 'f_flagged_discounted':
                $items = $items->where('flags', 'Item already has discounts');
                break;
            case 'f_flagged_lowprice':
                $items = $items->where('flags', 'Item price is lower than sale price');
                break;
            case 'f_flagged_notfound':
                $items = $items->where('flags', 'Item not found in point of sale system');
                break;
            case 'f_expired':
                $items = $items->where('expires', '<', Carbon::now());
                break;
        }

        if(request()->has('page')) {
            $items = $items->paginate(100);
            session(['page' => $items->currentPage()]);
        } else {
            $page = session('page', 1);
            if($page > 1 && (((float) $items->count()) / ($page - 1.0)) <= 100.0) {
                session(['page' => 1]);
            }
            $items = $items->paginate(100, ['*'], 'page', session('page', 1));
        }

        $queueCounts['bw']    = DB::table('manual_sales')->where('queued', true)->where('color', false)->whereNull('deleted_at')->count();
        $queueCounts['color'] = DB::table('manual_sales')->where('queued', true)->where('color', true)->whereNull('deleted_at')->count();

        $jobCounts['processing'] = DB::table('jobs')->where('queue', 'processing')->count();
        $jobCounts['imaging']    = DB::table('jobs')->where('queue', 'imaging')->count();

        return view('manual.index', compact('items', 'filter', 'queueCounts', 'jobCounts'));
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
            case 'queue':
                $this->queueSelectedItems($request);
                break;
            case 'printbwqueue':
                $this->printBWQueue($request);
                break;
            case 'printcolorqueue':
                $this->printColorQueue($request);
                break;
            case 'reprocess':
                $this->reprocessSelected($request);
                break;
            case 'delete':
                $this->deleteItems($request);
                break;
        }

        return redirect()->route('manual.index');
    }

    public function reprocessSelected(Request $request)
    {
        if(!isset($request->checked)) {
            flash()->warning('No items were selected.');
            return;
        }

        $items = ManualSale::whereIn('id', $request->checked)
                           ->get();

        if($this->reprocessItems($items)) {

            flash()->success('The selected items will be reprocessed.');
        }
    }

    public function reprocessItems($items)
    {
        foreach($items as $item) {
            $item->processed = false;
            $item->imaged = false;
            $item->printed = false;
            $item->flags = null;
            $item->save();

            dispatch((new ApplySalePrice($item))->onQueue('processing'));
        }

        return true;
    }

    public function create()
    {
        $data['brand']    = session('manual_sale_brand');
        $data['sale_cat'] = session('manual_sale_cat');
        $data['begin']    = session('manual_sale_begin');
        $data['no_begin'] = session('manual_sale_no_begin');
        $data['end']      = session('manual_sale_end');
        $data['no_end']   = session('manual_sale_no_end');
        $data['percent']  = session('manual_sale_percent');

        if(session('manual_sale_color') == 'radioColor') {
            $data['color'] = true;
        } else {
            $data['color'] = false;
        }
        if(session('manual_sale_pos_update') == 'radioPOSNo') {
            $data['POSUpdate'] = false;
        } else {
            $data['POSUpdate'] = true;
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

        if($request->radioPOSUpdate == 'radioPOSYes') {

            $POSUpdate = true;

            if(!isset($request->checkNoBegin)) {
                $sale_begin = new Carbon($request->sale_begin);
            } else {
                $sale_begin = null;
            }

            if(!isset($request->checkNoEnd)) {
                $sale_end   = new Carbon($request->sale_end);

                $expires = new Carbon($request->sale_end);
                $expires = $expires->addDay();
            } else {
                $sale_end   = null;

                $expires = Carbon::now('America/Chicago');
                $expires = $expires->addYears(100);
            }
        } else {

            $sale_begin = null;
            $sale_end   = null;

            $expires = Carbon::now('America/Chicago');
            $expires = $expires->addMonth();

            $POSUpdate = false;
        }

        if($request->previewInputSalePrice != '') {
            $salePrice = $request->previewInputSalePrice;
        } else {
            $salePrice = 0.00;
        }

        if($request->previewInputSaleCat != '') {
            $saleCat = $request->previewInputSaleCat;
        } else {
            $saleCat = 'Great Savings';
        }

        $sale = ManualSale::create(['upc'             => $request->previewInputUPC,
                                    'brand'           => $request->previewInputBrand,
                                    'desc'            => $request->previewInputDesc,
                                    'sale_price'      => $salePrice,
                                    'disp_sale_price' => $request->previewInputDispPrice,
                                    'reg_price'       => $request->previewInputRegPrice,
                                    'savings'         => $request->previewInputSavings,
                                    'sale_cat'        => $saleCat,
                                    'color'           => $colorBW,
                                    'pos_update'      => $POSUpdate,
                                    'processed'       => false,
                                    'imaged'          => false,
                                    'printed'         => false,
                                    'sale_begin'      => $sale_begin,
                                    'sale_end'        => $sale_end,
                                    'expires'         => $expires,
                                    'no_begin'        => isset($request->checkNoBegin),
                                    'no_end'          => isset($request->checkNoEnd)]);

        dispatch((new ApplySalePrice($sale))->onQueue('processing'));

        if(isset($request->submitContinue)) {
            session(['manual_sale_brand'      => $request->previewInputBrand]);
            session(['manual_sale_cat'        => $request->previewInputSaleCat]);
            session(['manual_sale_pos_update' => $request->radioPOSUpdate]);
            session(['manual_sale_color'      => $request->radioBWColor]);
            session(['manual_sale_begin'      => $request->sale_begin]);
            session(['manual_sale_end'        => $request->sale_end]);
            session(['manual_sale_no_begin'   => $request->checkNoBegin]);
            session(['manual_sale_no_end'     => $request->checkNoEnd]);
            session(['manual_sale_percent'    => $request->previewInputPercentOff]);

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

    public function POSQuery($upc) // Should probably be refactored over to the AjaxController
    {
        $result = POS::quickQuery($upc);

        if($result !== false) {

            return $result;
        }

        return 'false';
    }

    public function clearFormCookies()
    {
        session()->forget('manual_sale_brand');
        session()->forget('manual_sale_cat');
        session()->forget('manual_sale_pos_update');
        session()->forget('manual_sale_color');
        session()->forget('manual_sale_begin');
        session()->forget('manual_sale_end');
        session()->forget('manual_sale_no_begin');
        session()->forget('manual_sale_no_end');
        session()->forget('manual_sale_percent');
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
            flash()->warning('No items are queued for printing.');

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

    protected function queueItems($items)
    {
        if(count($items) == 0) {
            flash()->warning('No items are ready to be printed.');

            return false;
        }

        foreach($items as $item) {
            $item->queue();
        }

        return true;
    }

    /**
     * Loop through and print user selected INFRA items.
     *
     * @param Request $request
     */
    protected function queueSelectedItems(Request $request)
    {
        if(!isset($request->checked)) {
            flash()->warning('No items were selected.');
            return;
        }

        $items = ManualSale::whereIn('id', $request->checked)
                           ->where('imaged', true)
                           ->get();

        if($this->queueItems($items)) {

            flash()->success('The selected items have been queued for printing.');
        }
    }

    /**
     * Loop through and print all B&W items that are ready to be printed.
     *
     * @param Request $request
     */
    protected function printBWQueue(Request $request)
    {
        $items = ManualSale::where('queued', true)
                           ->where('color', false)
                           ->orderBy('brand', 'asc')
                           ->orderBy('upc', 'asc')
                           ->get();

        if($this->printItems($items)) {

            flash()->success('All B&amp;W items that were queued for printing have been printed.');
        }
    }

    /**
     * Loop through and print all Color items that are ready to be printed.
     *
     * @param Request $request
     */
    protected function printColorQueue(Request $request)
    {
        $items = ManualSale::where('queued', true)
                           ->where('color', true)
                           ->orderBy('brand', 'asc')
                           ->orderBy('upc', 'asc')
                           ->get();

        if($this->printItems($items)) {

            flash()->success('All color items that were queued for printing have been printed.');
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

            $pdf = $this->addAllCropMarks($pdf);

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
        $pdf->SetAlpha(0.25);
        $pdf->cropMark(0.25, 0.5, 0.2, 0.2, 'A,D');
        $pdf->cropMark(2.25, 0.5, 0.2, 0.2, 'A,D');
        $pdf->cropMark(4.25, 0.5, 0.2, 0.2, 'A,D');
        $pdf->cropMark(6.25, 0.5, 0.2, 0.2, 'A,D');
        $pdf->cropMark(8.25, 0.5, 0.2, 0.2, 'A,D');
        $pdf->cropMark(0.25, 3.5, 0.2, 0.2, 'A,D');
        $pdf->cropMark(2.25, 3.5, 0.2, 0.2, 'A,D');
        $pdf->cropMark(4.25, 3.5, 0.2, 0.2, 'A,D');
        $pdf->cropMark(6.25, 3.5, 0.2, 0.2, 'A,D');
        $pdf->cropMark(8.25, 3.5, 0.2, 0.2, 'A,D');
        $pdf->cropMark(0.25, 4.0, 0.2, 0.2, 'A,D');
        $pdf->cropMark(2.25, 4.0, 0.2, 0.2, 'A,D');
        $pdf->cropMark(4.25, 4.0, 0.2, 0.2, 'A,D');
        $pdf->cropMark(6.25, 4.0, 0.2, 0.2, 'A,D');
        $pdf->cropMark(8.25, 4.0, 0.2, 0.2, 'A,D');
        $pdf->cropMark(0.25, 7.0, 0.2, 0.2, 'A,D');
        $pdf->cropMark(2.25, 7.0, 0.2, 0.2, 'A,D');
        $pdf->cropMark(4.25, 7.0, 0.2, 0.2, 'A,D');
        $pdf->cropMark(6.25, 7.0, 0.2, 0.2, 'A,D');
        $pdf->cropMark(8.25, 7.0, 0.2, 0.2, 'A,D');
        $pdf->cropMark(0.25, 7.5, 0.2, 0.2, 'A,D');
        $pdf->cropMark(2.25, 7.5, 0.2, 0.2, 'A,D');
        $pdf->cropMark(4.25, 7.5, 0.2, 0.2, 'A,D');
        $pdf->cropMark(6.25, 7.5, 0.2, 0.2, 'A,D');
        $pdf->cropMark(8.25, 7.5, 0.2, 0.2, 'A,D');
        $pdf->cropMark(0.25, 10.5, 0.2, 0.2, 'A,D');
        $pdf->cropMark(2.25, 10.5, 0.2, 0.2, 'A,D');
        $pdf->cropMark(4.25, 10.5, 0.2, 0.2, 'A,D');
        $pdf->cropMark(6.25, 10.5, 0.2, 0.2, 'A,D');
        $pdf->cropMark(8.25, 10.5, 0.2, 0.2, 'A,D');
        $pdf->SetAlpha(1);

        return $pdf;
    }
}
