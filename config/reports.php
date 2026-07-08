<?php

return [
    'export_prefix' => 'export_',
    'export_path' => 'exports',
    'temporary_path' => sys_get_temp_dir(),
    'dateFormat' => 'Y-m-d',
    'csv' => [
        'delimiter' => ',',
        'enclosure' => '"',
        'escape' => '\\',
        'line_ending' => PHP_EOL,
    ],
];
