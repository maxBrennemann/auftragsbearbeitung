<?php

use Src\Classes\Link;
use Src\Classes\Project\Icon;
use Src\Classes\Project\Kunde;
use Src\Classes\Controller\TemplateController;
use MaxBrennemann\PhpUtilities\Tools;

$customerId = (int) Tools::get("id");
$customer = null;

try {
	$customer = new Kunde($customerId);
} catch (Exception $e) {
	$customerId = 0;
}

if ($customerId == 0): ?>
	<div class="note note-warn m-2.5">
		<p class="note-head"><?= Icon::get("triangle-alert") ?>Kunde nicht gefunden</p>
		<p>Zu dieser Kundennummer gibt es keinen Eintrag. <a href="<?= Link::getPageLink("neuer-kunde") ?>" class="link-primary">Neuen Kunden anlegen</a></p>
	</div>
<?php else:
	$orderCount = count($customer->getOrderIds());
	$displayName = $customer->getFirmenname() ?: trim($customer->getVorname() . " " . $customer->getNachname());

	/** Ein Eingabefeld der Kundendaten: [id, Label, Wert, volle Breite?, Typ] */
	$field = function (string $id, string $label, ?string $value, bool $wide = false, string $type = "text", string $extra = ""): string {
		$value = htmlspecialchars((string) $value, ENT_QUOTES);
		$span = $wide ? " col-span-full" : "";
		return <<<HTML
			<label class="field{$span}">
				<span class="field-label">{$label}{$extra}</span>
				<input class="input-primary" type="{$type}" id="{$id}" value="{$value}" autocomplete="off" data-initial="{$value}">
			</label>
		HTML;
	};
?>
	<input type="hidden" id="idCustomer" value="<?= $customer->getKundennummer() ?>">

	<div class="mx-2.5 mt-2 mb-4 flex flex-wrap items-end justify-between gap-3">
		<div>
			<span class="group-label text-text-muted">Kunde <?= $customer->getKundennummer() ?></span>
			<h2 class="page-title"><?= $displayName ?></h2>
			<p class="text-xs text-text-muted"><?= $orderCount == 1 ? "1 Auftrag" : "$orderCount Aufträge" ?></p>
		</div>
		<div class="relative flex items-center gap-2">
			<button class="btn-primary" data-fun="createNewOrder" data-binding="true"><?= Icon::get("plus") ?>Neuer Auftrag</button>
			<?= TemplateController::getTemplate("toggleOptions", [
				"toggleClass" => "",
				"toggleBtnId" => "customerOptionsBtn",
				"toggleId" => "moreOptions",
				"bindToggle" => true,
				"options" => [
					[
						"text" => "Mit anderem Kunden zusammenführen",
						"icon" => "merge",
						"function" => "mergeCustomer",
					],
					[
						"text" => "Kunde löschen",
						"icon" => "trash-2",
						"function" => "deleteCustomer",
						"danger" => true,
						"dividerBefore" => true,
					],
				],
			]) ?>
		</div>
	</div>

	<div class="grid grid-cols-1 xl:grid-cols-2">
		<section class="defCont" id="showKundendaten">
			<div class="section-head">
				<h2 class="section-title">Kundendaten</h2>
				<span class="section-meta">Kundennummer <?= $customer->getKundennummer() ?></span>
			</div>
			<span class="group-label mb-2">Name</span>
			<div class="field-grid">
				<?= $field("prename", "Vorname", $customer->getVorname()) ?>
				<?= $field("lastname", "Nachname", $customer->getNachname()) ?>
				<?= $field("companyname", "Firmenname", $customer->getFirmenname(), true) ?>
			</div>
			<span class="group-label mt-5 mb-2">Kontakt</span>
			<div class="field-grid">
				<?= $field("email", "E-Mail", $customer->getEmail(), false, "email") ?>
				<?= $field("invoiceEmail", "Rechnungs-E-Mail", $customer->getInvoiceEmail(), false, "email", TemplateController::getTemplate("infoButton", ["infoKey" => "invoice_mail"])) ?>
				<?= $field("phoneLandline", "Telefon Festnetz", $customer->getTelefonFestnetz(), false, "tel") ?>
				<?= $field("phoneMobile", "Telefon Mobil", $customer->getTelefonMobil(), false, "tel") ?>
				<?= $field("website", "Website", $customer->getWebsite()) ?>
				<?= $field("fax", "Fax", $customer->getFax(), false, "tel") ?>
			</div>
			<div class="flex gap-2 mt-5">
				<button class="btn-primary" data-binding="true" data-fun="saveCustomerData">Speichern</button>
				<button class="btn-secondary" data-binding="true" data-fun="resetCustomerData">Änderungen verwerfen</button>
			</div>
		</section>

		<section class="defCont" id="addNewAddress">
			<div class="section-head">
				<h2 class="section-title">Adressen</h2>
			</div>
			<div id="addressTable" class="overflow-x-auto"></div>
		</section>

		<section class="defCont xl:col-span-2" id="ansprechpartner">
			<div class="section-head">
				<h2 class="section-title">Ansprechpartner</h2>
			</div>
			<div id="contactPersonTable" class="w-full overflow-x-auto"></div>
		</section>

		<section class="defCont xl:col-span-2" id="auftraege">
			<div class="section-head">
				<h2 class="section-title">Aufträge</h2>
				<button class="btn-secondary btn-sm" data-fun="createNewOrder" data-binding="true"><?= Icon::get("plus", 14) ?>Neuer Auftrag</button>
			</div>
			<?php if ($orderCount == 0): ?>
				<p class="text-sm text-text-muted">Für diesen Kunden gibt es noch keine Aufträge.</p>
			<?php else: ?>
				<div class="<?= $orderCount > 8 ? "cards-collapsed" : "" ?>" id="orderCards">
					<?= $customer->getOrderCards() ?>
				</div>
				<?php if ($orderCount > 8): ?>
					<button class="btn-secondary mt-3" data-fun="showAllOrders" data-binding="true" id="showAllOrders">Alle <?= $orderCount ?> Aufträge anzeigen</button>
				<?php endif; ?>
			<?php endif; ?>
		</section>

		<section class="defCont" id="fahrzeuge">
			<div class="section-head">
				<h2 class="section-title">Fahrzeuge</h2>
			</div>
			<div id="vehiclesTable" class="overflow-x-auto"></div>
		</section>

		<section class="defCont" id="notizen">
			<div class="section-head">
				<h2 class="section-title">Notizen</h2>
			</div>
			<textarea class="input-primary w-full min-h-32" data-write="true" data-fun="setCustomerNote" aria-label="Notizen zum Kunden"><?= $customer->getNotizen() ?></textarea>
		</section>

		<section class="defCont xl:col-span-2" id="farben">
			<div class="section-head">
				<h2 class="section-title">Farben</h2>
			</div>
			<div id="colorTable" class="overflow-x-auto"></div>
		</section>
	</div>
<?php endif; ?>
