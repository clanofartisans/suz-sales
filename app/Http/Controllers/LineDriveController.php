<?php

namespace App\Http\Controllers;

use DB;
use POS;
use App\LineDrive;
use Carbon\Carbon;
use App\Jobs\ApplyLineDrive;
use Illuminate\Http\Request;

class LineDriveController extends Controller
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

        $items = LineDrive::orderBy('sale_begin', 'asc')
                           ->orderBy('sale_end', 'asc')
                           ->orderBy('brand', 'asc');

        switch($filter) {
            case 'f_all':
                $items = $items->where('expires', '>=', Carbon::now());
                break;
            case 'f_processed':
                $items = $items->where('processed', true)
                               ->where('expires', '>=', Carbon::now());;
                break;
            case 'f_flagged':
                $items = $items->whereNotNull('flags')
                               ->where('expires', '>=', Carbon::now());;
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

        $jobCounts['processing'] = DB::table('jobs')->where('queue', 'processing')->count();

        return view('linedrive.index', compact('items', 'filter', 'jobCounts'));
    }

    public function process(Request $request)
    {
        if($request->filter) {
            session(['filter' => $request->filter]);
        }

        switch($request->process) {
            case 'add':
                return redirect()->route('linedrive.create');
                break;
            case 'delete':
                $this->deleteItems($request);
                break;
        }

        return redirect()->route('linedrive.index');
    }

    public function create()
    {
        $brands = POS::getBrands();

        return view('linedrive.create', compact('brands'));
    }

    public function store(Request $request)
    {
        $sale_begin = new Carbon($request->sale_begin);
        $sale_end   = new Carbon($request->sale_end);

        $expires = new Carbon($request->sale_end);
        $expires = $expires->addDay();

        $sale = LineDrive::create(['brand'      => urldecode($request->brand),
                                   'discount'   => $request->discount,
                                   'sale_begin' => $sale_begin,
                                   'sale_end'   => $sale_end,
                                   'expires'    => $expires,
                                   'processed'  => false]);

        dispatch((new ApplyLineDrive($sale))->onQueue('processing'));

        return redirect()->route('linedrive.index');
    }

    protected function deleteItems(Request $request)
    {
        foreach($request->checked as $item) {
            LineDrive::find($item)->delete();
        }

        flash()->success('The selected items have been deleted.');
    }
}
