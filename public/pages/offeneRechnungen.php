<?php

use Src\Classes\Controller\TemplateController;
use Src\Classes\Project\Settings;

?>

<div class="w-full">
    <div class="px-2 rounded-sm ml-2 mt-1">
        <?= TemplateController::getTemplate("inputSwitch", [
            "id" => "toggleDueInvoices",
            "name" => "Alle offenen Rechnungen",
            "write" => "showDueInvoices",
        ]); ?>
    </div>
    <div id="openInvoiceTable" class="overflow-x-scroll h-136 mt-2"></div>
</div>

<input type="number" value="<?= Settings::get('invoice.vatRate') ?>" hidden id="inputVatHidden">