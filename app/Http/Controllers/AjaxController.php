<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;

class AjaxController extends Controller
{
    public function queueCountInfra()
    {
        $queueCount = [];

        $queueCount['infra'] = DB::table('infra_items')->where('queued', true)->count();

        return $queueCount;
    }

    public function queueCountsManual()
    {
        $queueCounts = [];

        $queueCounts['bw']    = DB::table('manual_sales')->where('queued', true)->where('color', false)->whereNull('deleted_at')->count();
        $queueCounts['color'] = DB::table('manual_sales')->where('queued', true)->where('color', true)->whereNull('deleted_at')->count();

        return $queueCounts;
    }

    public function jobCounts()
    {
        $jobCounts = [];

        $jobCounts['processing'] = DB::table('jobs')->where('queue', 'processing')->count();
        $jobCounts['imaging']    = DB::table('jobs')->where('queue', 'imaging')->count();

        return $jobCounts;
    }
}
