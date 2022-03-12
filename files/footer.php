	</main>
	<?php
		$listmaker   =		Link::getPageLink("listmaker");
		$einstellungen =	Link::getPageLink("einstellungen");
		$neuesAngebot =		Link::getPageLink("angebot");
		$neuesProdukt =		Link::getPageLink("neues-produkt");
		$diagramme =		Link::getPageLink("diagramme");
		$leistungen =		Link::getPageLink("leistungen");
		$changelog = 		Link::getPageLink("changelog");
	?>
	<footer class="moveToSide">
		<div class="linkBundle">
			<a class="linkBundleLink" href="<?=Link::getPageLink('')?>">← Zur Main Page</a><br>
			<a class="linkBundleLink" href="https://klebefux.de">Zu klebefux</a><br>
			<a class="linkBundleLink" href="https://b-schriftung.de">Zu b-schriftung</a>
		</div>
		<div class="linkBundle">
			<a class="linkBundleLink" href="<?=$listmaker?>">Listen</a><br>
			<a class="linkBundleLink" href="<?=$einstellungen?>">Einstellungen</a><br>
			<a class="linkBundleLink" href="<?=$neuesAngebot?>">Neues Angebot</a><br>
		</div>
		<div class="linkBundle">
			<a class="linkBundleLink" href="<?=$neuesProdukt?>">Neues Produkt</a><br>
			<a class="linkBundleLink" href="<?=$diagramme?>">Diagramme</a><br>
			<a class="linkBundleLink" href="<?=$leistungen?>">Leistungen</a><br>
		</div>
		<a href="<?=$changelog?>" style="font-size: 0.5em; color: grey"><?=getCurrentVersion()?></a>
	</footer>

	<?php 
		$files;
		if($isArticle) {
			$files = DBAccess::selectQuery("SELECT * FROM attachments_gen WHERE articleId = '${result['id']}' AND anchor = 'footer'");
		} else {
			$files = DBAccess::selectQuery("SELECT * FROM attachments WHERE articleId = '${result['id']}' AND anchor = 'footer'");
		}
		
		foreach($files as $file) {
			$link = Link::getResourcesShortLink($file['fileSrc'], $file['fileType']);
			
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