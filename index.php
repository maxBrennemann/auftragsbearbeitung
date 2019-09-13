<?php
	session_start();

	require_once('settings.php');
	require_once('classes/DBAccess.php');
	require_once('classes/Ajax.php');
	require_once('classes/Link.php');
	require_once('classes/project/FormGenerator.php');
	require_once('classes/project/Posten.php');
	$isArticle = false;

	/*
	* Before: page was submitted via $_GET paramter, but now the REQUEST_URI is read;
	* $url is splitted into the REQUEST_URI and the parameter part
	*/
	$url = $_SERVER['REQUEST_URI'];
	$url = explode('?', $url, 2);
	$page = str_replace(REWRITE_BASE . "content/", "", $url[0]);
	$parts = explode('/', $page);
	$page = $parts[count($parts) - 1];
	if($parts[0] == 'artikel') {
		$isArticle = true;
	}

	/*
	* filters AJAX requests and delegates them to the right files
	*/
	if (isset($_POST['getReason'])) {
		Ajax::manageRequests($_POST['getReason']);
	} else {
		showPage($page, $isArticle);
	}
	
	
	function showPage($page, $isArticle) {
		$selectQuery = "SELECT id, articleUrl, pageName FROM articles WHERE src = '$page'";
		
		$result = DBAccess::selectQuery($selectQuery);
		
		if($result == null) {
			$baseUrl = 'files/generated/';
			$result = DBAccess::selectQuery("SELECT id, articleUrl, pageName FROM generated_articles WHERE src = '$page'");
		} else {
			$baseUrl = 'files/';
		}
		
		$result = $result[0];
		$articleUrl = $result["articleUrl"];
		$pageName = $result["pageName"];
		
		if($articleUrl == NULL) {
			http_response_code(404);
			$articleUrl = "404.html";
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
?>