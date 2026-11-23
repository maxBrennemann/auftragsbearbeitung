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
	$invoiceContacts = Kunde::getContacts($auftrag->getKundennummer());

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

<?php
/** Vorschau der Rechnung als PDF, auf allen Ansichten gleich */
$pdfPreview = function (string $meta = "") use ($invoiceId, $orderId): string {
	return <<<HTML
		<section class="defCont">
			<div class="section-head">
				<h2 class="section-title">Vorschau</h2>
				<span class="section-meta">{$meta}</span>
			</div>
			<iframe src="/api/v1/invoice/{$invoiceId}/pdf?orderId={$orderId}" id="invoicePDFPreview" class="w-full h-lvh bg-sheet border border-line-soft rounded-control" title="Rechnung als PDF"></iframe>
		</section>
	HTML;
};
?>

<?php if ($target == "create" && $invoice->isLocked()): ?>
	<div class="mx-2.5 mt-2 mb-4 flex flex-wrap items-end justify-between gap-3">
		<div>
			<span class="group-label text-text-muted">Auftrag <?= $orderId ?></span>
			<h2 class="page-title">Rechnung Nr. <?= $invoiceNumber ?></h2>
		</div>
		<div class="flex flex-wrap gap-2">
			<button data-binding="true" data-fun="completeInvoice" class="btn-secondary">Rechnung dem Auftrag wieder zuordnen</button>
			<button data-binding="true" data-fun="goBack" class="btn-ghost"><?= Icon::get("arrow-left") ?>Zurück</button>
		</div>
	</div>
	<div class="note note-danger mx-2.5 mb-2.5">
		<p class="note-head"><?= Icon::get("lock") ?>Gesperrt</p>
		<p>Diese Rechnung ist abgeschlossen und kann nicht mehr geändert werden. Für einen Preisnachlass eine Gutschrift erstellen, bei einer fehlerhaften Rechnung diese stornieren und den Auftrag neu abrechnen. Beides ist in der Auftragsansicht möglich.</p>
	</div>
	<?= \Src\Classes\Controller\TemplateController::getTemplate("cancelledInvoices", ["orderId" => $orderId]) ?>
	<?= $pdfPreview() ?>

