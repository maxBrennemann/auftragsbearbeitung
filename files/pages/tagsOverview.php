<?php

// alle tags anzeigen
// tags anklickbar -> anzeigen welche Produkte diesen Tag verwenden
// häufigkeit von Tag anzeigen
// neu crawlen btn einführen
// tags häufigkeit und produkte dazu speichern

use MaxBrennemann\PhpUtilities\DBAccess;

$query = "SELECT `content` FROM module_sticker_tags";
$tags = DBAccess::selectQuery($query);

?>
<script type="module" src="<?=Classes\Link::getResourcesShortLink("tags.js", "js")?>"></script>
<p>Anzahl Tags: <?=count($tags)?></p>
<?php foreach ($tags as $tag): ?>
    <dt class="inline-block py-1 px-2 border-none rounded-lg bg-cyan-500 m-2"><?=$tag["content"]?></dt>
<?php endforeach; ?>