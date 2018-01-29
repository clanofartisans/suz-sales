<?php

namespace App\Http\Controllers;

use DB;
use POS;
use TCPDF;
use App\ExcelDoc;
use Carbon\Carbon;
use App\InfraItem;
use App\InfraSheet;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;

class InfraController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of uploaded INFRA workbooks.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $infrasheets = InfraSheet::orderBy('year', 'desc')
                                 ->orderBy('month', 'desc')
                                 ->get();

        $years     = $this->getUploadFormYears();
        $nextMonth = $this->getUploadFormNextMonth();
        $nextYear  = $this->getUploadFormNextYear();

        return view('infra.index', compact('infrasheets', 'years', 'nextMonth', 'nextYear'));
    }

    /**
     * Display a listing of items for a given INFRA workbooks.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $filter = session('infra_filter');

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
            case 'f_queued':
                $items = $items->where('queued', true);
                break;
            case 'f_printed':
                $items = $items->where('printed', true);
                break;
            case 'f_flagged':
                $items = $items->whereNotNull('flags');
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
        }

        if(request()->has('page')) {
            $items = $items->paginate(100);
            session(['infra_page' => $items->currentPage()]);
        } else {
            $page = session('infra_page', 1);
            if($page > 1 && (((float) $items->count()) / ($page - 1.0)) <= 100.0) {
                session(['infra_page' => 1]);
            }
            $items = $items->paginate(100, ['*'], 'page', session('infra_page', 1));
        }

        $queueCount = DB::table('infra_items')->where('queued', true)->count();

        $jobCounts['processing'] = DB::table('jobs')->where('queue', 'processing')->count();
        $jobCounts['imaging']    = DB::table('jobs')->where('queue', 'imaging')->count();

        return view('infra.show', compact('infrasheet', 'items', 'filter', 'queueCount', 'jobCounts'));
    }

    /**
     * Process an uploaded INFRA spreadsheet file.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function uploadStore(Request $request)
    {
        $infrasheet = new InfraSheet;

        $infrasheet->month = $request->upmonth;
        $infrasheet->year  = $request->upyear;

        $infrasheet->filename = $request->upworkbook->store('infrasheets');

        $infrasheet->save();

        $excelDoc = new ExcelDoc($infrasheet->filename);

        $excelDoc->prepareAndSaveItemData($infrasheet->id);

        POS::StartInfraSheet($infrasheet);

        flash()->success('The INFRA workbook was uploaded successfully.');

        return redirect()->route('infra.index');
    }

    /**
     * Approve or print INFRA items based on user input.
     *
     * @param int $id
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function process($id, Request $request)
    {
        if($request->filter) {
            session(['infra_filter' => $request->filter]);
        }
        switch($request->process) {
            case 'approve':
                $this->approveItems($request);
                break;
            case 'approveall':
                $this->approveAllItems($request);
                break;
            case 'queue':
                $this->queueSelectedItems($request);
                break;
            case 'print':
                $this->printAllQueuedItems($request);
                break;
        }

        return redirect()->route('infra.show', ['id' => $id]);
    }

    protected function approveAllItems(Request $request)
    {
        $items = InfraItem::where('infrasheet_id', $request->infrasheet)
                          ->get();

        foreach($items as $item) {
            $item->approve();
        }

        flash()->success('All items have been approved.');
    }

    /**
     * Loop through and approve user selected INFRA items.
     *
     * @param Request $request
     */
    protected function approveItems(Request $request)
    {
        foreach($request->checked as $item) {
            InfraItem::find($item)->approve();
        }

        flash()->success('The selected items have been approved.');
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

        $items = InfraItem::whereIn('id', $request->checked)
                          ->where('imaged', true)
                          ->get();

        if($this->queueItems($items)) {

            flash()->success('The selected items have been queued for printing.');
        }
    }

    /**
     * Loop through and print all INFRA items that are ready to be printed.
     *
     * @param Request $request
     */
    protected function printAllQueuedItems(Request $request)
    {
        $items = InfraItem::where('infrasheet_id', $request->infrasheet)
                          ->where('queued', true)
                          ->orderBy('brand', 'asc')
                          ->orderBy('id', 'asc')
                          ->get();

        if($this->printItems($items)) {

            flash()->success('All items that were queued for printing have been printed.');
        }
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
            $images[] = storage_path("app/images/infra/$item->id.png");
        }

        $this->printSheet($images);

        foreach($items as $item) {
            $item->print();
        }

        return true;
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

    /**
     * Builds an array of previous and upcoming years based on current year.
     *
     * @return array
     */
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

    /**
     * Calculates and returns next month as an integer.
     *
     * @return int
     */
    protected function getUploadFormNextMonth()
    {
        $carbon = Carbon::now();

        $nextMonth = $carbon->copy()->addMonth();
        $nextMonth = $nextMonth->month;

        return $nextMonth;
    }

    /**
     * Calculates and returns next year as an integer.
     *
     * @return int
     */
    protected function getUploadFormNextYear()
    {
        $carbon = Carbon::now();

        $nextMonth = $carbon->copy()->addMonth();
        $nextYear  = $nextMonth->year;

        return $nextYear;
    }
}
