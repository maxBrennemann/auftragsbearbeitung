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
<section class="defCont">
	<div class="section-head">
		<h2 class="section-title">Aufträge</h2>
		<?php if ($query !== null): ?>
			<a class="link-primary text-sm" href="<?= \Src\Classes\Link::getPageLink("order-overview") ?>">Alle Aufträge anzeigen</a>
		<?php endif; ?>
	</div>
	<div class="flex flex-wrap items-end gap-4 mb-4">
		<div class="flex items-end gap-2">
			<label class="field">
				<span class="field-label">Auftragsnummer</span>
				<input type="number" min="1" class="input-primary w-32" id="idInput">
			</label>
			<button class="btn-secondary" data-binding="true" data-fun="showOrder">Öffnen</button>
		</div>
		<div class="flex items-end gap-2">
			<label class="field">
				<span class="field-label">Stichwort</span>
				<input type="text" class="input-primary w-64" value="<?= htmlspecialchars((string) $query) ?>" id="queryInput" placeholder="Kunde, Bezeichnung …">
			</label>
			<button class="btn-secondary" data-binding="true" data-fun="searchOrder"><?= \Src\Classes\Project\Icon::get("search", 14) ?>Suchen</button>
		</div>
	</div>
	<div class="overflow-x-auto"><?= $showOffeneAuftraege ?></div>
</section>
