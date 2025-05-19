<div class="col-span-6">
    <div class="hidden bg-red-300 m-2 p-2 rounded-md gap-2 items-center" id="showMissingFileWarning">
        <?= \Classes\Project\Icon::get("iconWarning", 25, 25) ?>
        <div class="ml-2">
            <p>Die Rechnung konnte nicht gefunden werden!</p>
            <button data-fun="recreateInvoice" data-binding="true" class="btn-primary mt-1">Neu erstellen</button>
            <button data-fun="resetInvoice" data-binding="true" class="btn-primary mt-1">Rechnung zurücksetzen</button>
        </div>
    </div>
    <div class="defCont" id="orderFinished">
        <p>Auftrag <?= $auftrag->getAuftragsnummer() ?> wurde abgeschlossen. Rechnungsnummer: <span id="rechnungsnummer"><?= $auftrag->getInvoiceNumber() ?></span></p>
        <button class="btn-primary mt-2" data-fun="showAuftrag" data-binding="true">Auftrag anzeigen</button>
        <?php
        $invoiceLink = "Rechnung_" . $auftrag->getInvoiceId() . ".pdf";
        $invoiceLink = \Classes\Link::getResourcesShortLink($invoiceLink, "pdf");
        ?>
        <a class="link-primary" href="<?= $invoiceLink ?>" target="_blank">Zur Rechnungs-PDF</a>
    </div>
    <?php if (!$auftrag->getIsPayed()): ?>
        <div class="defCont">
            <div id="orderPaymentState">
                <p>Die Rechnung wurde noch nicht beglichen.</p>
                <label>
                    <input type="date" id="inputPayDate" class="input-primary">
                </label>
                <select id="paymentType" class="input-primary">
                    <option value="unbezahlt">Unbezahlt</option>
                    <option value="ueberweisung">Überweisung</option>
                    <option value="bar">Bar</option>
                    <option value="paypal">PayPal</option>
                    <option value="kreditkarte">Kreditkarte</option>
                    <option value="amazonpay">AmazonPay</option>
                    <option value="weiteres">Weiteres</option>
                </select>
                <button class="btn-primary" data-binding="true" data-fun="setPayed">Rechnung wurde bezahlt</button>
            </div>
        </div>
    <?php else: ?>
        <div class="defCont">
            <p>Die Rechnung wurde am <?= $auftrag->getPaymentDate() ?> mit <?= $auftrag->getPaymentType() ?> bezahlt.</p>
        </div>
    <?php endif; ?>
    <div class="defCont">
        <embed type="application/pdf" src="<?= $invoiceLink ?>" width="100%" height="400" id="invoiceEmbed">
    </div>
</div>