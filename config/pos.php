<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Point of Sale System - Driver
    |--------------------------------------------------------------------------
    |
    | *****************************************************************
    | **************************************************************
    | ***********************************************************
    |
    */
    'driver' => env('POS_DRIVER', 'orderdog'),

    /*
    |--------------------------------------------------------------------------
    | Point of Sale System - Name
    |--------------------------------------------------------------------------
    |
    | *****************************************************************
    | **************************************************************
    | ***********************************************************
    |
    */
    'name' => env('POS_NAME', 'POS System'),

    /*
    |--------------------------------------------------------------------------
    | Point of Sale System - Name
    |--------------------------------------------------------------------------
    |
    | *****************************************************************
    | **************************************************************
    | ***********************************************************
    |
    */
    'shortname' => env('POS_SHORTNAME', 'POS'),

    'counterpoint' => [

        'user' => env('COUNTERPOINT_USER', 'SALESMGR')
    ]

];
