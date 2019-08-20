<?php
	session_start();

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
	
	showPage($page, $isArticle);
	
	function showPage($page, $isArticle) {
		$selectQuery = "SELECT id, articleUrl, pageName FROM articles WHERE src = '$page'";
		
		require_once('classes/DBAccess.php');
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