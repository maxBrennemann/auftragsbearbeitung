<?php

$url = $_SERVER['REQUEST_URI'];
$url = explode('?', $url, 2);
$page = str_replace(REWRITE_BASE . SUB_URL, "", $url[0]);
$parts = explode('/', $page);
$page = $parts[count($parts) - 1];
if ($parts[0] == 'artikel') {
	$isArticle = true;
}

echo $url;

?>