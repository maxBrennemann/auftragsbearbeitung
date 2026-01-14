<?php

use Src\Classes\Project\Auftrag;
use Src\Classes\Project\SearchController;
use MaxBrennemann\PhpUtilities\Tools;

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
    $showOffeneAuftraege = Auftrag::getAuftragsliste([]);
}

?>
<div class="w-full bg-gray-100 p-3 rounded-md">
	<input type="number" min="1" class="input-primary" id="idInput">
	<button class="btn-primary" data-binding="true" data-fun="showOrder">Auftrag anzeigen</button>
	<br>
	<input type="text" class="input-primary" value="<?= $query ?>" id="queryInput">
	<button class="btn-primary" data-binding="true" data-fun="searchOrder">Auftrag suchen</button>
	<br><br>
	<?=$showOffeneAuftraege?>
</div>