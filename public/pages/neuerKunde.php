<?php

use Src\Classes\Project\Icon;

/**
 * Ein Formularfeld. $form ist "cForm" (Firma/Verein) oder "pForm" (Privatkunde); gesendet werden die name-Attribute.
 */
$input = function (string $form, string $name, string $label, string $type = "text", string $span = ""): string {
	return <<<HTML
		<label class="field {$span}">
			<span class="field-label">{$label}</span>
			<input type="{$type}" form="{$form}" name="{$name}" autocomplete="some-unrecognised-value" class="input-primary">
		</label>
	HTML;
};

$salutation = function (string $form): string {
	return <<<HTML
		<label class="field">
			<span class="field-label">Anrede</span>
			<select form="{$form}" class="input-primary" name="anrede">
				<option value="0">Herr</option>
				<option value="1">Frau</option>
				<option value="5">Divers</option>
				<option value="2">Firma</option>
				<option value="3">Verein</option>
				<option value="4">Sonstiges</option>
			</select>
		</label>
	HTML;
};

$address = function (string $form) use ($input): string {
	return $input($form, "street", "Straße")
		. $input($form, "houseNumber", "Hausnummer")
		. $input($form, "addressAddition", "Adresszusatz", "text", "col-span-full")
		. $input($form, "plz", "Postleitzahl", "text")
		. $input($form, "city", "Ort")
		. $input($form, "country", "Land");
};

$notes = function (string $form): string {
	return <<<HTML
		<label class="field col-span-full">
			<span class="field-label">Notizen</span>
			<textarea name="notes" form="{$form}" class="input-primary"></textarea>
		</label>
	HTML;
};

?>
<form id="cForm" name="cForm"></form>
<form id="pForm" name="pForm"></form>

<section class="defCont">
	<div class="section-head">
		<h2 class="section-title">Neuen Kunden anlegen</h2>
		<div class="btn-group" role="tablist" aria-label="Art des Kunden">
			<button type="button" role="tab" class="is-active" aria-selected="true" aria-controls="companyForm" id="showCompanies">Firma oder Verein</button>
			<button type="button" role="tab" aria-selected="false" aria-controls="privateForm" id="showPersons">Privatkunde</button>
		</div>
	</div>

	<!-- Firma / Verein: JS schaltet nur `hidden` um -->
	<div id="companyForm" role="tabpanel">
		<div class="grid grid-cols-1 lg:grid-cols-2 gap-x-8 gap-y-6">
			<div>
				<span class="group-label mb-2">Firma</span>
				<div class="field-grid">
					<?= $input("cForm", "customerName", "Firmen- oder Vereinsname", "text", "col-span-full") ?>
				</div>
				<span class="group-label mt-5 mb-2">Adresse</span>
				<div class="field-grid"><?= $address("cForm") ?></div>
				<span class="group-label mt-5 mb-2">Kontakt</span>
				<div class="field-grid">
					<?= $input("cForm", "companyemail", "E-Mail", "email") ?>
					<?= $input("cForm", "website", "Website", "url") ?>
					<?= $input("cForm", "telfestnetz", "Telefon Festnetz", "tel") ?>
					<?= $input("cForm", "telmobil", "Telefon Mobil", "tel") ?>
				</div>
			</div>
			<div>
				<span class="group-label mb-2">Ansprechpartner</span>
				<div class="field-grid">
					<?= $salutation("cForm") ?>
					<div></div>
					<?= $input("cForm", "contactPrename", "Vorname") ?>
					<?= $input("cForm", "contactSurname", "Nachname") ?>
					<?= $input("cForm", "emailaddress", "E-Mail", "email", "col-span-full") ?>
					<?= $input("cForm", "phoneExtension", "Telefon Durchwahl", "tel") ?>
					<?= $input("cForm", "mobileNumber", "Mobilnummer", "tel") ?>
				</div>
				<span class="group-label mt-5 mb-2">Sonstiges</span>
				<div class="field-grid"><?= $notes("cForm") ?></div>
			</div>
		</div>
	</div>

	<!-- Privatkunde -->
	<div id="privateForm" role="tabpanel" class="hidden">
		<div class="grid grid-cols-1 lg:grid-cols-2 gap-x-8 gap-y-6">
			<div>
				<span class="group-label mb-2">Person</span>
				<div class="field-grid">
					<?= $salutation("pForm") ?>
					<div></div>
					<?= $input("pForm", "prename", "Vorname") ?>
					<?= $input("pForm", "surname", "Nachname") ?>
				</div>
				<span class="group-label mt-5 mb-2">Kontakt</span>
				<div class="field-grid">
					<?= $input("pForm", "companyemail", "E-Mail", "email", "col-span-full") ?>
					<?= $input("pForm", "telfestnetz", "Telefon Festnetz", "tel") ?>
					<?= $input("pForm", "telmobil", "Telefon Mobil", "tel") ?>
				</div>
			</div>
			<div>
				<span class="group-label mb-2">Adresse</span>
				<div class="field-grid"><?= $address("pForm") ?></div>
				<span class="group-label mt-5 mb-2">Sonstiges</span>
				<div class="field-grid"><?= $notes("pForm") ?></div>
			</div>
		</div>
	</div>

	<button data-binding="true" data-fun="sendCustomerData" class="btn-primary mt-6"><?= Icon::get("user-plus") ?>Kunden anlegen</button>
</section>
