<?php
$rootPath = dirname(__DIR__);
$courts = array(
    'NTD' => '臺灣南投地方法院',
    'TPD' => '臺灣台北地方法院',
    'PCD' => '臺灣新北地方法院',
    'SLD' => '臺灣士林地方法院',
    'TYD' => '臺灣桃園地方法院',
    'SCD' => '臺灣新竹地方法院',
    'MLD' => '臺灣苗栗地方法院',
    'TCD' => '臺灣臺中地方法院',
    'CHD' => '臺灣彰化地方法院',
    'ULD' => '臺灣雲林地方法院',
    'CYD' => '臺灣嘉義地方法院',
    'TND' => '臺灣臺南地方法院',
    'CTD' => '臺灣橋頭地方法院',
    'KSD' => '臺灣高雄地方法院',
    'PTD' => '臺灣屏東地方法院',
    'TTD' => '臺灣臺東地方法院',
    'HLD' => '臺灣花蓮地方法院',
    'ILD' => '臺灣宜蘭地方法院',
    'KLD' => '臺灣基隆地方法院',
    'PHD' => '臺灣澎湖地方法院',
    'LCD' => '褔建連江地方法院',
    'KMD' => '福建金門地方法院',
);

/*
old: http://cdcb.judicial.gov.tw/abbs/wkw/WHD6K00_DOWNLOADCVS.jsp?court=
*/

$oFh = [];
$headerOffice = ['登記案號', '序號', '分事務所地址'];
$headerMember = ['登記案號', '序號', '職稱', '姓名'];
$urlYear = '';
$urlPool = [];
foreach ($courts as $k => $v) {
    $url = 'https://aomp109.judicial.gov.tw/judbp/whd6k/WHD6K01/PUB_DATA/' . $k . '_RA.csv';
    file_put_contents($rootPath . '/tmp/' . $k . '.csv', file_get_contents($url));
    $fh = fopen($rootPath . '/tmp/' . $k . '.csv', 'r');
    $header = fgetcsv($fh, 2048);
    array_pop($header);
    $currentData = 'main';
    while ($line = fgetcsv($fh, 4096)) {
        $year = trim(substr($line[0], 0, 3));
        if (empty($year) || !preg_match('/^[0-9]{3}$/', $year)) {
            continue;
        }
        
        switch (count($line)) {
            case 4:
                $currentData = 'office';
                $header = $headerOffice;
                break;
            case 5:
                $currentData = 'member';
                $header = $headerMember;
                break;
            default:
                $currentData = 'main';
        }
        $key = $k . $year . $currentData;
        $rawPath = $rootPath . '/raw/' . $year;
        if (!file_exists($rawPath)) {
            mkdir($rawPath, 0777, true);
        }
        if($urlYear != $year) {
            $urlYear = $year;
            $urlYearFile = $rootPath . '/raw/' . $year . '/urlPool.csv';
            if(file_exists($urlYearFile)) {
                $urlPool = [];
                $urlFh = fopen($urlYearFile, 'r');
                while($urlLine = fgetcsv($urlFh, 1024)) {
                    $urlPool[$urlLine[0]] = $urlLine[1];
                }
                fclose($urlFh);
            }
        }
        if (!isset($oFh[$key])) {
            $oFh[$key] = $rawPath . '/' . $k . '_' . $currentData . '.csv';
            $aFh = fopen($oFh[$key], 'w');
            fputcsv($aFh, $header);
            fclose($aFh);
        }
        $lineUrl = array_pop($line);
        $lineUrlParts = explode('/judbp/whd6k/q/', $lineUrl);
        $lineUrlKey = $k . $line[0];
        if(!isset($urlPool[$lineUrlKey]) && isset($lineUrlParts[1])) {
            $urlPool[$lineUrlKey] = $lineUrlParts[1];
            $urlFh = fopen($urlYearFile, 'a+');
            fputcsv($urlFh, [$lineUrlKey, $urlPool[$lineUrlKey]]);
            fclose($urlFh);
        }
        $aFh = fopen($oFh[$key], 'a');
        fputcsv($aFh, $line);
        fclose($aFh);
    }
}