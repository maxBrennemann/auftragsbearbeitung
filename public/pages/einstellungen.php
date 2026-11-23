<?php

use Src\Classes\Controller\TemplateController;
use Src\Classes\Link;
use Src\Classes\Project\Icon;
use Src\Classes\Project\Image;
use Src\Classes\Project\InvoiceNumberTracker;
use Src\Classes\Project\Settings;
use Src\Classes\Project\User;

$companyLogo = Image::getLogo();

/** Ein Einstellungsfeld, das bei Änderung sofort gespeichert wird (changeSetting) */
$settingField = function (string $key, string $label, string $type = "text", string $extraLabel = ""): string {
	$value = htmlspecialchars((string) Settings::get($key), ENT_QUOTES);
	$input = $type === "textarea"
		? "<textarea class=\"input-primary\" data-write=\"true\" data-fun=\"changeSetting\" data-setting=\"{$key}\">{$value}</textarea>"
		: "<input type=\"{$type}\" class=\"input-primary\" value=\"{$value}\" data-write=\"true\" data-fun=\"changeSetting\" data-setting=\"{$key}\">";
	return <<<HTML
		<label class="field">
			<span class="field-label">{$label}{$extraLabel}</span>
			{$input}
		</label>
	HTML;
};

$sections = [
	"invoiceSettings" => "Unternehmen und Rechnung",
	"mailSettings" => "E-Mail-Versand",
	"pdfTexts" => "PDF-Texte",
	"orderTypeSettings" => "Auftragstypen",
	"wholesalerSettings" => "Einkauf",
	"categorySettings" => "Kategorien",
	"userSettings" => "Mitarbeiter",
	"timeTrackingSettings" => "Zeiterfassung",
	"fileSettings" => "Dateien und Sicherung",
];

?>
<nav class="mx-2.5 mt-2 mb-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm" aria-label="Abschnitte der Einstellungen">
	<span class="group-label text-text-muted">Springen zu</span>
	<?php foreach ($sections as $id => $title): ?>
		<a class="link-primary font-semibold" href="#<?= $id ?>"><?= $title ?></a>
	<?php endforeach; ?>
</nav>

