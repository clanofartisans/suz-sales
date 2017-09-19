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
        $filter = session('filter');

        if(empty($filter)) {
            $filter = 'f_all';
        }

        $items = ManualSale::orderBy('brand', 'asc')
                           ->orderBy('upc', 'asc');

        switch($filter) {
            case 'f_processed':
                $items = $items->where('processed', true);
                break;
            case 'f_ready_to_print':
                $items = $items->where('imaged', true)
                               ->where('printed', false);
                break;
            case 'f_printed':
                $items = $items->where('printed', true);
                break;
            case 'f_flagged':
                $items = $items->where('flags', '!=', false);
                break;
            case 'f_flagged':
                $items = $items->where('expires', '>=', Carbon::now());
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
                dd('print');
                //$this->printItems($request);
                break;
            case 'printall':
                dd('printall');
                //$this->printAllReadyItems($request);
                break;
            case 'delete':
                dd('delete');
                //$this->approveItems($request);
                break;
        }

        return redirect()->route('manual.index');
        //return redirect()->route('infra.show', ['id' => $id]);
    }

    public function create()
    {
        $data = InfraItem::find(1604);
        return view('manual.create', compact('data'));
    }

    public function store()
    {
        dd(request());
        return;
    }

    public function ODQuery($upc)
    {
        $api = new OrderDogAPI;

        $result = $api->quickQuery($upc);

        if($result !== false) {

            return $result;
        }
    }
