<?php

use Src\Classes\Project\Kunde;
use Src\Classes\Project\SearchController;
use MaxBrennemann\PhpUtilities\Tools;
use Src\Classes\Controller\TemplateController;

$query = Tools::get("query");
$query = urldecode($query);
$customers = [];

if ($query != null) {
    $results = SearchController::search("type:kunde $query", 50);
    foreach ($results as $result) {
        $id = (int) $result["data"]["Kundennummer"];
        $customers[] = new Kunde($id);
    }
} else {
    $customers = Kunde::getAllCustomerOverviews();
}

?>

<?php $hasQuery = $query !== null && $query !== ""; ?>
<section class="defCont">
    <div class="section-head">
        <h2 class="section-title">Kunden</h2>
        <span class="section-meta"><?= $hasQuery ? count($customers) . " Treffer für „" . htmlspecialchars($query) . "“" : count($customers) . " Kunden" ?></span>
    </div>
    <div class="flex flex-wrap items-center gap-3 mb-4">
        <?= TemplateController::getTemplate("search", [
            "searchId" => "search",
            "searchValue" => $query,
            "placeHolder" => "Name, Ort oder Kundennummer",
        ]); ?>
        <span class="field-hint">Mindestens 3 Zeichen, mit Enter suchen</span>
        <?php if ($hasQuery): ?>
            <a class="link-primary text-sm" href="<?= \Src\Classes\Link::getPageLink("customer-overview") ?>">Alle Kunden anzeigen</a>
        <?php endif; ?>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-3">
        <?php foreach ($customers as $customer) : ?>
            <?= TemplateController::getTemplate("customerCardTemplate", [
                "customer" => $customer,
                "targetLink" => true,
            ]); ?>
        <?php endforeach; ?>
    </div>
</section>
