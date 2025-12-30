<?php

use Src\Classes\Project\Kunde;
use Src\Classes\Project\SearchController;
use MaxBrennemann\PhpUtilities\Tools;
use Src\Classes\Controller\TemplateController;

$query = Tools::get("query");
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

<div class="search bg-gray-50 px-4 py-2 rounded-md inline-flex w-full items-center">
    <p class="mr-3">Suche:</p>
    <?= TemplateController::getTemplate("search", [
        "searchId" => "search",
        "searchValue" => Tools::get("query"),
    ]); ?>
</div>
<div class="grid grid-cols-3 xl:grid-cols-4 w-full">
    <?php foreach ($customers as $customer) : ?>
        <?= TemplateController::getTemplate("customerCardTemplate", [
        "customer" => $customer,
    ]); ?>
    <?php endforeach; ?>
</div>