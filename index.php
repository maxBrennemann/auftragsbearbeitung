<?php
	session_start();

	require_once('settings.php');
	require_once('classes/DBAccess.php');
	$isArticle = false;
	
	if(isset($_GET['page'])) {
		$page = $_GET['page'];
		$parts = explode('/', $page);
		$page = $parts[count($parts) - 1];
		if($parts[0] == 'artikel') {
			$isArticle = true;
		}
	} else {
		$page = '';
	}

	/*
	* filters AJAX requests and delegates them to the right files
	*/
	if (isset($_POST['getReason'])) {
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
		include($baseUrl . $articleUrl);
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
?>