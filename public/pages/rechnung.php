<?php

use Src\Classes\Link;
use Src\Classes\Project\Address;
use Src\Classes\Project\Auftrag;
use Src\Classes\Project\Icon;
use Src\Classes\Project\Invoice;
use Src\Classes\Project\InvoiceNumberTracker;
use Src\Classes\Project\Kunde;
use MaxBrennemann\PhpUtilities\Tools;

$rechnungslink;

$target = Tools::get("target");
$id = (int) Tools::get("id");

$orderId = -1;
$invoiceId = -1;
$invoice = null;

if ($target == "create") {
	$orderId = $id;

	if ($id <= 0) {
		$target = "errorView";
	}

	$auftrag = new Auftrag($orderId);
	$invoiceContacts = Invoice::getContacts($auftrag->getKundennummer());

	$nextInvoiceNumber = InvoiceNumberTracker::peekNextInvoiceNumber();
	$invoice = Invoice::getInvoiceByOrderId($orderId);
	$invoiceId = $invoice->getId();
	$invoiceNumber = $invoice->getNumber();

	$invoiceAddresses = Address::getAllAdressesFormatted($auftrag->getKundennummer());
	$selectedAddress = $invoice->getAddressId();

	$kunde = new Kunde($auftrag->getKundennummer());
} else if ($target == "view") {
	$invoice = Invoice::getInvoice($id);

	if ($invoice == null) {
		$target = "errorView";
	} else {
		$invoiceId = $invoice->getId();
		$orderId = $invoice->getOrder()->getAuftragsnummer();
	}
}

?>

<input class="hidden" id="invoiceId" value="<?= $invoiceId ?>">
<input class="hidden" id="orderId" value="<?= $orderId ?>">

