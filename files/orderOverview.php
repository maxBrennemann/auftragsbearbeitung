<?php

use MaxBrennemann\PhpUtilities\Tools;

use Classes\Link;
use Classes\Project\Auftrag;
use Classes\Project\SearchController;

$query = Tools::get("query");
$customers = [];

if ($query !== null) {
    $results = SearchController::initSearch("order", $query, 10);
    foreach ($results as $resultType) {
        foreach ($resultType as $result) {
            $customers[] = new Auftrag($result["row"]["Auftragsnummer"]);
        }
    }
} else {
    $showOffeneAuftraege = Auftrag::getAuftragsliste();
}

?>
<div class="w-full bg-gray-100 p-2 rounded-md">
	<input type="number" min="1" oninput="document.getElementById('auftragsLink').href = '<?=$auftragAnzeigen?>?id=' + this.value;">
	<a href="#" id="auftragsLink">Auftrag anzeigen</a>
	<br>
	<input type="text" oninput="document.getElementById('auftragSuche').href = '<?=$auftragAnzeigen?>?query=' + this.value;">
	<a href="#" id="auftragSuche">Auftrag suchen</a>
	<br><br>
	<?=$showOffeneAuftraege?>
</div>