<section class="defCont scroll-mt-28" id="invoiceSettings">
	<div class="section-head">
		<h2 class="section-title">Unternehmen und Rechnung</h2>
		<span class="section-meta">Änderungen werden sofort gespeichert</span>
	</div>

	<div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
		<div>
			<span class="group-label mb-2">Unternehmensdaten</span>
			<div class="field-grid">
				<div class="col-span-full"><?= $settingField("company.name", "Firmenname") ?></div>
				<div class="col-span-full"><?= $settingField("company.address", "Adresse") ?></div>
				<?= $settingField("company.zip", "PLZ") ?>
				<?= $settingField("company.city", "Ort") ?>
				<?= $settingField("company.country", "Land") ?>
				<?= $settingField("company.phone", "Telefonnummer", "tel") ?>
				<?= $settingField("company.email", "E-Mail", "email") ?>
				<?= $settingField("company.website", "Website") ?>
				<div class="col-span-full"><?= $settingField("company.imprint", "Absenderzeile auf der Rechnung", "textarea") ?></div>
			</div>
		</div>

		<div>
			<span class="group-label mb-2">Bank</span>
			<div class="field-grid">
				<div class="col-span-full"><?= $settingField("company.bank", "Bank") ?></div>
				<div class="col-span-full"><?= $settingField("company.IBAN", "IBAN") ?></div>
				<?= $settingField("company.BIC", "BIC") ?>
				<?= $settingField("company.Kontoinhaber", "Kontoinhaber") ?>
			</div>

			<span class="group-label mt-5 mb-2">Rechnungen</span>
			<div class="field-grid">
				<?= $settingField("company.UstIdNr", "USt-IdNr.") ?>
				<?= $settingField("invoice.vatRate", "Umsatzsteuersatz [%]", "text", TemplateController::getTemplate("infoButton", ["infoKey" => "vat_info"])) ?>
				<?= $settingField("invoice.dueDate", "Zahlungsziel [Tage]", "number") ?>
				<?= $settingField("invoice.defaultWage", "Stundenlohn [€]") ?>
				<div class="col-span-full"><?= $settingField("company.invoiceCopyTo", "Rechnungskopie senden an") ?></div>
			</div>

			<div class="mt-5 note note-warn">
				<p class="note-head"><?= Icon::get("triangle-alert") ?>Achtung</p>
				<label class="field-label mt-1" for="newInvoiceNumber">Aktuelle Rechnungsnummer</label>
				<div class="flex gap-2 mt-1">
					<input type="number" id="newInvoiceNumber" class="input-primary flex-1" value="<?= InvoiceNumberTracker::getMaxInvoiceNumber() ?>">
					<button class="btn-secondary whitespace-nowrap" data-fun="sendNewInvoiceNumber" data-binding="true">Zählerstand setzen</button>
				</div>
				<p class="field-hint mt-1">Überschreibt den aktuellen Zählerstand. Die nächste Rechnung erhält die nächsthöhere Nummer.</p>
			</div>

			<div class="mt-3 note note-info">
				<p class="note-head"><?= Icon::get("info") ?>Hinweis</p>
				<label class="field-label mt-1" for="legacyDataCutoff">Auswertungen: Altdaten ausblenden</label>
				<input type="date" id="legacyDataCutoff" class="input-primary w-full mt-1"
					value="<?= Settings::get('statistics.legacyDataCutoff') ?>"
					data-write="true" data-fun="changeSetting" data-setting="statistics.legacyDataCutoff">
				<p class="field-hint mt-1">
					Rechnungen vor diesem Datum fließen nicht in Umsatz-, Zahlungsdauer-, offene-Rechnungen- und
					Top-Kunden-Auswertungen unter /diagramme ein, z. B. bei unvollständigen Übernahmedaten aus einem
					alten Rechnungsprogramm. Leer lassen, um alle Daten einzubeziehen.
				</p>
			</div>
		</div>
	</div>

	<span class="group-label mt-8 mb-2">Logo und Browser-Icon</span>
	<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
		<div class="sheet">
			<p class="field-label mb-2"><?= Icon::get("image") ?>Firmen- und Rechnungslogo</p>
			<div class="relative mb-3 p-3 grid place-items-center bg-[#f6f7f8] border border-line-soft rounded-control <?= $companyLogo ? "" : "hidden" ?>" id="companyLogo">
				<img src="<?= $companyLogo ? Link::getResourcesShortLink($companyLogo, "upload") : "" ?>" class="max-h-20 object-contain" alt="Aktuelles Firmenlogo">
				<button type="button" class="btn-danger btn-icon btn-sm absolute top-1.5 right-1.5" data-fun="deleteLogo" data-binding="true" title="Logo löschen" aria-label="Logo löschen">
					<?= Icon::get("trash-2") ?>
				</button>
			</div>
			<?= TemplateController::getTemplate("uploadFile", [
				"target" => "companyLogo",
				"singleFile" => true,
				"accept" => "image/*"
			]); ?>
		</div>

		<div class="sheet">
			<p class="field-label mb-2"><?= Icon::get("panels-top-left") ?>Favicon (Symbol im Browser-Tab)</p>
			<div class="relative mb-3 p-3 grid place-items-center bg-[#f6f7f8] border border-line-soft rounded-control" id="favicon">
				<img src="<?= Link::getResourcesShortLink("favicon.png", "img") ?>" class="w-10 h-10 object-contain" alt="Aktuelles Favicon">
				<button type="button" class="btn-secondary btn-icon btn-sm absolute top-1.5 right-1.5" data-fun="deleteFavicon" data-binding="true" title="Auf Standard-Favicon zurücksetzen" aria-label="Auf Standard-Favicon zurücksetzen">
					<?= Icon::get("rotate-ccw") ?>
				</button>
			</div>
			<?= TemplateController::getTemplate("uploadFile", [
				"target" => "favicon",
				"singleFile" => true,
				"accept" => "image/*"
			]); ?>
		</div>
	</div>
</section>

<section class="defCont scroll-mt-28" id="mailSettings">
	<div class="section-head">
		<h2 class="section-title">E-Mail-Versand</h2>
		<span class="section-meta">für Rechnungen, Angebote und Mahnungen</span>
	</div>
	<div class="field-grid md:grid-cols-2 xl:grid-cols-3">
		<?= $settingField("mail.host", "SMTP-Host") ?>
		<?= $settingField("mail.port", "SMTP-Port", "number") ?>
		<?= $settingField("mail.username", "SMTP-Benutzername") ?>
		<label class="field">
			<span class="field-label">SMTP-Passwort</span>
			<input type="password" class="input-primary" autocomplete="new-password"
				placeholder="Unverändert lassen, um es zu behalten"
				data-write="true" data-fun="changeSetting" data-setting="mail.password">
			<span class="field-hint">
				<?php
				try {
				    echo Settings::get('mail.password') !== '' ? "Ein Passwort ist hinterlegt." : "Kein Passwort hinterlegt.";
				} catch (\RuntimeException $e) {
				    echo "<span class=\"text-danger font-semibold\">Das gespeicherte Passwort kann nicht entschlüsselt werden (SETTINGS_ENCRYPTION_KEY fehlt oder wurde geändert). Bitte neu eingeben.</span>";
				}
				?>
			</span>
		</label>
		<?= $settingField("mail.fromAddress", "Absenderadresse", "email") ?>
		<?= $settingField("mail.fromName", "Absendername") ?>
	</div>
