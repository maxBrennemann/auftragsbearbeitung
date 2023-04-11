	</main>
	<?php
		/* Die Links werden im Header generiert */
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
			<a class="linkBundleLink" href="<?=$leistungenLinks?>">Leistungen</a><br>
		</div>
		<a href="<?=$changelog?>" style="font-size: 0.5em; color: grey"><?=getCurrentVersion()?></a>
	</footer>
</body>
</html>