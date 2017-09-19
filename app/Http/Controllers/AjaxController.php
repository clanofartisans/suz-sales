<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;

class AjaxController extends Controller
{
    public function jobCounts()
    {
        $jobCounts['processing'] = DB::table('jobs')->where('queue', 'processing')->count();
        $jobCounts['imaging']    = DB::table('jobs')->where('queue', 'imaging')->count();

        return $jobCounts;
    }
}