</section>

<section class="defCont scroll-mt-28" id="pdfTexts">
	<div class="section-head">
		<h2 class="section-title">PDF-Texte</h2>
		<span class="section-meta">Textbausteine, die auf PDFs erscheinen können</span>
	</div>
	<div id="pdfTextsCont" class="overflow-x-auto"></div>
</section>

<section class="defCont scroll-mt-28" id="orderTypeSettings">
	<div class="section-head">
		<h2 class="section-title">Auftragstypen</h2>
	</div>
	<div id="orderTypes" class="overflow-x-auto"></div>
</section>

<section class="defCont scroll-mt-28" id="wholesalerSettings">
	<div class="section-head">
		<h2 class="section-title">Einkauf</h2>
		<span class="section-meta">Händler und Bezugsquellen</span>
	</div>
	<div id="wholesalerTypes" class="overflow-x-auto"></div>
</section>

<section class="defCont scroll-mt-28" id="categorySettings">
	<div class="section-head">
		<h2 class="section-title">Kategorien</h2>
	</div>
	<div id="categoryTree" class="sheet mb-3"></div>
	<div class="flex flex-wrap items-end gap-3">
		<label class="field">
			<span class="field-label">Neue Kategorie</span>
			<input type="text" class="input-primary w-56" id="newCategory">
		</label>
		<label class="field">
			<span class="field-label">Unterhalb von</span>
			<select id="parentCategory" class="input-primary w-56"></select>
		</label>
		<button id="addCategory" class="btn-primary"><?= Icon::get("plus") ?>Kategorie hinzufügen</button>
	</div>
</section>

<section class="defCont scroll-mt-28" id="userSettings">
	<div class="section-head">
		<h2 class="section-title">Mitarbeiter</h2>
	</div>
	<div id="userTable" class="overflow-x-auto"></div>
</section>

<section class="defCont scroll-mt-28" id="timeTrackingSettings">
	<div class="section-head">
		<h2 class="section-title">Zeiterfassung</h2>
		<span class="section-meta">gilt nur für dich</span>
	</div>
	<div class="switchCont">
		<?= TemplateController::getTemplate("inputSwitch", [
		    "id" => "showTimeTracking",
		    "name" => "Laufende Arbeitszeit oben in der Kopfzeile anzeigen",
		    "value" => Settings::get("showTimeTracking", User::getCurrentUserId()) ? "checked" : "",
		    "binding" => "startStopTime",
		]); ?>
	</div>
</section>

<section class="defCont scroll-mt-28" id="fileSettings">
	<div class="section-head">
		<h2 class="section-title">Dateien und Sicherung</h2>
		<span class="section-meta" id="showFilesInfo"></span>
	</div>
	<a href="#" download="temp_file_name" id="download_db" class="hidden">Datenbank herunterladen</a>
	<a href="#" download="temp_file_name" id="download_files" class="hidden">Dateien herunterladen</a>
	<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
		<div class="sheet">
			<span class="group-label mb-2">Sicherung</span>
			<div class="flex flex-wrap gap-2">
				<button class="btn-secondary" data-binding="true" data-fun="downloadDatabase"><?= Icon::get("database") ?>Datenbank herunterladen</button>
				<button class="btn-secondary" data-binding="true" data-fun="downloadAllFiles"><?= Icon::get("files") ?>Alle Dateien (ZIP)</button>
			</div>
		</div>
		<div class="sheet">
			<span class="group-label mb-2">Pflege</span>
			<div class="flex flex-wrap gap-2">
				<button id="clearFiles" data-binding="true" class="btn-secondary">Dateien aufräumen</button>
				<button id="adjustFiles" data-binding="true" class="btn-secondary">Dateinamen anpassen</button>
				<button data-binding="true" data-fun="deleteCache" class="btn-secondary">Cache löschen</button>
			</div>
		</div>
	</div>
</section>
