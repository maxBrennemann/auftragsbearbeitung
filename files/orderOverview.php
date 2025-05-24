<?php

use MaxBrennemann\PhpUtilities\Tools;

use Classes\Project\Auftrag;
use Classes\Project\SearchController;

$query = Tools::get("query");
$showOffeneAuftraege = [];

if ($query !== null) {
    $results = SearchController::search("type:auftrag $query");
	$ids = [];
    foreach ($results as $result) {
        $ids[] = (int) $result["data"]["Auftragsnummer"];
    }
	$showOffeneAuftraege = Auftrag::getAuftragsliste($ids);
} else {
    $showOffeneAuftraege = Auftrag::getAuftragsliste();
}

?>
<div class="w-full bg-gray-100 p-2 rounded-md">
	<input type="number" min="1" oninput="document.getElementById('auftragsLink').href = '/auftrag?id=' + this.value;" class="input-primary">
	<a href="#" id="auftragsLink">Auftrag anzeigen</a>
	<br>
	<input type="text" oninput="document.getElementById('auftragSuche').href = '/order-overview?query=' + this.value;" class="input-primary">
	<a href="#" id="auftragSuche">Auftrag suchen</a>
	<br><br>
	<?=$showOffeneAuftraege?>
</div>