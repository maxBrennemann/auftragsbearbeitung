<?php
/* https://www.prestashop.com/forums/topic/266004-using-the-webservice-to-get-image-urls/ */

$apiKey = ""; // webservice key with permission get image only
$website = "https://klebefux.de"; // your prestashop website URL
$url = $website . '/api/images/products/' . $_GET['product'] . '/' . $_GET['image'];

header("Content-Type: image/jpeg");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_USERPWD, $apiKey . ':');
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.1 Safari/537.11');
$res = curl_exec($ch);
$rescode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo $res;
