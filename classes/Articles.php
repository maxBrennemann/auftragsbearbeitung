<?php

require_once('DBAccess.php');

class Articles {
	
	function __construct() {
		
	}
	
	public static function addArticle() {
		if(isset($_POST['pageName'])) {
			$pageName = $_POST['pageName'];
			$src = $_POST['src'];
			
			$maxId = DBAccess::selectQuery("SELECT MAX(id) FROM generated_articles");
			$maxId = $maxId[0]["MAX(id)"];
			
			$uploaddir = "files/generated/";
			$fileName = $pageName . "_" . $maxId . ".php";
			
			if(move_uploaded_file($_FILES['srcfile']['tmp_name'], $uploaddir . $fileName)) {
				$dir = "Directory:" . $fileName;
			}
			
			$params = (object) array(
				'articleUrl' => $fileName,
				'pageName' => $pageName,
				'src' => $src
			);
			
			DBAccess::insertQuery("INSERT INTO generated_articles (articleUrl, pageName, src) VALUES (:articleUrl, :pageName, :src)", $params);
			
			if(isset($_FILES['attachedFiles'])) {
				$total = count($_FILES['attachedFiles']['name']);
				for($i = 0 ; $i < $total; $i++) {
					$tmpFilePath = $_FILES['upload']['tmp_name'][$i];

					if($tmpFilePath != ""){
						/* https://stackoverflow.com/questions/5427222/finding-extension-of-uploaded-file-php */
						$extension = end((explode(".", $tmpFilePath))); // extra () to prevent notice
						
						switch($extension) {
							case "js":
								$uploaddir = REWRITE_BASE . "files/js/" . $name . "_" . $maxId . ".js";
								$fileName = $name . "_" . $maxId . ".js";
								break;
							case "css":
								$uploaddir = REWRITE_BASE . "files/css/" . $name . "_" . $maxId . ".css";
								$fileName = $name . "_" . $maxId . ".css";
								break;
							case "png":
							case "svg":
							case "jpg":
							case "jpeg":
								$uploaddir = REWRITE_BASE . "files/img/upload/";
								break;
						}
						
						if(move_uploaded_file($tmpFilePath, $newFilePath)) {
							DBAccess::insertQuery("INSERT INTO ATTACHMENTS (articleId, fileSrc, fileType) VALUES (:maxId, :fileName, :extension)");
						}
					}
				}
			}
		}
	}
	
	public function editArticle($articleId) {
		
	}
}

?>