/*
    public function show($id)
    {
        $filter = session('filter');

        if(empty($filter)) {
            $filter = 'f_all';
        }

        $infrasheet = InfraSheet::findOrFail($id);

        $items = InfraItem::where('infrasheet_id', $id)
                          ->orderBy('brand', 'asc')
                          ->orderBy('id', 'asc');

        switch($filter) {
            case 'f_approved':
                $items = $items->where('approved', true);
                break;
            case 'f_processed':
                $items = $items->where('processed', true);
                break;
            case 'f_ready_to_print':
                $items = $items->where('imaged', true)
                               ->where('printed', false);
                break;
            case 'f_printed':
                $items = $items->where('printed', true);
                break;
            case 'f_flagged':
                $items = $items->where('flags', '!=', false);
                break;
        }

        $items = $items->paginate(100);

        $jobsCount['processing'] = DB::table('jobs')->where('queue', 'processing')->count();
        $jobsCount['imaging']    = DB::table('jobs')->where('queue', 'imaging')->count();

        return view('infra.show', compact('infrasheet', 'items', 'filter', 'jobsCount'));
    }

    public function uploadStore(Request $request)
    {
        $infrasheet = new InfraSheet;

        $infrasheet->month = $request->upmonth;
        $infrasheet->year  = $request->upyear;

        $infrasheet->filename = $request->upworkbook->store('infrasheets');

        $infrasheet->save();

        $excelDoc = new ExcelDoc($infrasheet->filename);

        $excelDoc->prepareAndSaveItemData($infrasheet->id);

        flash()->success('The INFRA workbook was uploaded successfully.');

        return redirect()->route('infra.index');
    }

    public function approveItems(Request $request)
    {
        foreach($request->checked as $item) {
            InfraItem::find($item)->approve();
        }

        flash()->success('The selected items have been approved.');
    }

    public function printItems(Request $request)
    {
        if(!isset($request->checked)) {
            flash()->warning('No items were selected.');
            return;
        }

        $infraItems = InfraItem::whereIn('id', $request->checked)
                               ->where('imaged', true)
                               ->get();

        if($infraItems->isNotEmpty()) {
            foreach($infraItems as $item) {
                $images[] = realpath(storage_path('app/images')) . '\\' . $item->id . '.png';
            }

            $this->printSheet($images);

            foreach($infraItems as $item) {
                $item->print();
            }

            flash()->success('The selected items have been printed.');
        }
    }

    public function printAllReadyItems(Request $request)
    {
        $items = InfraItem::where('infrasheet_id', $request->infrasheet)
                          ->where('imaged', true)
                          ->where('printed', false)
                          ->orderBy('brand', 'asc')
                          ->orderBy('id', 'asc')
                          ->get();

        if(count($items) == 0) {
            flash()->warning('No items are ready to be printed.');
            return;
        }

        foreach($items as $item) {
            $images[] = realpath(storage_path('app/images')) . '\\' . $item->id . '.png';
        }

        $this->printSheet($images);

        foreach($items as $item) {
            $item->print();
        }

        flash()->success('All items that were ready to print have been printed.');
    }
*/
    /*
            public function testSale()
            {
                return view('infra.testsale');
            }
    */

    /*
        protected function buildPDFFile(Report $report)
        {
            $pdf = PDF::loadView('infra.testsale');

            $pdf->save(storage_path('app/test.pdf'));

            return $pdf->download('test.pdf');
        }
    */
    /*
        public function testSale()
        {
            $pdf = PDF::loadView('infra.testsale');

            $pdf->save(storage_path('app/test.pdf'));

            return $pdf->download('test.pdf');
        }
    */

    /*
     *
     * $pdf = PDF::loadView('pdf.invoice', $data);
return $pdf->download('invoice.pdf');
     */
    /*
        public function testSale()
        {
            $pdf = PDF::loadView('infra.testsale');

            return $pdf->download('invoice.pdf');
        }
    */
    /*
        // Perfect Functioning Single Sale Image Generation (Color)
        public function testSale()
        {
            $pdf = SnappyImage::loadView('infra.testsale');

            return $pdf->stream('invoice.png');
        }
    */
    /*
        // Perfect Functioning Single Sale Image Generation (B&W)
        public function testSale()
        {
            $pdf = SnappyImage::loadView('infra.testsalebw');

            return $pdf->stream('invoice.png');
        }
    */
    /*
        public function testSale()
        {
            $user = auth()->user();
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

            $pdf->AddPage();

            $img = base_path('public\\img\\generated.png');

            $x = 0.25;
            $y = 0.5;

            $pdf->Image($img, $x, $y, 2, 3, 'PNG', '', '', false, 300, '', false, false, 0, false, false, false);

            $pdf->cropMark($x, $y, 0.2, 0.2, 'TL');

            $x += 2.0;

            $pdf->Image($img, $x, $y, 2, 3, 'PNG', '', '', false, 300, '', false, false, 0, false, false, false);

            $pdf->cropMark($x, $y, 0.2, 0.2, 'TOP');

            $x += 2.0;

            $pdf->Image($img, $x, $y, 2, 3, 'PNG', '', '', false, 300, '', false, false, 0, false, false, false);

            $pdf->cropMark($x, $y, 0.2, 0.2, 'TOP');

            $x += 2.0;

            $pdf->Image($img, $x, $y, 2, 3, 'PNG', '', '', false, 300, '', false, false, 0, false, false, false);

            $pdf->cropMark($x, $y, 0.2, 0.2, 'TOP');

            $pdf->cropMark(($x + 2.0), $y, 0.2, 0.2, 'TR');

            $x = 0.25;
            $y += 3.5;

            $pdf->Image($img, $x, $y, 2, 3, 'PNG', '', '', false, 300, '', false, false, 0, false, false, false);

            $pdf->cropMark($x, ($y - 0.5), 0.2, 0.2, 'LEFT');
            $pdf->cropMark($x, $y, 0.2, 0.2, 'LEFT');

            $x += 2.0;

            $pdf->Image($img, $x, $y, 2, 3, 'PNG', '', '', false, 300, '', false, false, 0, false, false, false);

            $pdf->cropMark($x, ($y - 0.25), 0.2, 0.2, 'TOP,BOTTOM');

            $x += 2.0;

            $pdf->Image($img, $x, $y, 2, 3, 'PNG', '', '', false, 300, '', false, false, 0, false, false, false);

            $pdf->cropMark($x, ($y - 0.25), 0.2, 0.2, 'TOP,BOTTOM');

            $x += 2.0;

            $pdf->Image($img, $x, $y, 2, 3, 'PNG', '', '', false, 300, '', false, false, 0, false, false, false);

            $pdf->cropMark($x, ($y - 0.25), 0.2, 0.2, 'TOP,BOTTOM');

            $pdf->cropMark(($x + 2.0), ($y - 0.5), 0.2, 0.2, 'RIGHT');
            $pdf->cropMark(($x + 2.0), $y, 0.2, 0.2, 'RIGHT');

            $x = 0.25;
            $y += 3.5;

            $pdf->Image($img, $x, $y, 2, 3, 'PNG', '', '', false, 300, '', false, false, 0, false, false, false);

            $pdf->cropMark($x, ($y - 0.5), 0.2, 0.2, 'LEFT');
            $pdf->cropMark($x, $y, 0.2, 0.2, 'LEFT');

            $x += 2.0;

            $pdf->Image($img, $x, $y, 2, 3, 'PNG', '', '', false, 300, '', false, false, 0, false, false, false);

            $pdf->cropMark($x, ($y - 0.25), 0.2, 0.2, 'TOP,BOTTOM');

            $x += 2.0;

            $pdf->Image($img, $x, $y, 2, 3, 'PNG', '', '', false, 300, '', false, false, 0, false, false, false);

            $pdf->cropMark($x, ($y - 0.25), 0.2, 0.2, 'TOP,BOTTOM');

            $x += 2.0;

            $pdf->Image($img, $x, $y, 2, 3, 'PNG', '', '', false, 300, '', false, false, 0, false, false, false);

            $pdf->cropMark($x, ($y - 0.25), 0.2, 0.2, 'TOP,BOTTOM');

            $pdf->cropMark(($x + 2.0), ($y - 0.5), 0.2, 0.2, 'RIGHT');
            $pdf->cropMark(($x + 2.0), $y, 0.2, 0.2, 'RIGHT');

            $pdf->cropMark(0.25, 10.5, 0.2, 0.2, 'BL');
            $pdf->cropMark(2.25, 10.5, 0.2, 0.2, 'BOTTOM');
            $pdf->cropMark(4.25, 10.5, 0.2, 0.2, 'BOTTOM');
            $pdf->cropMark(6.25, 10.5, 0.2, 0.2, 'BOTTOM');
            $pdf->cropMark(8.25, 10.5, 0.2, 0.2, 'BR');

            $pdf->Output('test.pdf', 'I');
        }
    */
