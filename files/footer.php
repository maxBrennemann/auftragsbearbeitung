	</main>
	<?php
		/* Die Links werden im Header generiert */
	?>
	<footer class="moveToSide">
		<div class="linkBundle">
			<a class="linkBundleLink hover:underline" href="<?=Classes\Link::getPageLink('')?>">← Zur Main Page</a><br>
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
</body>
</html>