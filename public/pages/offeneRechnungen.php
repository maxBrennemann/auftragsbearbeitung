<?php

use Src\Classes\Controller\TemplateController;
use Src\Classes\Project\Settings;

?>

<section class="defCont">
    <div class="section-head">
        <h2 class="section-title">Offene Rechnungen</h2>
        <?= TemplateController::getTemplate("inputSwitch", [
            "id" => "toggleDueInvoices",
            "name" => "Alle offenen Rechnungen",
            "write" => "showDueInvoices",
        ]); ?>
    </div>
    <div id="openInvoiceTable" class="overflow-auto max-h-[70vh]"></div>
</section>

<input type="number" value="<?= Settings::get('invoice.vatRate') ?>" hidden id="inputVatHidden">