<?php

namespace App\Http\Controllers;

use App\EmployeeDiscount;
use App\Jobs\ApplyEmployeeDiscount;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use POS;

class EmployeeDiscountController extends Controller
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
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $filter = session('filter');

        if (empty($filter)) {
            $filter = 'f_all';
        }

        $items = EmployeeDiscount::where(function ($query) {
            $query->where('no_begin', false)
                                            ->orWhere('no_end', false);
        })
                                 ->orderBy('sale_end', 'asc')
                                 ->orderBy('sale_begin', 'asc')
                                 ->orderBy('brand', 'asc');

        $forever = EmployeeDiscount::where('no_begin', true)
                                   ->where('no_end', true)
                                   ->orderBy('brand', 'asc');

        switch ($filter) {
            case 'f_all':
                $items   = $items->where('expires', '>=', Carbon::now());
                $forever = $forever->where('expires', '>=', Carbon::now());
                break;
            case 'f_processed':
                $items = $items->where('processed', true)
                                 ->where('expires', '>=', Carbon::now());
                $forever = $forever->where('processed', true)
                                    ->where('expires', '>=', Carbon::now());
                break;
            case 'f_flagged':
                $items = $items->whereNotNull('flags')
                                 ->where('expires', '>=', Carbon::now());
                $forever = $forever->whereNotNull('flags')
                                   ->where('expires', '>=', Carbon::now());
                break;
            case 'f_expired':
                $items   = $items->where('expires', '<', Carbon::now());
                $forever = $forever->where('expires', '<', Carbon::now());
                break;
        }

        $items   = $items->get();
        $forever = $forever->get();

        $items = $items->concat($forever);

        foreach ($items as $item) {
            if ($item->no_begin && $item->no_end) {
                $item->from_to = 'Forever';
            } else {
                if (is_null($item->sale_begin)) {
                    $item->from_to = '&mdash;';
                } else {
                    $item->from_to = $item->sale_begin->toFormattedDateString();
                }

                $item->from_to .= ' to ';

                if (is_null($item->sale_end)) {
                    $item->from_to .= '&mdash;';
                } else {
                    $item->from_to .= $item->sale_end->toFormattedDateString();
                }
            }
        }

        $jobCounts = [];

        $jobCounts['processing'] = DB::table('jobs')->where('queue', 'processing')->count();

        return view('employeediscount.index', compact('items', 'filter', 'jobCounts'));
    }

    public function process(Request $request)
    {
        if ($request->filter) {
            session(['filter' => $request->filter]);
        }

        switch ($request->process) {
            case 'add':
                return redirect()->route('employeediscount.create');
                break;
            case 'delete':
                $this->deleteItems($request);
                break;
        }

        return redirect()->route('employeediscount.index');
    }

    public function create()
    {
        $brands = POS::getBrands();

        return view('employeediscount.create', compact('brands'));
    }

    public function store(Request $request)
    {
        if (!isset($request->checkNoBegin)) {
            $sale_begin = new Carbon($request->sale_begin);
        } else {
            $sale_begin = null;
        }

        if (!isset($request->checkNoEnd)) {
            $sale_end = new Carbon($request->sale_end);

            $expires = new Carbon($request->sale_end);
            $expires = $expires->addDay();
        } else {
            $sale_end = null;

            $expires = new Carbon();
            $expires = $expires->addYears(10);
        }

        $sale = EmployeeDiscount::create(['brand'      => urldecode($request->brand),
                                          'discount'   => $request->discount,
                                          'sale_begin' => $sale_begin,
                                          'sale_end'   => $sale_end,
                                          'expires'    => $expires,
                                          'processed'  => false,
                                          'no_begin'   => isset($request->checkNoBegin),
                                          'no_end'     => isset($request->checkNoEnd)]);

        dispatch((new ApplyEmployeeDiscount($sale))->onQueue('processing'));

        return redirect()->route('employeediscount.index');
    }

    protected function deleteItems(Request $request)
    {
        foreach ($request->checked as $item) {
            EmployeeDiscount::find($item)->delete();
        }

        flash()->success('The selected items have been deleted.');
    }
}
