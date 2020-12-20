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
	if($parts[0] == 'artikel') {
		$isArticle = true;
	}

	/*
	* filters AJAX requests and delegates them to the right files
	*/
	if (isset($_POST['getReason'])) {
		Ajax::manageRequests($_POST['getReason'], $page);
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
			$result = ["id" => -1, "articleUrl" => "test", "pageName" => "test"];
			$articleUrl = $result["articleUrl"];
			$pageName = $result["pageName"];

			require_once('classes/project/Table.php');

			include('files/header.php');

			$t = new Table("kunde", 6);
			//$t->addColumn("test", ["test"]);
			//$t->addRow(["id" => 37, "articleUrl" => "none", "pageName" => "tolle seite", "src" => "keine Qeulle", "test" => "test"]);
			//$t->addLink("https://klebefux.de");
			$t->addActionButton("delete", $identifier = "Kundennummer");

			echo $t->getTable();

			$_SESSION["undefined"] = serialize($t);
			
			?>Test
			
			<script>
				function updateIsDone(key) {
					var tableId = document.querySelector("table").dataset.name;
					//var key = event.target.dataset.key;
					var setTo = "37";
					var editTable = new AjaxCall(`getReason=table&name=${tableId}&action=update&key=${key}&setTo=${setTo}`);
					editTable.makeAjaxCall(function (response) {
						console.log(response);
					});
				}
				function deleteRow(key) {
					var tableId = document.querySelector("table").dataset.name;
					//var key = event.target.dataset.key;
					var setTo = "37";
					var editTable = new AjaxCall(`getReason=table&name=${tableId}&action=delete&key=${key}&setTo=${setTo}`);
					editTable.makeAjaxCall(function (response) {
						console.log(response);
					});
				}
			</script>
			
			<?php

			include('files/footer.php');
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
	return "0.1.4";
}
?>