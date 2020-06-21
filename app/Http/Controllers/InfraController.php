<?php

namespace App\Http\Controllers;

use App\Exceptions\InfraFileTestException;
use App\Exceptions\POSSystemException;
use App\InfraSheet;
use App\POS\Facades\POS;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InfraController extends Controller
{
    /**
     * Show a list of all available INFRA sheets.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $infrasheets = InfraSheet::orderBy('year', 'desc')
                                 ->orderBy('month', 'desc')
                                 ->get();

        $years         = $this->getUploadFormYears();
        $selectedMonth = $this->getSelectedMonth();
        $selectedYear  = $this->getSelectedYear();

        return view('infra.index', compact('infrasheets', 'years', 'selectedMonth', 'selectedYear'));
    }

    /**
     * Create a new INFRA sheet from an uploaded INFRA file.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Support\Renderable
     * @throws InfraFileTestException
     */
    public function store(Request $request)
    {
        $infrasheet = InfraSheet::makeFromUpload($request->file('upworkbook'), $request->upmonth, $request->upyear);

        POS::initializeInfraSale($infrasheet);

        //$infrasheet->queueNewSheetJobs();

        flash()->success('The INFRA workbook was uploaded successfully.');

        return redirect()->route('infra.index');
    }

    /**
     * Get the month that should be selected by default in the upload form.
     *
     * @return int
     */
    public function getSelectedMonth()
    {
        return Carbon::now()->addMonth()->month;
    }

    /**
     * Get the year that should be selected by default in the upload form.
     *
     * @return int
     */
    public function getSelectedYear()
    {
        return Carbon::now()->addMonth()->year;
    }

    /**
     * Get the previous, current, and next year for the upload form.
     *
     * @return iterable
     */
    public function getUploadFormYears(): iterable
    {
        $current = Carbon::now()->year;
        return [$current - 1, $current, $current +1];
    }
}
