<?php

use Classes\Link;
use Classes\Project\Address;
use Classes\Project\Auftrag;
use Classes\Project\Icon;
use Classes\Project\Invoice;
use Classes\Project\InvoiceNumberTracker;
use Classes\Project\Kunde;
use MaxBrennemann\PhpUtilities\Tools;

$rechnungslink;

$target = Tools::get("target");
$id = Tools::get("id");

if ($target == "create") {
    $auftrag = new Auftrag($id);
    $invoiceContacts = Invoice::getContacts($auftrag->getKundennummer());

    $nextInvoiceNumber = InvoiceNumberTracker::peekNextInvoiceNumber();
    $invoice = Invoice::getInvoice($id);
    $invoiceId = $invoice->getId();
    $invoiceNumber = $invoice->getNumber();

    $invoiceAddresses = Address::getAllAdressesFormatted($auftrag->getKundennummer());
    $selectedAddress = $invoice->getAddressId();

    $kunde = new Kunde($auftrag->getKundennummer());
}

if ($target == "view") {
    $invoice = Invoice::getInvoice($id);
    $invoiceId = $invoice->getId();
} ?>

<input class="hidden" id="invoiceId" value="<?= $invoiceId ?>">
<input class="hidden" id="orderId" value="<?= $id ?>">

<?php if ($target == "create"): ?>
	<div class="defCont">
		<div>
			<h3 class="font-bold">Auftrag <span><?= $id ?></span></h3>
			<?php if ($invoiceNumber == 0) : ?>
				<p title="Diese Nummer ist vorläufig reserviert und kann sich noch ändern.">Nächste Rechnungsnummer: <b><?= $nextInvoiceNumber ?></b></p>
			<?php else: ?>
				<p>Rechnungsnummer: <b><?= $invoiceNumber ?></b></p>
			<?php endif; ?>
		</div>

		<p class="mt-2">Firmenname</p>
		<div>
			<input type="text" class="input-primary mt-1 w-72" value="<?= $kunde->getFirmenname() ?>">
		</div>
		<button class="btn-primary" data-binding="true" data-fun="addAltName">Alternativtext eingeben</button>

		<p class="mt-2">Adresse auswählen</p>
		<?php if ($invoiceAddresses == null || empty($invoiceAddresses)): ?>
			<i>Keine Rechnungsadressen vorhanden oder unvollständig. Bei Bedarf <a href="<?= Link::getPageLink("kunde") . "?id=" . $auftrag->getKundennummer() ?>" class="link-primary">beim Kunden</a> ergänzen.</i>
		<?php else: ?>
			<select id="addressId" class="input-primary w-72 mt-1" data-write="true" data-fun="selectAddress">
				<?php foreach ($invoiceAddresses as $i => $r): ?>
					<option value="<?= $i ?>" <?= $selectedAddress == $i ? "selected" : "" ?>><?= $r ?></option>
				<?php endforeach; ?>
			</select>
		<?php endif; ?>

		<p class="mt-2">Ansprechpartner auswählen</p>
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

		<p class="mt-2">Rechnungsdatum festlegen</p>
		<input type="date" data-write="true" data-fun="invoiceDate" class="input-primary mt-1" value="<?= $invoice->getCreationDate() ?>">
		<p class="mt-2">Leistungsdatum festlegen</p>
		<input type="date" data-write="true" data-fun="serviceDate" class="input-primary mt-1" value="<?= $invoice->getPerformanceDate() ?>">

		<hr class="mt-2">

		<div class="mt-3">
			<h4 class="font-semibold inline-flex items-center" data-target=".predefinedTexts, #texts .toggle-up, #texts .toggle-down" data-toggle="true" id="texts">
				<p class="py-2 cursor-pointer">Vordefinierte Texte</p>
				<span class="cursor-pointer">
					<span class="toggle-up hidden"><?= Icon::getDefault("iconChevronUp") ?></span>
					<span class="toggle-down"><?= Icon::getDefault("iconChevronDown") ?></span>
				</span>
			</h4>
			<div class="predefinedTexts hidden">
				<p>Den Text zum (ab)wählen einmal anklicken. Die Rechnungsvorschau wird dann neu generiert.</p>
				<div class="defaultInvoiceTexts grid grid-flow-row gap-4 mt-2">
					<?php foreach ($invoice->getTexts() as $text): ?>
						<div class="invoiceTexts bg-white rounded-xl cursor-pointer p-3 select-none flex" title="Übernehmen" data-binding="true" data-fun="toggleText" data-active="<?= $text["active"] ?>" data-id="<?= $text["id"] ?>">
							<p class="max-h-20 overflow-auto flex-auto"><?= $text["text"] ?></p>
							<div class="pl-3 flex items-center">
								<?php if ($text["id"] != 0) : ?>
									<button class="btn-edit"><?= Icon::getDefault("iconEdit") ?></button>
									<button class="btn-delete ml-1"><?= Icon::getDefault("iconDelete") ?></button>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="my-2">
					<input id="newText" class="input-primary">
					<button data-binding="true" data-fun="addText" class="btn-primary">Hinzufügen</button>
				</div>
			</div>
		</div>

		<hr>

		<div class="mt-3">
			<h4 class="font-semibold inline-flex items-center" data-target=".toggleVehicles, #vehicles .toggle-up, #vehicles .toggle-down" data-toggle="true" id="vehicles">
				<p class="py-2 cursor-pointer">Fahrzeuge</p>
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
			</div>
		</div>

		<hr>

		<div class="mt-3">
			<?php if ($auftrag != null && $auftrag->getAuftragspostenData() != null): ?>
				<button data-binding="true" data-fun="completeInvoice" class="btn-primary">Rechnung <?= $invoiceNumber == 0 ? "abschließen" : "neu generieren" ?></button>
				<button class="btn-primary" data-binding="true" data-fun="changeItemsOrder">Reihenfolge</button>
			<?php else: ?>
				<button disabled class="btn-primary">Rechnung abschließen</button>
			<?php endif; ?>
			<button onclick="window.history.go(-1); return false;" class="btn-cancel">Abbrechen</button>
			<div class="float-right">
				<a href="/einstellungen#invoiceSettings" class="link-primary">Rechnungseinstellungen</a>
				<a href="<?= Link::getPageLink("kunde") . "?id=" . $auftrag->getKundennummer() ?>" class="link-primary ml-2">Zum Kunden</a>
			</div>
		</div>
	</div>
	<div class="mt-3">
		<iframe src="/api/v1/invoice/<?= $invoiceId ?>/pdf?orderId=<?= $id ?>" id="invoicePDFPreview" class="w-full h-lvh"></iframe>
	</div>
<?php elseif ($target == "view"): ?>
	<p class="my-2 font-semibold">Rechnung <span id="rechnungsnummer"><?= $invoice->getNumber(); ?></span></p>
	<button onclick="window.history.go(-1); return false;" class="btn-cancel">Zurück</button>
	<button class="btn-primary" data-fun="completeInvoice" data-binding="true">PDF neu erstellen</button>
	<iframe src="/api/v1/invoice/<?= $invoiceId ?>/pdf?orderId=<?= $id ?>" class="w-full h-lvh mt-2" id="invoicePDFPreview"></iframe>
<?php else: ?>
	<p>Es ist ein unerwarteter Fehler aufgetreten.</p>
	<button class="btn-primary" onclick="window.history.go(-1); return false;" type="submit">Zurück</button>
<?php endif; ?>