<?php if ($target == "create"): ?>
	<div class="defCont grid grid-cols-1 lg:grid-cols-2">
		<div class="col-span-2">
			<h3 class="font-bold">Auftrag <span><?= $orderId ?></span></h3>
			<?php if ($invoiceNumber == 0) : ?>
				<p title="Diese Nummer ist vorläufig reserviert und kann sich noch ändern.">Nächste Rechnungsnummer: <b><?= $nextInvoiceNumber ?></b></p>
			<?php else: ?>
				<p>Rechnungsnummer: <b><?= $invoiceNumber ?></b></p>
			<?php endif; ?>
			<hr class="mt-2">
		</div>
		<div>
			<h4 class="mt-2 font-semibold">Firmenname</h4>
			<div>
				<input type="text" class="input-primary mt-1 w-72" value="<?= $kunde->getFirmenname() ?>">
			</div>
			<button class="btn-primary" data-binding="true" data-fun="addAltName">Alternativtext eingeben</button>

			<h4 class="mt-2 font-semibold">Adresse auswählen</h4>
			<?php if ($invoiceAddresses == null || empty($invoiceAddresses)): ?>
				<i>Keine Rechnungsadressen vorhanden oder unvollständig. Bei Bedarf <a href="<?= Link::getPageLink("kunde") . "?id=" . $auftrag->getKundennummer() ?>" class="link-primary">beim Kunden</a> ergänzen.</i>
			<?php else: ?>
				<select id="addressId" class="input-primary w-72 mt-1" data-write="true" data-fun="selectAddress">
					<?php foreach ($invoiceAddresses as $i => $r): ?>
						<option value="<?= $i ?>" <?= $selectedAddress == $i ? "selected" : "" ?>><?= $r ?></option>
					<?php endforeach; ?>
				</select>
			<?php endif; ?>

			<h4 class="mt-2 font-semibold">Ansprechpartner auswählen</h4>
			<?php if ($invoiceContacts == null || empty($invoiceContacts)): ?>
				<i>Keine Ansprechpartner vorhanden. Bei Bedarf <a href="<?= Link::getPageLink("kunde") . "?id=" . $auftrag->getKundennummer() ?>" class="link-primary">beim Kunden</a> ergänzen.</i>
			<?php else: ?>
				<select id="contactId" class="input-primary w-72 mt-1" data-write="true" data-fun="selectContact">
					<option value="0">Kein Ansprechpartner</option>
					<?php foreach ($invoiceContacts as $i => $r): ?>
						<option value="<?= $i ?>"><?= $r ?></option>
					<?php endforeach; ?>
				</select>
			<?php endif; ?>

			<h4 class="mt-2 font-semibold">Rechnungsdatum festlegen</h4>
			<input type="date" data-write="true" data-fun="invoiceDate" class="input-primary mt-1" value="<?= $invoice->getCreationDate() ?>">
			<h4 class="mt-2 font-semibold">Leistungsdatum festlegen</h4>
			<input type="date" data-write="true" data-fun="serviceDate" class="input-primary mt-1" value="<?= $invoice->getPerformanceDate() ?>">
		</div>

		<div>
			<div class="mt-3">
				<h4 class="font-semibold inline-flex items-center" data-target=".predefinedTexts, #texts .toggle-up, #texts .toggle-down" data-toggle="true" id="texts">
					<p class="py-2 cursor-pointer select-none">Vordefinierte Texte</p>
					<span class="cursor-pointer">
						<span class="toggle-up hidden"><?= Icon::getDefault("iconChevronUp") ?></span>
						<span class="toggle-down"><?= Icon::getDefault("iconChevronDown") ?></span>
					</span>
				</h4>
				<div class="predefinedTexts hidden bg-white p-2 rounded-md">
					<p>Den Text zum (ab)wählen einmal anklicken. Die Rechnungsvorschau wird dann neu generiert.</p>
					<div class="defaultInvoiceTexts grid grid-flow-row gap-4 mt-2 max-h-80 overflow-y-scroll">
						<?php foreach ($invoice->getTexts() as $text): ?>
							<div class="invoiceTexts bg-gray-100 rounded-xl cursor-pointer p-3 mr-1 select-none flex" title="Übernehmen" data-binding="true" data-fun="toggleText" data-active="<?= $text["active"] ?>" data-id="<?= $text["id"] ?>">
								<p class="max-h-20 overflow-auto flex-auto"><?= $text["text"] ?></p>
								<div class="pl-3 flex items-center">
									<?php if ($text["id"] != 0) : ?>
										<button class="btn-edit" data-id="<?= $text["id"] ?>" data-binding="true" data-fun="editText"><?= Icon::getDefault("iconEdit") ?></button>
										<button class="btn-delete ml-1" data-id="<?= $text["id"] ?>" data-binding="true" data-fun="deleteText"><?= Icon::getDefault("iconDelete") ?></button>
									<?php endif; ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
					<div class="mt-2">
						<input id="newText" class="input-primary">
						<button data-binding="true" data-fun="addText" class="btn-primary">Hinzufügen</button>
					</div>
				</div>
			</div>

			<div class="mt-3">
				<h4 class="font-semibold inline-flex items-center" data-target=".toggleVehicles, #vehicles .toggle-up, #vehicles .toggle-down" data-toggle="true" id="vehicles">
					<p class="py-2 cursor-pointer select-none">Fahrzeuge</p>
					<span class="cursor-pointer">
						<span class="toggle-up hidden"><?= Icon::getDefault("iconChevronUp") ?></span>
						<span class="toggle-down"><?= Icon::getDefault("iconChevronDown") ?></span>
					</span>
				</h4>
				<div class="toggleVehicles hidden">
					<?php foreach ($invoice->getAttachedVehicles() as $vehicle): ?>
						<p data-id="<?= $vehicle["Nummer"] ?>">
							<?= $vehicle["Kennzeichen"] ?> <?= $vehicle["Fahrzeug"] ?>
						</p>
					<?php endforeach; ?>
					<?php if (count($invoice->getAttachedVehicles()) == 0): ?>
						<p>Keine Fahrzeuge verknüpft</p>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

	<div class="defCont">
		<h3 class="font-bold">Rechnungsoptionen</h3>
		<div class="mt-3">
			<?php if ($auftrag != null && $auftrag->getAuftragspostenData() != null): ?>
				<button data-binding="true" data-fun="completeInvoice" class="btn-primary">Rechnung <?= $invoiceNumber == 0 ? "abschließen" : "neu generieren" ?></button>
				<button class="btn-primary" data-binding="true" data-fun="changeItemsOrder">Reihenfolge</button>
			<?php else: ?>
				<button disabled class="btn-primary">Rechnung abschließen</button>
			<?php endif; ?>
			<button data-binding="true" data-fun="goBack" class="btn-cancel">Abbrechen</button>
			<div class="float-right">
				<a href="/einstellungen#invoiceSettings" class="link-primary">Rechnungseinstellungen</a>
				<a href="<?= Link::getPageLink("kunde") . "?id=" . $auftrag->getKundennummer() ?>" class="link-primary ml-2">Zum Kunden</a>
			</div>
		</div>
	</div>
	<div class="mt-3">
		<iframe src="/api/v1/invoice/<?= $invoiceId ?>/pdf?orderId=<?= $orderId ?>" id="invoicePDFPreview" class="w-full h-lvh"></iframe>
	</div>
<?php elseif ($target == "view"): ?>
	<p class="my-2 font-semibold">Rechnung <span id="rechnungsnummer"><?= $invoice->getNumber(); ?></span></p>
	<button data-binding="true" data-fun="goBack" class="btn-cancel">Zurück</button>
	<button class="btn-primary" data-fun="completeInvoice" data-binding="true">PDF neu erstellen</button>
	<iframe src="/api/v1/invoice/<?= $invoiceId ?>/pdf?orderId=<?= $orderId ?>" class="w-full h-lvh mt-2" id="invoicePDFPreview"></iframe>
<?php else: ?>
	<p>Es ist ein unerwarteter Fehler aufgetreten oder die Rechnungsnummer existiert nicht.</p>
	<button class="btn-primary" data-binding="true" data-fun="goBack" type="submit">Zurück</button>
<?php endif; ?>