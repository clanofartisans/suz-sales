<?php

return array(


    'pdf' => array(
        'enabled' => true,
        'binary'  => env('SNAPPY_PDF_BIN', '"C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe"'),
        'timeout' => false,
        'options' => array('dpi' => '1200', 'image-dpi' => '1200', 'image-quality' => '100', 'page-size' => 'A0', 'disable-smart-shrinking' => true),
        'env'     => array(),
    ),
    'image' => array(
        'enabled' => true,
        'binary'  => env('SNAPPY_IMG_BIN', '"C:\Program Files\wkhtmltopdf-beta\bin\wkhtmltoimage.exe"'),
        'timeout' => false,
        'options' => array('format' => 'png',
                           'quality' => '100',
                           'crop-h' => '900',
                           'crop-w' => '600',
                           'crop-x' => '8',
                           'crop-y' => '8'),
        'env'     => array(),
    ),


);
