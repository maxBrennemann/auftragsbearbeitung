	</main>
	<?php
		use Classes\Link;

		$listmaker =		Link::getPageLink("listmaker");
		$einstellungen =	Link::getPageLink("einstellungen");
		$neuesAngebot =		Link::getPageLink("angebot");
		$neuesProdukt =		Link::getPageLink("neues-produkt");
		$diagramme =		Link::getPageLink("diagramme");
		$leistungenLinks =	Link::getPageLink("leistungen");
		$changelog = 		Link::getPageLink("changelog");
	?>
	<footer class="moveToSide">
		<div class="linkBundle">
			<a class="linkBundleLink hover:underline" href="<?=Link::getPageLink('')?>">← Zur Main Page</a><br>
			<a class="linkBundleLink hover:underline" href="https://klebefux.de">Zu klebefux</a><br>
			<a class="linkBundleLink hover:underline" href="https://b-schriftung.de">Zu b-schriftung</a>
		</div>
		<div class="linkBundle">
			<a class="linkBundleLink hover:underline" href="<?=$listmaker?>">Listen</a><br>
			<a class="linkBundleLink hover:underline" href="<?=$einstellungen?>">Einstellungen</a><br>
			<a class="linkBundleLink hover:underline" href="<?=$neuesAngebot?>">Neues Angebot</a><br>
		</div>
		<div class="linkBundle">
			<a class="linkBundleLink hover:underline" href="<?=$neuesProdukt?>">Neues Produkt</a><br>
			<a class="linkBundleLink hover:underline" href="<?=$diagramme?>">Diagramme</a><br>
			<a class="linkBundleLink hover:underline" href="<?=$leistungenLinks?>">Leistungen</a><br>
		</div>
		<a class="hover:underline" href="<?=$changelog?>" style="font-size: 0.5em; color: grey"><?=getCurrentVersion()?></a>
	</footer>
	<?php

	if ($duration != false) {
		echo "<script>console.log('Page loaded in " . $duration . " seconds');</script>";
	}

	?>
</body>
</html>