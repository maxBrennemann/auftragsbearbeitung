<?php

use Classes\Link;

use MaxBrennemann\PhpUtilities\Tools;

use Classes\Project\Auftrag;
use Classes\Project\Invoice;
use Classes\Project\Address;
use Classes\Project\Icon;
use Classes\Project\InvoiceNumberTracker;

$rechnungslink;

$target = Tools::get("target");
$id = Tools::get("id");

if ($target == "create") {
	$auftrag = new Auftrag($id);
	$invoiceAddresses = Address::getAllAdressesFormatted($auftrag->getKundennummer());

	$nextInvoiceNumber = InvoiceNumberTracker::peekNextInvoiceNumber();
	$invoice = Invoice::getInvoice($id);
	$invoiceId = $invoice->getId();
}

if ($target == "view") {
	$invoice = Invoice::getInvoice($id);
	$invoiceId = $invoice->getId();
}

if ($target == "create"): ?>
	<div class="defCont">
		<div>
			<h3 class="font-bold">Auftrag <span id="orderId"><?= $id ?></span></h3>
			<p title="Diese Nummer ist vorläufig reserviert und kann sich noch ändern.">Nächste Rechnungsnummer: <b><?= $nextInvoiceNumber ?></b></p>
			<input class="hidden" id="invoiceId" value="<?= $invoiceId ?>">
			<input class="hidden" id="orderId" value="<?= $id ?>">
		</div>

		<div class="innerDefCont">
			<p>Adresse auswählen</p>
			<?php if ($invoiceAddresses == null || empty($invoiceAddresses)): ?>
				<i>Keine Rechnungsadressen vorhanden oder unvollständig. Bei Bedarf <a href="<?= Link::getPageLink("kunde") . "?id=" . $auftrag->getKundennummer() ?>" class="link-primary">beim Kunden</a> ergänzen.</i>
			<?php else: ?>
				<select id="addressId" class="input-primary-new w-72" data-binding="true" data-fun="selectAddress">
					<?php foreach ($invoiceAddresses as $i => $r): ?>
						<option value="<?= $i ?>"><?= $r ?></option>
					<?php endforeach; ?>
				</select>
			<?php endif; ?>

			<p class="mt-2">Rechnungsdatum festlegen</p>
			<input type="date" data-write="true" data-fun="invoiceDate" class="input-primary-new" value="<?= $invoice->getCreationDate() ?>">
			<p class="mt-2">Leistungsdatum festlegen</p>
			<input type="date" data-write="true" data-fun="serviceDate" class="input-primary-new" value="<?= $invoice->getPerformanceDate() ?>">
		</div>

		<hr>

		<div class="mt-3">
			<h4 class="font-semibold inline-flex items-center" data-fun="togglePredefinedTexts" data-binding="true">
				<span>Vordefinierte Texte</span>
				<span class="cursor-pointer">
					<span class="toggle-up hidden"><?= Icon::getDefault("iconChevronUp") ?></span>
					<span class="toggle-down"><?= Icon::getDefault("iconChevronDown") ?></span>
				</span>
			</h4>
			<div class="predefinedTexts hidden">
				<p>Den Text zum (ab)wählen einmal anklicken. Die Rechnungsvorschau wird dann neu generiert.</p>
				<div class="defaultInvoiceTexts grid grid-cols-3 gap-4 mt-2">
					<?php foreach ($invoice->getTexts() as $text): ?>
						<p class="invoiceTexts bg-white rounded-xl cursor-pointer p-3 select-none" title="Übernehmen" data-binding="true" data-fun="toggleText" data-active="<?= $text["active"] ?>" data-id="<?= $text["id"] ?>"><?= $text["text"] ?></p>
					<?php endforeach; ?>
				</div>
				<div class="my-2">
					<input id="newText" class="input-primary-new">
					<button data-binding="true" data-fun="addText" class="btn-primary-new">Hinzufügen</button>
				</div>
			</div>
			<div class="predefinedTexts"></div>
		</div>

		<hr>

		<div class="mt-3">
			<?php if ($auftrag != null && $auftrag->getAuftragspostenData() != null): ?>
				<button data-binding="true" data-fun="completeInvoice" class="btn-primary-new">Rechnung abschließen</button>
			<?php else: ?>
				<button disabled class="btn-primary-new">Rechnung abschließen</button>
			<?php endif; ?>
			<button onclick="window.history.go(-1); return false;" class="btn-cancel">Abbrechen</button>
		</div>
	</div>
	<div class="mt-3">
		<iframe src="<?= Link::getPageLink('pdf') . "?type=invoice&invoiceId=$invoiceId&orderId=$id" ?>" id="invoicePDFPreview"></iframe>
	</div>
<?php elseif ($target == "view"): ?>
	<p class="my-2 font-semibold">Rechnung <span id="rechnungsnummer"><?= $invoice->getNumber(); ?></span></p>
	<iframe src="<?= Link::getPageLink('pdf') . "?type=invoice&invoiceId=$invoiceId&orderId=$id" ?>"></iframe>
<?php else: ?>
	<p>Es ist ein unerwarteter Fehler aufgetreten.</p>
	<button class="btn-primary-new" onclick="window.history.go(-1); return false;" type="submit">Zurück</button>
<?php endif; ?>