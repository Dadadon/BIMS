<?php

return [

    'pdf' => [
        'enabled' => true,
        'binary'  => env('WKHTMLTOPDF_PATH', '/usr/local/bin/wkhtmltopdf'),
        'timeout' => false,
        'options' => [
            'dpi'                 => 150,
            'enable-smart-shrinking' => true,
            'no-outline'          => true,
            'margin-top'          => 0,
            'margin-right'        => 0,
            'margin-bottom'       => 0,
            'margin-left'         => 0,
        ],
        'env' => [],
    ],

    'image' => [
        'enabled' => true,
        'binary'  => env('WKHTMLTOIMAGE_PATH', '/usr/local/bin/wkhtmltoimage'),
        'timeout' => false,
        'options' => [],
        'env' => [],
    ],

];
