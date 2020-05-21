<?php
$rootPath = dirname(__DIR__);
include $rootPath . '/cns11643/scripts/big5e_to_utf8.php';
$courts = array(
    'TPD' => '臺灣台北地方法院',
    'PCD' => '臺灣新北地方法院',
    'SLD' => '臺灣士林地方法院',
    'TYD' => '臺灣桃園地方法院',
    'SCD' => '臺灣新竹地方法院',
    'MLD' => '臺灣苗栗地方法院',
    'TCD' => '臺灣臺中地方法院',
    'NTD' => '臺灣南投地方法院',
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
    'KSY' => '臺灣高雄少年法院',
    'LCD' => '褔建連江地方法院',
    'KMD' => '福建金門地方法院',
);

$baseUrl = 'http://cdcb.judicial.gov.tw/abbs/wkw/WHD6K00_DOWNLOADCVS.jsp?court=';

$rawPath = $rootPath . '/raw';

foreach($courts AS $k => $v) {
    file_put_contents($rawPath . '/' . $k . '.csv', Converter::iconv(file_get_contents($baseUrl . $k), 1));
}