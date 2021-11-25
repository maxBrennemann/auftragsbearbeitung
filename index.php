<?php
	session_start();

	require_once('settings.php');
	require_once('classes/DBAccess.php');
	require_once('classes/Ajax.php');
	require_once('classes/Link.php');
	require_once('classes/project/FormGenerator.php');
	require_once('classes/project/Posten.php');
	require_once('classes/project/Angebot.php');
	require_once('classes/project/NotificationManager.php');
	$isArticle = false;

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
					$rechnung = new Rechnung();
					$rechnung->PDFgenerieren();
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
	
	function showPage($page, $isArticle) {
		if ($page == "test") {
			include('test.php');
			return null;
		}

		$result = DBAccess::selectQuery("SELECT id, articleUrl, pageName FROM articles WHERE src = '$page'");
		$articleUrl = "";

		if ($result == null) {
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
	return "0.1.12";
}
?>