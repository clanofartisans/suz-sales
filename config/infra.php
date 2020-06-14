<?php

return [

    /*
    |--------------------------------------------------------------------------
    | INFRA Spreadsheet Worksheet Name
    |--------------------------------------------------------------------------
    |
    | Here you should specify the worksheet name we want to use when we're
    | processing the uploaded INFRA spreadsheet. We currently prefer to
    | use the worksheet for KeHE rather than the worksheet for UNFI.
    |
    */
    'sheetname' => 'KeHE',

    /*
    |--------------------------------------------------------------------------
    | INFRA Spreadsheet Data Header Labels
    |--------------------------------------------------------------------------
    |
    | Here you should specify the values for the data headers we'll search
    | for in the INFRA spreadsheet. These are the critical fields we'll
    | need in order to put items on sale and generate the sale tags.
    |
    */
    'header' => [
        'upc'   => 'UPC',
        'brand' => 'Brand',
        'desc'  => 'Product Description',
        'size'  => 'Unit Size',
        'price' => 'Flyer CT $',
    ],
];
