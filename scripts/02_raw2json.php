<?php
/**
 * sudo apt-get install php-uuid php7.4-uuid
*/
$rootPath = dirname(__DIR__);
$years = [];
foreach(glob($rootPath . '/raw/*') AS $yearPath) {
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

$dbFh = fopen($rootPath . '/db/dbKeys.csv', 'r');
$dbKeys = [];
while($line = fgetcsv($dbFh, 2048)) {
    $line[0] = str_replace('　', '', $line[0]);
    $dbKeys[$line[0]] = $line[1];
}
$listKeys = [];

$pool = [
    'member' => [],
];
$templateContent = file_get_contents($rootPath . '/db/template.html');
foreach($years AS $year => $yearPath) {
    foreach(glob($yearPath . '/*_main.csv') AS $csvFile) {
        $p = pathinfo($csvFile);
        $metaFile = $p['dirname'] . '/' . str_replace('_main', '_member', $p['basename']);
        if(file_exists($metaFile)) {
            $fh = fopen($metaFile, 'r');
            $header = fgetcsv($fh, 2048);
            $header[0] = '登記案號';
            while($line = fgetcsv($fh, 2048)) {
                if(count($line) !== 5) {
                    continue;
                }
                $data = array_combine($header, $line);
                if(!isset($pool['member'][$data['登記案號']])) {
                    $pool['member'][$data['登記案號']] = [];
                }
                $pool['member'][$data['登記案號']][] = [
                    $data['職稱'], $data['姓名']
                ];
            }
        }
        
        $fh = fopen($csvFile, 'r');
        $header = fgetcsv($fh, 2048);
        $header[0] = '登記案號';
        while($line = fgetcsv($fh, 2048, ',', '"', false)) {
            if(count($line) !== 40) {
                continue;
            }
            $data = array_combine($header, $line);
            if(empty($data['登記案號'])) {
                continue;
            }
            foreach($dateFields AS $dateField) {
                if(strlen($data[$dateField]) === 7) {
                    $y = intval(substr($data[$dateField], 0, 3)) + 1911;
                    $m = substr($data[$dateField], 3, 2);
                    $d = substr($data[$dateField], 5, 2);
                    $data[$dateField] = implode('-', [$y, $m, $d]);
                }
            }
            $data['許可機關日期'] = strtr($data['許可機關日期'], $approvedBy1);
            $data['許可機關日期'] = preg_replace($approvedBy2, $approvedBy3, $data['許可機關日期']);
            $dbKey = $data['法人名稱'] . $data['設立登記日期'];
            if(isset($listKeys[$data['許可機關日期']])) {
                $pk = $listKeys[$data['許可機關日期']];
            } elseif(isset($dbKeys[$dbKey])) {
                $pk = $dbKeys[$dbKey];
                $listKeys[$data['許可機關日期']] = $pk;
            } else {
                $pk = uuid_create();
                $dbKeys[$dbKey] = $pk;
                $aFh = fopen($rootPath . '/db/dbKeys.csv', 'a');
                fputcsv($aFh, [$dbKey, $pk]);
                fclose($aFh);
                $listKeys[$data['許可機關日期']] = $pk;
            }
            
            $pagePath = $rootPath . '/docs/foundations/view/' . $pk;
            if(!file_exists($pagePath)) {
                mkdir($pagePath, 0777, true);
            }
            $members = '';
            if(!empty($pool['member'][$data['登記案號']])) {
                foreach($pool['member'][$data['登記案號']] AS $member) {
                    $members .= '<dt>' . $member[0] . '</dt>';
                    $members .= '<dd>' . $member[1] . '</dd>';
                }    
            }
            $page = strtr($templateContent, [
                '{{field_name}}' => $data['法人名稱'],
                '{{field_purpose}}' => $data['目的'],
                '{{field_type}}' => ($data['類別'] == 1) ? '財團' : '社團',
                '{{field_owner}}' => $data['法人代表'],
                '{{field_date_register}}' => $data['設立登記日期'],
                '{{field_address}}' => $data['主事務所'],
                '{{field_source}}' => $data['捐助方法'],
                '{{field_list_number}}' => $data['許可機關日期'],
                '{{field_asset}}' => $data['財產總額'],
                '{{field_date_update}}' => $data['登記日期'],
                '{{field_url}}' => $data['網站連結'],
                '{{field_members}}' => $members,
            ]);
            file_put_contents($pagePath . '/index.html', $page);
            file_put_contents($pagePath . '/' . $data['登記日期'] . '.html', $page);
            $jsonFile = $pagePath . '/history.json';
            if(file_exists($jsonFile)) {
                $json = json_decode(file_get_contents($jsonFile), true);
            }
            $json[$data['登記日期']] = 1;
            file_put_contents($jsonFile, json_encode($json));
        }
    }
}