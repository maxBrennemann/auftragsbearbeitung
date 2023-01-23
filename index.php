<?php

session_start();
errorReporting();

require_once('settings.php');
require_once('classes/DBAccess.php');
require_once('classes/Ajax.php');
require_once('classes/Link.php');
require_once('classes/Protocoll.php');
require_once('classes/project/FormGenerator.php');
require_once('classes/project/CacheManager.php');
require_once('classes/project/Icon.php');
require_once('classes/project/Posten.php');
require_once('classes/project/Angebot.php');
require_once('classes/project/NotificationManager.php');
$isArticle = false;

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
* Before: page was submitted via $_GET paramter, but now the REQUEST_URI is read;
* $url is splitted into the REQUEST_URI and the parameter part
*/
$url = $_SERVER['REQUEST_URI'];
$url = explode('?', $url, 2);
$page = str_replace(REWRITE_BASE . SUB_URL, "", $url[0]);
$parts = explode('/', $page);
$page = $parts[count($parts) - 1];
if ($parts[0] == 'artikel') {
	$isArticle = true;
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

	if (isset($_POST['logout_session'])) {
		Login::handleLogout();
	}

	if (isset($_POST['login_session'])) {
		Login::manageRequest();
		return null;
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
		if (strcmp($uploadDestination, "order") == 0) {
			$auftragsId = (int) $_POST['auftrag'];
			$upload = new Upload();
			$upload->uploadFilesAuftrag($auftragsId);
		} else if (strcmp($uploadDestination, "product") == 0) {
			$auftragsId = (int) $_POST['produkt'];
			$upload = new Upload();
			$upload->uploadFilesProduct($auftragsId);
		} else if (strcmp($uploadDestination, "postenAttachment") == 0) {
			$key = $_POST['key'];
			$table = $_POST['tableKey'];
			Posten::addFile($key, $table);
		}  else if (strcmp($uploadDestination, "vehicle") == 0) {
			$key = $_POST['key'];
			$table = $_POST['tableKey'];
			$fahrzeugnummer = Table::getIdentifierValue($table, $key);

			$auftragsnummer = $_POST['orderid'];
			$upload = new Upload();
			$upload->uploadFilesVehicle($fahrzeugnummer, $auftragsnummer);
		} else if (strcmp($uploadDestination, "motiv") == 0) {
			$motivname = $_POST['motivname'];

			$upload = new Upload();
			if (isset($_POST["motivNumber"])) {
				$upload->uploadFilesMotive($motivname, $_POST["motivNumber"]);
			} else {
				$upload->uploadFilesMotive($motivname);
			}
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
			
		} else if (isLoggedIn()) {
			showPage($page, $isArticle);
		} else {
			showPage("login", false);
		}
	}

	if ($status == "on") {
		$cachedFileContent = ob_get_flush();
		file_put_contents($cacheFile, $cachedFileContent);
	}
}

function showPage($page, $isArticle) {
	if ($page == "test") {
		include('test.php');
		return null;
	}

	$result = DBAccess::selectQuery("SELECT id, articleUrl, pageName FROM articles WHERE src = '$page'");
	$articleUrl = "";

	/* checks if file exists */
	if ($result == null || !file_exists("files/" . $result[0]["articleUrl"])) {
		/* generated articles does not exist in this project */
		//$baseUrl = 'files/generated/';
		//$result = DBAccess::selectQuery("SELECT id, articleUrl, pageName FROM generated_articles WHERE src = '$page'");

		http_response_code(404);

		$baseUrl = 'files/';
		$result['id'] = 0;
		$result["articleUrl"] = $articleUrl = "404.php";
		$result["pageName"] = $pageName = "Page not found";
	} else {
		$baseUrl = 'files/';
		$result = $result[0];
		$articleUrl = $result["articleUrl"];
		$pageName = $result["pageName"];
	}
	
	include('files/header.php');
	include($baseUrl . $articleUrl);
	include('files/footer.php');
}

/*
* https://stackoverflow.com/questions/2236668/file-get-contents-breaks-up-utf-8-characters
*/
function file_get_contents_utf8($fn) {
	$content = file_get_contents($fn);
	return mb_convert_encoding($content, 'UTF-8', mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));
}

function isLoggedIn() {
	if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] == true) {
		return true;	
	}
	return false;
}

function getCurrentVersion() {
	return CURRENTVERSION;
}

function errorReporting() {
	if (defined('ERRORREPORTING') && ERRORREPORTING) {
		error_reporting(E_ALL);
		ini_set('display_errors', '1');
	}
}

function getParameter($value, $type = "GET", $default = "") {
	switch ($type) {
		case "GET":
			if (isset($_GET[$value])) {
				return $_GET[$value];
			}
			break;
		case "POST":
			if (isset($_POST[$value])) {
				return $_POST[$value];
			}
			break;
	}
	return $default;
}

function insertTemplate($path, array $parameters = []) {
	if (file_exists($path)) {
		extract($parameters);
		include($path);
	}
}

?>