/*
    public function printSheet($images)
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

    public function addAllCropMarks($pdf)
    {
        $x = 0.25;
        $y = 0.5;

        $pdf->cropMark($x, $y, 0.2, 0.2, 'TL');

        $x += 2.0;

        $pdf->cropMark($x, $y, 0.2, 0.2, 'TOP');

        $x += 2.0;

        $pdf->cropMark($x, $y, 0.2, 0.2, 'TOP');

        $x += 2.0;

        $pdf->cropMark($x, $y, 0.2, 0.2, 'TOP');

        $pdf->cropMark(($x + 2.0), $y, 0.2, 0.2, 'TR');

        $x = 0.25;
        $y += 3.5;

        $pdf->cropMark($x, ($y - 0.5), 0.2, 0.2, 'LEFT');
        $pdf->cropMark($x, $y, 0.2, 0.2, 'LEFT');

        $x += 2.0;

        $pdf->cropMark($x, ($y - 0.25), 0.2, 0.2, 'TOP,BOTTOM');

        $x += 2.0;

        $pdf->cropMark($x, ($y - 0.25), 0.2, 0.2, 'TOP,BOTTOM');

        $x += 2.0;

        $pdf->cropMark($x, ($y - 0.25), 0.2, 0.2, 'TOP,BOTTOM');

        $pdf->cropMark(($x + 2.0), ($y - 0.5), 0.2, 0.2, 'RIGHT');
        $pdf->cropMark(($x + 2.0), $y, 0.2, 0.2, 'RIGHT');

        $x = 0.25;
        $y += 3.5;

        $pdf->cropMark($x, ($y - 0.5), 0.2, 0.2, 'LEFT');
        $pdf->cropMark($x, $y, 0.2, 0.2, 'LEFT');

        $x += 2.0;

        $pdf->cropMark($x, ($y - 0.25), 0.2, 0.2, 'TOP,BOTTOM');

        $x += 2.0;

        $pdf->cropMark($x, ($y - 0.25), 0.2, 0.2, 'TOP,BOTTOM');

        $x += 2.0;

        $pdf->cropMark($x, ($y - 0.25), 0.2, 0.2, 'TOP,BOTTOM');

        $pdf->cropMark(($x + 2.0), ($y - 0.5), 0.2, 0.2, 'RIGHT');
        $pdf->cropMark(($x + 2.0), $y, 0.2, 0.2, 'RIGHT');

        $pdf->cropMark(0.25, 10.5, 0.2, 0.2, 'BL');
        $pdf->cropMark(2.25, 10.5, 0.2, 0.2, 'BOTTOM');
        $pdf->cropMark(4.25, 10.5, 0.2, 0.2, 'BOTTOM');
        $pdf->cropMark(6.25, 10.5, 0.2, 0.2, 'BOTTOM');
        $pdf->cropMark(8.25, 10.5, 0.2, 0.2, 'BR');

        return $pdf;
    }

    protected function getUploadFormYears()
    {
        $carbon = Carbon::now();

        $lastYear = $carbon->copy()->subYear();
        $nextYear = $carbon->copy()->addYear();

        $years[] = $lastYear->year;
        $years[] = $carbon->year;
        $years[] = $nextYear->year;

        return $years;
    }

    protected function getUploadFormNextMonth()
    {
        $carbon = Carbon::now();

        $nextMonth = $carbon->copy()->addMonth();
        $nextMonth = $nextMonth->month;

        return $nextMonth;
    }

    protected function getUploadFormNextYear()
    {
        $carbon = Carbon::now();

        $nextMonth = $carbon->copy()->addMonth();
        $nextYear  = $nextMonth->year;

        return $nextYear;
    }
*/
}
