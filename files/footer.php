	<footer></footer>

	<?php 
		$files;
		if($isArticle) {
			$files = DBAccess::selectQuery("SELECT * FROM attachments_gen WHERE articleId = '${result['id']}' AND anchor = 'footer'");
		} else {
			$files = DBAccess::selectQuery("SELECT * FROM attachments WHERE articleId = '${result['id']}' AND anchor = 'footer'");
		}
		
		foreach($files as $file) {
			$link = Link::getResourcesLink($file['fileSrc'], $file['fileType']);
			
			if($file['fileType'] == 'css') {
				echo '<link rel="stylesheet" href="' . $link . '">';
			} else if($file['fileType'] == 'js') {
				echo '<script src="' . $link . '"></script>';
			} else if($file['fileType'] == 'font') {
				echo '<style> @font-face { font-family: ' . $file['fileName'] . '; src: url("' . $link . '"); }</style>';
			}
		}
	?>
</body>
</html>