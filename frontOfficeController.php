<?php

/* starts session for registered users and shopping carts */
session_start();

require_once('classes/Link.php');
require_once('classes/project/Produkt.php');
require_once('classes/front/Navigation.php');
require_once('classes/Ajax.php');

$isArticle = false;

$url = $_SERVER['REQUEST_URI'];
$url = explode('?', $url, 2);
$page = str_replace($_ENV["REWRITE_BASE"] . $_ENV["SUB_URL"], "", $url[0]);
$parts = explode('/', $page);
$page = $parts[count($parts) - 1];
if ($parts[0] == 'artikel') {
    $isArticle = true;
}

if (isset($_POST['getReason'])) {
    Ajax::manageRequests($_POST['getReason'], $page);
} else {
    showPage($page, $isArticle);
}

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

        $title = $pageName;

        if ($articleUrl == "productPage.php") {
            $nummer = isset($_GET["id"]) ? $_GET["id"] : 0;
            $query = "SELECT Bezeichnung FROM produkt WHERE Nummer = $nummer";
            $data =  DBAccess::selectQuery($query);
            $title = $data[0]["Bezeichnung"];
        }
    }
    
    include('files/frontOffice/header.php');
    include($baseUrl . $articleUrl);
    include('files/frontOffice/footer.php');
}

?>