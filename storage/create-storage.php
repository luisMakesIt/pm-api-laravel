<?php

$storageDirs = [
    'framework/{sessions,views,cache,testing}',
    'logs',
];

foreach ($storageDirs as $dir) {
    $fullPath = __DIR__ . "/../../../storage/{$dir}";
    @mkdir($fullPath, 0775, true);
}

// Symlink public storage
$publicStorage = __DIR__ . "/../../public/storage";
$actualStorage = __DIR__ . "/../../../storage/app/public";
if (!file_exists($publicStorage) && !is_link($publicStorage)) {
    @symlink($actualStorage, $publicStorage);
}
