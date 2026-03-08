<div class="w-full">
    <button class="btn-primary" data-fun="showDueInvoices" data-binding="true">Fällige Rechnungen</button>
    <div id="openInvoiceTable" class="overflow-x-scroll h-136"></div>
</div>
<?php
$__vat = \Src\Classes\Project\Settings::get('invoice.vatRate');
?>
<script>
    window.invoiceVatRate = <?= (float) $__vat ?>;
</script>