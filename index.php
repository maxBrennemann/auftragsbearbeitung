<?php

/**
 * Before: page was submitted via $_GET paramter, but now the REQUEST_URI is read;
 * $url is splitted into the REQUEST_URI and the parameter part
 */
require_once('settings.php');
$url = $_SERVER['REQUEST_URI'];
$url = explode('?', $url, 2);
$page = str_replace(REWRITE_BASE . SUB_URL, "", $url[0]);
$parts = explode('/', $page);
$page = $parts[count($parts) - 1];

switch ($parts[1]) {
	case "js":
	case "css":
	case "font":
	case "pdf_invoice":
	case "upload":
		require_once('resourcesManager.php');
		exit;
	case "api":
		require_once('ajaxRouter.php');
		exit;
	case "admin":
		require_once('admin.php');
		exit;
	case "account":
		require_once('account.php');
		exit;
	case "shop":
		require_once('frontOfficeController.php');
		exit;
	case "upgrade":
		require_once('upgrade.php');
		exit;
}

require_once('globalFunctions.php');

session_start();
errorReporting();

require_once('classes/project/Envs.php');
require_once('classes/DBAccess.php');
require_once('classes/Ajax.php');
require_once('classes/Link.php');
require_once('classes/Login.php');
require_once('classes/Protocoll.php');
require_once('classes/project/FormGenerator.php');
require_once('classes/project/CacheManager.php');
require_once('classes/project/Icon.php');
require_once('classes/project/Posten.php');
require_once('classes/project/Angebot.php');
require_once('classes/project/NotificationManager.php');

/* TODO: index.php neu überarbeiten und logischer aufbauen, eventuell mit htaccess mehr filtern? */

/* TODO: alle db accesses auf parameterizes sql umstellen wegen sql injections */

/* TODO: api requests hier abfangen */
$apiRequest = false;
if ($apiRequest == true) {
	header('X-Accel-Buffering: no');
}

/**
 * polyfill str_contains, maybe remove it later when support for older version drops
 * https://www.php.net/manual/en/function.str-contains.php
 */
if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle) {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }
}

/* 
 * simple caching from:
 * https://www.a-coding-project.de/ratgeber/php/simples-caching 
 * added a time stamp check and added triggers to recreate page
 */
$t = false;
$cacheFile = "cache/cache_" . md5($_SERVER['REQUEST_URI']) . ".txt";
$status = CacheManager::getCacheStatus();

if (file_exists($cacheFile) && !(count($_GET) || count($_POST)) && $t && $status == "on") {
	echo file_get_contents_utf8($cacheFile);
} else {
	if ($status == "on") {
		ob_start();
	}

	if (file_get_contents("php://input") != "") {
		$PHP_INPUT = json_decode(file_get_contents("php://input"), true);
		
		if ($PHP_INPUT != null) {
			$_POST = array_merge($_POST, $PHP_INPUT);
		}
	}

	/*
	* filters AJAX requests and delegates them to the right files
	*/
	if (isset($_POST['getReason'])) {
		Ajax::manageRequests($_POST['getReason'], $page);
	}
	else if (isset($_POST['upload'])) {
		$uploadDestination = $_POST['upload'];
		require_once('classes/Upload.php');

		/* checks which upload mechanism should be called */
		switch ($uploadDestination) {
			case "order":
				$auftragsId = (int) $_POST['auftrag'];
				$upload = new Upload();
				$upload->uploadFilesAuftrag($auftragsId);
				break;
			case "product":
				$auftragsId = (int) $_POST['produkt'];
				$upload = new Upload();
				$upload->uploadFilesProduct($auftragsId);
				break;
			case "postenAttachment":
				$key = $_POST['key'];
				$table = $_POST['tableKey'];
				Posten::addFile($key, $table);
				break;
			case "vehicle":
				$key = $_POST['key'];
				$table = $_POST['tableKey'];
				$fahrzeugnummer = Table::getIdentifierValue($table, $key);

				$auftragsnummer = $_POST['orderid'];
				$upload = new Upload();
				$upload->uploadFilesVehicle($fahrzeugnummer, $auftragsnummer);
				break;
			case "motiv":
				$motivname = $_POST['motivname'];
				$upload = new Upload();

				if (isset($_POST["motivNumber"])) {
					$upload->uploadFilesMotive($motivname, $_POST["motivNumber"]);
				} else {
					$upload->uploadFilesMotive($motivname);
				}
				break;
		}
	} else {
		if ($page == "pdf") {
			$type = $_GET['type'];
			switch ($type) {
				case "angebot":
					$angebot = new Angebot();
					$angebot->PDFgenerieren();
				break;
				case "rechnung":
					require_once('classes/project/Rechnung.php');
					if (isset($_SESSION['tempInvoice'])) {
						$rechnung = unserialize($_SESSION['tempInvoice']);

						if (!isset($_SESSION['currentInvoice_orderId'])) {
							echo "Fehler beim Generieren der Rechnung!";
							return null;
						}

						if ($rechnung->getOrderId() == $_SESSION['currentInvoice_orderId']) {
							$rechnung->PDFgenerieren();
						} else {
							$rechnung = new Rechnung();
							$rechnung->PDFgenerieren();
						}
					} else {
						$rechnung = new Rechnung();
						$rechnung->PDFgenerieren();
					}
				break;
				case "auftrag":
					require_once('classes/project/PDF_Auftrag.php');

					if (isset($_GET['id'])) {
						$id = (int) $_GET['id'];
						PDF_Auftrag::getPDF($id);
					}
				break;
			}
		} else if ($page == "cron") {
			Ajax::manageRequests("testDummy", $page);
		} else if (isLoggedIn()) {
			showPage($page);
		} else {
			showPage("login");
		}
	}

	if ($status == "on") {
		$cachedFileContent = ob_get_flush();
		file_put_contents($cacheFile, $cachedFileContent);
	}
}

function showPage($page) {
	if ($page == "test") {
		include('test.php');
		return null;
	}

	$pageDetails = DBAccess::selectQuery("SELECT id, articleUrl, pageName FROM articles WHERE src = '$page'");
	$articleUrl = "";

	/* checks if file exists */
	if ($pageDetails == null || !file_exists("./files/" . $pageDetails[0]["articleUrl"])) {
		http_response_code(404);

		$baseUrl = 'files/';
		$pageDetails['id'] = 0;
		$pageDetails["articleUrl"] = $articleUrl = "404.php";
		$pageDetails["pageName"] = $pageName = "Page not found";
	} else {
		$baseUrl = './files/';
		$pageDetails = $pageDetails[0];
		$articleUrl = $pageDetails["articleUrl"];
		$pageName = $pageDetails["pageName"];
	}
	
	include('./files/header.php');
	include($baseUrl . $articleUrl);
	include('./files/footer.php');
}