<?php elseif ($target == "create"): ?>
	<?php $hasItems = $auftrag != null && $auftrag->getAuftragspostenData() != null; ?>
	<div class="mx-2.5 mt-2 mb-4 flex flex-wrap items-end justify-between gap-3">
		<div>
			<span class="group-label text-text-muted">Rechnung zu Auftrag <?= $orderId ?></span>
			<?php if ($invoiceNumber == 0) : ?>
				<h2 class="page-title">Neue Rechnung</h2>
				<p class="text-xs text-text-muted">Voraussichtlich Nr. <?= $nextInvoiceNumber ?>. Die Nummer wird erst beim Abschließen vergeben und kann sich bis dahin ändern.</p>
			<?php else: ?>
				<h2 class="page-title">Rechnung Nr. <?= $invoiceNumber ?></h2>
			<?php endif; ?>
		</div>
		<div class="flex flex-wrap items-center gap-2">
			<button data-binding="true" data-fun="goBack" class="btn-ghost"><?= Icon::get("arrow-left") ?>Zum Auftrag</button>
			<?php if ($hasItems): ?>
				<button class="btn-secondary" data-binding="true" data-fun="changeItemsOrder"><?= Icon::get("arrow-down-up") ?>Reihenfolge der Posten</button>
				<button data-binding="true" data-fun="completeInvoice" class="btn-primary"><?= Icon::get("check") ?>Rechnung abschließen</button>
			<?php else: ?>
				<button disabled class="btn-primary" title="Der Auftrag hat noch keine Posten.">Rechnung abschließen</button>
			<?php endif; ?>
		</div>
	</div>

	<?= \Src\Classes\Controller\TemplateController::getTemplate("cancelledInvoices", ["orderId" => $orderId]) ?>

	<div class="grid grid-cols-1 lg:grid-cols-2">
		<section class="defCont">
			<div class="section-head">
				<h2 class="section-title">Empfänger und Daten</h2>
				<a href="<?= Link::getPageLink("kunde") . "?id=" . $auftrag->getKundennummer() ?>" class="link-primary text-sm">Zum Kunden</a>
			</div>

			<div class="field-grid">
				<div class="field col-span-full">
					<span class="field-label">Firmenname</span>
					<div class="flex flex-wrap items-center gap-2">
						<input type="text" class="input-primary flex-1 min-w-48" value="<?= $kunde->getFirmenname() ?>" readonly aria-label="Firmenname">
						<button class="btn-secondary btn-sm" data-binding="true" data-fun="addAltName"><?= Icon::get("pencil", 14) ?>Kopfzeile anpassen</button>
					</div>
					<span class="field-hint">Statt des Firmennamens kann eine eigene Kopfzeile mit bis zu vier Zeilen auf der Rechnung stehen.</span>
				</div>

				<div class="field col-span-full">
					<label class="field-label" for="addressId">Rechnungsadresse</label>
					<?php if ($invoiceAddresses == null || empty($invoiceAddresses)): ?>
						<p class="field-hint">Keine Rechnungsadresse vorhanden oder unvollständig. Bei Bedarf <a href="<?= Link::getPageLink("kunde") . "?id=" . $auftrag->getKundennummer() ?>" class="link-primary">beim Kunden</a> ergänzen.</p>
					<?php else: ?>
						<select id="addressId" class="input-primary" data-write="true" data-fun="selectAddress">
							<?php foreach ($invoiceAddresses as $i => $r): ?>
								<option value="<?= $i ?>" <?= $selectedAddress == $i ? "selected" : "" ?>><?= $r ?></option>
							<?php endforeach; ?>
						</select>
					<?php endif; ?>
				</div>

				<div class="field col-span-full">
					<label class="field-label" for="contactId">Ansprechpartner</label>
					<?php if ($invoiceContacts == null || empty($invoiceContacts)): ?>
						<p class="field-hint">Keine Ansprechpartner vorhanden. Bei Bedarf <a href="<?= Link::getPageLink("kunde") . "?id=" . $auftrag->getKundennummer() ?>" class="link-primary">beim Kunden</a> ergänzen.</p>
					<?php else: ?>
						<select id="contactId" class="input-primary" data-write="true" data-fun="selectContact">
							<option value="0">Kein Ansprechpartner</option>
							<?php foreach ($invoiceContacts as $i => $r): ?>
								<option value="<?= $i ?>"><?= $r ?></option>
							<?php endforeach; ?>
						</select>
					<?php endif; ?>
				</div>

				<label class="field">
					<span class="field-label">Rechnungsdatum</span>
					<input type="date" data-write="true" data-fun="invoiceDate" class="input-primary" value="<?= $invoice->getCreationDate() ?>">
				</label>

				<div class="field col-span-full">
					<label class="field-label" for="performanceDateType">Leistungsdatum</label>
					<div class="grid grid-cols-[minmax(0,10rem)_minmax(0,14rem)] gap-2">
						<select id="performanceDateType" class="input-primary w-full" data-write="true" data-fun="selectPerformanceDateType">
							<option value="date" <?= $invoice->getPerformanceDateType() == "date" ? "selected" : "" ?>>Datum</option>
							<option value="week" <?= $invoice->getPerformanceDateType() == "week" ? "selected" : "" ?>>Kalenderwoche</option>
						</select>
						<input type="date" id="performanceDateInput" data-write="true" data-fun="serviceDate" class="input-primary w-full min-w-0 <?= $invoice->getPerformanceDateType() == "week" ? "hidden" : "" ?>" value="<?= $invoice->getPerformanceDate() ?>" aria-label="Leistungsdatum">
						<input type="week" id="performanceWeekInput" data-write="true" data-fun="serviceDateWeek" class="input-primary w-full min-w-0 <?= $invoice->getPerformanceDateType() == "date" ? "hidden" : "" ?>" value="<?= $invoice->getPerformanceDateWeekValue() ?>" aria-label="Leistungswoche">
					</div>
					<label class="field-hint inline-flex items-start gap-1.5">
						<input type="checkbox" class="mt-0.5" id="showPerformanceDate" data-write="true" data-fun="togglePerformanceDateVisibility" <?= $invoice->getShowPerformanceDate() ? "checked" : "" ?>>
						Auf der Rechnung anzeigen (immer als letzter Punkt)
					</label>
				</div>
			</div>
		</section>

		<section class="defCont">
			<div class="section-head">
				<h2 class="section-title">Texte und Anhänge</h2>
				<a href="/einstellungen#invoiceSettings" class="link-primary text-sm">Rechnungseinstellungen</a>
			</div>

			<button type="button" class="disclosure" data-target=".predefinedTexts, #texts .toggle-up, #texts .toggle-down" data-toggle="true" id="texts">
				<span>Vordefinierte Texte</span>
				<span>
					<span class="toggle-up hidden"><?= Icon::get("chevron-up") ?></span>
					<span class="toggle-down"><?= Icon::get("chevron-down") ?></span>
				</span>
			</button>
			<div class="predefinedTexts hidden pt-3">
				<p class="field-hint mb-2">Anklicken, um einen Text auf die Rechnung zu übernehmen oder wieder zu entfernen. Die Vorschau aktualisiert sich danach.</p>
				<div class="defaultInvoiceTexts flex flex-col gap-2 max-h-96 overflow-y-auto pr-1">
					<?php foreach ($invoice->getTexts() as $text): ?>
						<?php if ($text["id"] < 0) continue; /* Leistungsdatum wird links separat gesteuert */ ?>
						<div class="invoiceTexts choice-card" role="button" tabindex="0" aria-pressed="<?= $text["active"] == 1 ? "true" : "false" ?>" data-binding="true" data-fun="toggleText" data-active="<?= $text["active"] ?>" data-id="<?= $text["id"] ?>">
							<span class="choice-mark" aria-hidden="true"><?= Icon::get("check", 12) ?></span>
							<p class="max-h-20 overflow-auto flex-auto text-sm"><?= $text["text"] ?></p>
							<?php if ($text["id"] != 0) : ?>
								<div class="flex items-center gap-0.5">
									<button type="button" class="btn-ghost btn-icon btn-sm" data-id="<?= $text["id"] ?>" data-binding="true" data-fun="editText" title="Text bearbeiten" aria-label="Text bearbeiten"><?= Icon::get("pencil") ?></button>
									<button type="button" class="btn-ghost btn-icon btn-sm text-danger" data-id="<?= $text["id"] ?>" data-binding="true" data-fun="deleteText" title="Text löschen" aria-label="Text löschen"><?= Icon::get("trash-2") ?></button>
								</div>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="flex gap-2 mt-3">
					<input id="newText" class="input-primary flex-1" placeholder="Eigener Text für diese Rechnung" aria-label="Neuer Text">
					<button data-binding="true" data-fun="addText" class="btn-secondary"><?= Icon::get("plus", 14) ?>Hinzufügen</button>
				</div>
			</div>

			<button type="button" class="disclosure mt-2" data-target=".toggleVehicles, #vehicles .toggle-up, #vehicles .toggle-down" data-toggle="true" id="vehicles">
				<span>Fahrzeuge</span>
				<span>
					<span class="toggle-up hidden"><?= Icon::get("chevron-up") ?></span>
					<span class="toggle-down"><?= Icon::get("chevron-down") ?></span>
				</span>
			</button>
			<div class="toggleVehicles hidden pt-3 text-sm">
				<?php foreach ($invoice->getAttachedVehicles() as $vehicle): ?>
					<p data-id="<?= $vehicle["Nummer"] ?>" class="flex items-center gap-2">
						<?= Icon::get("car", 14) ?><span class="font-semibold tabular-nums"><?= $vehicle["Kennzeichen"] ?></span> <?= $vehicle["Fahrzeug"] ?>
					</p>
				<?php endforeach; ?>
				<?php if (count($invoice->getAttachedVehicles()) == 0): ?>
					<p class="text-text-muted">Mit diesem Auftrag sind keine Fahrzeuge verknüpft.</p>
				<?php endif; ?>
			</div>
		</section>
	</div>

	<?= $pdfPreview("wird nach jeder Änderung neu erzeugt") ?>

<?php elseif ($target == "view"): ?>
	<div class="mx-2.5 mt-2 mb-4 flex flex-wrap items-end justify-between gap-3">
		<div>
			<span class="group-label text-text-muted">Auftrag <?= $orderId ?></span>
			<h2 class="page-title">Rechnung Nr. <span id="rechnungsnummer"><?= $invoice->getNumber(); ?></span></h2>
		</div>
		<button data-binding="true" data-fun="goBack" class="btn-ghost"><?= Icon::get("arrow-left") ?>Zurück</button>
	</div>
	<?= $pdfPreview() ?>

<?php else: ?>
	<div class="note note-danger m-2.5">
		<p class="note-head"><?= Icon::get("triangle-alert") ?>Rechnung nicht gefunden</p>
		<p>Zu dieser Nummer gibt es keine Rechnung, oder sie konnte nicht geladen werden.</p>
		<button class="btn-secondary mt-2" data-binding="true" data-fun="goBack"><?= Icon::get("arrow-left") ?>Zurück</button>
	</div>
<?php endif; ?>
