<?php

require_once('classes/Link.php');
require_once('classes/project/Produkt.php');
require_once('classes/front/Navigation.php');

$isArticle = false;

$url = $_SERVER['REQUEST_URI'];
$url = explode('?', $url, 2);
$page = str_replace(REWRITE_BASE . SUB_URL, "", $url[0]);
$parts = explode('/', $page);
$page = $parts[count($parts) - 1];
if ($parts[0] == 'artikel') {
    $isArticle = true;
}

showPage($page, $isArticle);

function showPage($page, $isArticle) {
    if ($page == "test") {
        include('test.php');
        return null;
    }

    $result = DBAccess::selectQuery("SELECT id, articleUrl, pageName FROM frontpage WHERE src = '$page'");
    $articleUrl = "";

    if ($result == null) {
        /* generated articles does not exist in this project */
        //$baseUrl = 'files/generated/';
        //$result = DBAccess::selectQuery("SELECT id, articleUrl, pageName FROM generated_articles WHERE src = '$page'");

        http_response_code(404);

        $baseUrl = 'files/frontOffice/';
        $result['id'] = 0;
        $result["articleUrl"] = $articleUrl = "404.php";
        $result["pageName"] = $pageName = "Page not found";
    } else {
        $baseUrl = 'files/frontOffice/';
        $result = $result[0];
        $articleUrl = $result["articleUrl"];
        $pageName = $result["pageName"];
    }
    
    include('files/frontOffice/header.php');
    include($baseUrl . $articleUrl);
    include('files/frontOffice/footer.php');
}

?>