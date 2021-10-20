<?php
$rootPath = dirname(__DIR__);
$dbFh = fopen($rootPath . '/docs/dbKeys.csv', 'r');
$dbKeys = [];
while($line = fgetcsv($dbFh, 2048)) {
    $line[0] = str_replace('　', '', $line[0]);
    $dbKeys[$line[0]] = $line[1];
}

$dbFh = fopen($rootPath . '/docs/dbKeys.csv', 'w');
foreach($dbKeys AS $k => $v) {
    $pageFile = $rootPath . '/docs/foundations/view/' . $v . '/index.html';
    if(file_exists($pageFile)) {
        fputcsv($dbFh, [$k, $v]);
    }
}