<?php

use Classes\Controller\TemplateController;

$customers = [];

?>

<div>
    <h3 class="font-semibold">Neuen Kunden wählen</h3>
    <p class="text-xs">Hier über die Suche einen neuen Kunden auswählen.</p>

    <div class="mt-2">
        <div class="search bg-gray-50 px-4 py-2 rounded-md inline-flex w-full items-center">
            <p class="mr-3">Suche:</p>
            <?= TemplateController::getTemplate("search", [
                "searchId" => "searchCustomers",
            ]); ?>
        </div>
        <div class="grid grid-cols-3 xl:grid-cols-4 w-full">
            <?php foreach ($customers as $customer) : ?>
                <?= TemplateController::getTemplate("customerCardTemplate", [
                    "customer" => $customer,
                ]); ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>