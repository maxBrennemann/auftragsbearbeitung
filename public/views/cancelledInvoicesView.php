<?php

use Src\Classes\Link;
use Src\Classes\Project\CreditNote;
use Src\Classes\Project\Invoice;

$cancelledInvoices = Invoice::getCancelledForOrder((int) $orderId);

?>
<?php if (!empty($cancelledInvoices)): ?>
    <div class="defCont">
        <p class="font-semibold">Stornierte Rechnungen zu diesem Auftrag</p>
        <?php foreach ($cancelledInvoices as $cancelled): ?>
            <p class="mt-1">
                Rechnung Nr. <?= (int) $cancelled["invoice_number"] ?> vom <?= $cancelled["invoice_date"] ?>
                (<a class="link-primary" href="<?= Link::getResourcesShortLink("Rechnung_" . (int) $cancelled["invoice_number"] . ".pdf", "pdf") ?>" target="_blank">PDF</a>)
                <?php if ($cancelled["cancellation_number"] !== null): ?>
                    – storniert am <?= $cancelled["cancellation_date"] ?> mit Stornorechnung Nr. <?= (int) $cancelled["cancellation_number"] ?>
                    (<a class="link-primary" href="<?= CreditNote::getPdfLink((int) $cancelled["cancellation_number"], CreditNote::TYPE_CANCELLATION) ?>" target="_blank">PDF</a>):
                    <?= htmlspecialchars((string) $cancelled["reason"]) ?>
                <?php endif; ?>
            </p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
