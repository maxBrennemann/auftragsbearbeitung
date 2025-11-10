<h3 class="font-semibold mb-2">Suchergebnisse:</h3>
<?php 

use Src\Classes\Link;

foreach ($results as $result) : ?>
<?php switch ($result["type"]): ?>
<?php case "auftrag": ?>
    <p>Auftrag <a href="<?= Link::getPageLink("auftrag") ?>?id=<?= $result["data"]["Auftragsnummer"] ?>" class="link-primary"><?= $result["data"]["Auftragsnummer"] ?></a> "<?= $result["data"]["Auftragsbezeichnung"] ?>"</p>
    <?php break; ?>
<?php case "kunde": ?>
    <?php break; ?>
<?php case "produkt": ?>
    <?php break; ?>
<?php case "wiki_articles": ?>
    <?php break; ?>
<?php endswitch; ?>
<?php endforeach; ?>