<?php

/**
 * sudo apt-get install php-uuid php7.4-uuid
 */
$rootPath = dirname(__DIR__);
$years = [];
foreach (glob($rootPath . '/raw/*') as $yearPath) {
    $p = pathinfo($yearPath);
    $years[$p['filename']] = $yearPath;
}
ksort($years);

$approvedBy1 = array(
    '一' => '1', '二' => '2', '三' => '3', '四' => '4', '五' => '5', '六' => '6', '七' => '7',
    '八' => '8', '九' => '9', '１' => '1', '２' => '2', '３' => '3', '４' => '4', '５' => '5',
    '６' => '6', '７' => '7', '８' => '8', '９' => '9', '０' => '0', 'Ｏ' => '0',
    '（' => '(', '）' => ')', '　' => '', '︵' => '(', '︶' => ')'
);
$approvedBy2 = array(
    '/([1-9])十/',
    '/十([1-9])/',
    '/十/',
    '/廿([1-9])/',
    '/廿/',
);
$approvedBy3 = array(
    '${1}',
    '1${1}',
    '10',
    '2${1}',
    '20',
);
$dateFields = ['收件日期', '登記日期', '公告日期', '設立登記日期', '結案日期', '歸檔日期', '發證日期', '註銷日期', '撤銷日期'];

$dbFh = fopen($rootPath . '/docs/dbKeys.csv', 'r');
$dbKeys = [];
while ($line = fgetcsv($dbFh, 2048)) {
    $dbKeys[$line[0]] = $line[1];
}
$listKeys = [];
$jsonPath = $rootPath . '/docs/json';
if (!file_exists($jsonPath)) {
    mkdir($jsonPath, 0777, true);
}
$manualFix = [
    '544137a9-0c44-48ed-8482-2b20acb5b862/2815-12-27' => [
        '登記日期' => '2015-12-27',
    ],
    '544137aa-a1d0-4119-9c27-2b20acb5b862/2031-12-30' => [
        '登記日期' => '2013-12-30',
    ],
];

$memberPairs = [
    '　' => '',
    ' ' => '',
    '\\' => '',
];

foreach ($years as $year => $yearPath) {
    $urlPool = [];
    $urlYearFile = $rootPath . '/raw/' . $year . '/urlPool.csv';
    $urlFh = fopen($urlYearFile, 'r');
    while ($urlLine = fgetcsv($urlFh, 1024)) {
        $urlPool[$urlLine[0]] = $urlLine[1];
    }
    fclose($urlFh);
    foreach (glob($yearPath . '/*_main.csv') as $csvFile) {
        $pool = [];
        $p = pathinfo($csvFile);
        $parts = explode('_', $p['filename']);
        $metaFile = $p['dirname'] . '/' . str_replace('_main', '_member', $p['basename']);
        if (file_exists($metaFile)) {
            $fh = fopen($metaFile, 'r');
            $header = fgetcsv($fh, 2048);
            $header[0] = '登記案號';
            while ($line = fgetcsv($fh, 2048)) {
                if (count($line) !== 4) {
                    continue;
                }
                foreach ($line as $k => $v) {
                    $line[$k] = strtr($v, $memberPairs);
                }
                $data = array_combine($header, $line);
                if (!isset($pool[$data['登記案號']])) {
                    $pool[$data['登記案號']] = [];
                }
                $pool[$data['登記案號']][] = [
                    $data['職稱'], $data['姓名']
                ];
            }
        }

        $fh = fopen($csvFile, 'r');
        $header = fgetcsv($fh, 2048);
        $header[0] = '登記案號';
        while ($line = fgetcsv($fh, 2048, ',', '"', false)) {
            $data = array_combine($header, $line);
            if (empty($data['登記案號'])) {
                continue;
            }
            foreach ($dateFields as $dateField) {
                if (strlen($data[$dateField]) === 7) {
                    $y = intval(substr($data[$dateField], 0, 3)) + 1911;
                    $m = substr($data[$dateField], 3, 2);
                    $d = substr($data[$dateField], 5, 2);
                    $data[$dateField] = implode('-', [$y, $m, $d]);
                }
            }
            $data['許可機關日期'] = strtr($data['許可機關日期'], $approvedBy1);
            $data['許可機關日期'] = preg_replace($approvedBy2, $approvedBy3, $data['許可機關日期']);
            $data['法人名稱'] = str_replace('　', '', $data['法人名稱']);
            $data['設立登記日期'] = str_replace('　', '', $data['設立登記日期']);
            $dbKey = $data['法人名稱'] . $data['設立登記日期'];

            if (isset($listKeys[$data['許可機關日期']])) {
                $pk = $listKeys[$data['許可機關日期']];
            } elseif (isset($dbKeys[$dbKey])) {
                $pk = $dbKeys[$dbKey];
                $listKeys[$data['許可機關日期']] = $pk;
            } else {
                $pk = uuid_create();
                $dbKeys[$dbKey] = $pk;
                $aFh = fopen($rootPath . '/docs/dbKeys.csv', 'a');
                fputcsv($aFh, [$dbKey, $pk]);
                fclose($aFh);
                $listKeys[$data['許可機關日期']] = $pk;
            }
            $manualCheckKey = $pk . '/' . $data['登記日期'];
            if (isset($manualFix[$manualCheckKey])) {
                $data = array_merge($data, $manualFix[$manualCheckKey]);
            }

            $data['members'] = [];
            if (!empty($pool[$data['登記案號']])) {
                foreach ($pool[$data['登記案號']] as $member) {
                    $memberKey = $member[0] . $member[1];
                    $data['members'][$memberKey] = $member;
                }
            }
            ksort($data['members']);
            $urlKey = $parts[0] . $data['登記案號'];
            if (isset($urlPool[$urlKey])) {
                $data['url'] = 'https://aomp109.judicial.gov.tw/judbp/whd6k/q/' . $urlPool[$urlKey];
            } else {
                $data['url'] = '';
            }

            $jsonFile = $jsonPath . '/' . $pk . '.json';
            if (file_exists($jsonFile)) {
                $json = json_decode(file_get_contents($jsonFile), true);
            } else {
                $json = [];
            }
            $json[$data['登記日期']] = $data;
            ksort($json);
            file_put_contents($jsonFile, json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }
    }
}
