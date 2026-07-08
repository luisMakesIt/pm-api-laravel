<?php

return [
    'driver' => 'sanitize',
    'view_path' => resource_path('views/vendor/pdf'),
    'margin_bottom' => 20,
    'margin_left' => 15,
    'margin_right' => 15,
    'margin_top' => 15,
    'default' => [
        'mode' => 'utf-8',
        'format' => 'A4',
        'auto_script' => true,
        'auto_meta' => true,
        'auto_locale_countries' => true,
    ],
];
