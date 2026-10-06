<div class="col-span-6">
    <div class="hidden bg-red-300 m-2 p-2 rounded-md gap-2 items-center" id="showMissingFileWarning">
        <?= \Src\Classes\Project\Icon::get("iconWarning", 25, 25) ?>
        <div class="ml-2">
            <p>Die Rechnung konnte nicht gefunden werden!</p>
            <button data-fun="recreateInvoice" data-binding="true" class="btn-primary mt-1">Neu erstellen</button>
        </div>
    </div>
    <div class="defCont" id="orderFinished">
        <p>Auftrag <?= $auftrag->getAuftragsnummer() ?> wurde abgeschlossen. Rechnungsnummer: <span id="rechnungsnummer"><?= $auftrag->getInvoiceNumber() ?></span></p>
        <button class="btn-primary mt-2" data-fun="showAuftrag" data-binding="true">Auftrag anzeigen</button>
        <?php
        $invoiceLink = "Rechnung_" . $auftrag->getInvoiceNumber() . ".pdf";
        $invoiceLink = \Src\Classes\Link::getResourcesShortLink($invoiceLink, "pdf");
        ?>
        <a class="link-primary" href="<?= $invoiceLink ?>" target="_blank">Zur Rechnungs-PDF</a>
    </div>
    <div class="defCont">
        <div id="orderPaymentState">
            <?php if (!$auftrag->isPaid()): ?>
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
            <?php else: ?>
                <p>Die Rechnung wurde am <span class="info-badge"><?= $auftrag->getPaymentDate() ?></span> per <span class="info-badge"><?= $auftrag->getPaymentType() ?></span> bezahlt.</p>
                <button class="btn-primary mt-2" data-binding="true" data-fun="setUnpaid">Als unbezahlt markieren</button>
            <?php endif; ?>
        </div>
    </div>
    <?php
    $creditNotes = array_filter(
        \Src\Classes\Project\CreditNote::getForInvoice($auftrag->getInvoiceId()),
        fn($creditNote) => $creditNote["type"] === \Src\Classes\Project\CreditNote::TYPE_CREDIT
    );
    $creditedNet = array_sum(array_map(fn($creditNote) => (float) $creditNote["net_amount"], $creditNotes));
    ?>
    <div class="defCont">
        <p class="font-semibold">Gutschriften zur Rechnung</p>
        <?php if (empty($creditNotes)): ?>
            <p class="mt-1">Zu dieser Rechnung gibt es noch keine Gutschrift.</p>
        <?php else: ?>
            <table class="mt-1 w-full text-left">
                <thead>
                    <tr>
                        <th class="pr-3">Nr.</th>
                        <th class="pr-3">Datum</th>
                        <th class="pr-3">Grund</th>
                        <th class="pr-3 text-right">Betrag (netto)</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($creditNotes as $creditNote): ?>
                        <tr>
                            <td class="pr-3"><?= (int) $creditNote["credit_number"] ?></td>
                            <td class="pr-3"><?= date("d.m.Y", strtotime($creditNote["creation_date"])) ?></td>
                            <td class="pr-3"><?= htmlspecialchars($creditNote["reason"]) ?></td>
                            <td class="pr-3 text-right">-<?= number_format((float) $creditNote["net_amount"], 2, ',', '.') ?> €</td>
                            <td class="whitespace-nowrap">
                                <a class="link-primary" href="<?= \Src\Classes\Project\CreditNote::getPdfLink((int) $creditNote["credit_number"]) ?>" target="_blank">PDF</a>
                                <button class="btn-primary ml-2" data-binding="true" data-fun="sendCreditNote" data-id="<?= (int) $creditNote["id"] ?>">Per E-Mail senden</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p class="mt-1">Summe der Gutschriften: <span class="info-badge"><?= number_format($creditedNet, 2, ',', '.') ?> € netto</span></p>
        <?php endif; ?>

        <div class="mt-3 flex flex-wrap items-end gap-2">
            <label class="flex flex-col">
                <span class="text-sm">Betrag (netto, in €)</span>
                <input type="number" min="0.01" step="0.01" id="creditNoteAmount" class="input-primary w-40">
            </label>
            <label class="flex flex-col flex-1 min-w-64">
                <span class="text-sm">Grund (erscheint auf der Gutschrift)</span>
                <input type="text" maxlength="255" id="creditNoteReason" class="input-primary" placeholder="z. B. Preisnachlass wie vereinbart">
            </label>
            <label class="flex items-center gap-1">
                <input type="checkbox" id="creditNoteSendMail">
                <span>per E-Mail an den Kunden senden</span>
            </label>
            <button class="btn-primary" data-binding="true" data-fun="createCreditNote">Gutschrift erstellen</button>
        </div>
        <p class="text-xs text-gray-500 mt-1">Die Rechnung selbst bleibt unverändert. Die Gutschrift erhält eine eigene Nummer aus dem Rechnungsnummernkreis und kann danach nicht mehr geändert werden.</p>
    </div>
    <div class="defCont">
        <p class="font-semibold">Rechnung stornieren</p>
        <p class="mt-1">Ist die Rechnung fehlerhaft, wird sie mit einer Stornorechnung aufgehoben. Die Rechnung selbst bleibt erhalten, der Auftrag wird wieder freigegeben und kann danach neu abgerechnet werden. Für einen reinen Preisnachlass genügt eine Gutschrift.</p>
        <div class="mt-2 flex flex-wrap items-end gap-2">
            <label class="flex flex-col flex-1 min-w-64">
                <span class="text-sm">Grund (erscheint auf der Stornorechnung)</span>
                <input type="text" maxlength="255" id="cancelInvoiceReason" class="input-primary" placeholder="z. B. Falsche Rechnungsadresse">
            </label>
            <label class="flex items-center gap-1">
                <input type="checkbox" id="cancelInvoiceSendMail">
                <span>per E-Mail an den Kunden senden</span>
            </label>
            <button class="btn-cancel" data-binding="true" data-fun="cancelInvoice">Rechnung stornieren</button>
        </div>
    </div>
    <?= \Src\Classes\Controller\TemplateController::getTemplate("cancelledInvoices", ["orderId" => $auftrag->getAuftragsnummer()]) ?>
    <div class="defCont">
        <embed type="application/pdf" src="<?= $invoiceLink ?>" width="100%" height="800" id="invoiceEmbed">
    </div>
</div>