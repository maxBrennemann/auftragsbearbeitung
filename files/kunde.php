<?php

use MaxBrennemann\PhpUtilities\Tools;

use Classes\Link;

use Classes\Project\Kunde;

$orderCount = 0;
$customerId = (int) Tools::get("id");

if ($customerId == 0): ?>
	<p>Kundennummer nicht gefunden oder ungültig.</p>
	<p><a href="<?=Link::getPageLink("neuer-kunde")?>" class="link-primary">Hier</a> kannst Du einen neuen Kunden anlegen.</p>
<?php

else:
$customer = new Kunde($customerId);

?>
<div class="mt-4 bg-gray-100 p-4 rounded-lg">
	<h2 class="font-bold">Allgemein</h2>
	<p>Anzahl der Aufträge: <?=$orderCount?></p>
	<button class="btn-primary">Neuen Auftrag erstellen</button>
	<button class="btn-primary">Neue Notiz erstellen</button>
	<button class="btn-primary">Neuen Ansprechpartner erstellen</button>
	<button class="btn-primary">Neue Farbe erstellen</button>
	<button class="btn-primary">Neues Fahrzeug erstellen</button>
	<button class="btn-primary">Neue Adresse erstellen</button>
	<button class="btn-primary">Kunde zusammenführen</button>
	<button class="btn-attention">Kunde löschen</button>
</div>
<div class="gridCont mt-4">
	<div id="showKundendaten">
		<h3 class="font-bold ml-3">Kundendaten</h3>
		<div class="row">
			<div class="width12">
				<div class="inputCont">
					<label for="kdnr">Kundennummer:</label>
					<input disabled class="data-input" id="kdnr" value="<?=$customer->getKundennummer()?>">
				</div>
			</div>
		</div>
		<div class="row">
			<div class="width6">
				<div class="inputCont">
					<label for="vorname">Vorname:</label>
					<input class="data-input" id="vorname" value="<?=$customer->getVorname()?>" autocomplete="none">
				</div>
			</div>
			<div class="width6">
				<div>
					<label for="nachname">Nachname:</label>
					<input class="data-input" id="nachname" value="<?=$customer->getNachname()?>" autocomplete="none">
				</div>
			</div>
		</div>
		<div class="row">
			<div class="width12">
				<div class="inputCont">
					<label for="firmenname">Firmenname:</label>
					<input class="data-input" id="firmenname" value="<?=$customer->getFirmenname()?>" autocomplete="none">
				</div>
			</div>
		</div>
		<div class="row">
			<div class="width6">
				<div class="inputCont">
					<label for="strasse">Straße:</label>
					<input class="data-input" id="strasse" value="<?=$customer->getStrasse()?>" autocomplete="none">
				</div>
			</div>
			<div class="width6">
				<div>
					<label for="hausnr">Hausnummer:</label>
					<span id="addrCount" style="display: none">1/1</span>
					<input class="data-input" id="hausnr" value="<?=$customer->getHausnummer()?>" autocomplete="none">
				</div>
			</div>
		</div>
		<div id="pseudo" style="display: none" class="background"></div>
		<div class="row">
			<div class="width6">
				<div class="inputCont">
					<label for="plz">Postleitzahl:</label>
					<input class="data-input" id="plz" value="<?=$customer->getPostleitzahl()?>" autocomplete="none">
				</div>
			</div>
			<div class="width6">
				<div class="inputCont">
					<label for="ort">Ort:</label>
					<input class="data-input" id="ort" value="<?=$customer->getOrt()?>" autocomplete="none">
				</div>
			</div>
		</div>
		<div class="row">
			<div class="width12">
				<div class="inputCont">
					<label for="email">Email:</label>
					<input class="data-input" id="email" value="<?=$customer->getEmail()?>" autocomplete="none">
				</div>
			</div>
		</div>
		<div class="row" style="display: none" id="websiteCont">
			<div class="width12">
				<div class="inputCont">
					<label for="website">Website:</label>
					<input class="data-input" id="website" value="<?=$customer->getWebsite()?>" autocomplete="none">
				</div>
			</div>
		</div>
		<div class="row">
			<div class="width6">
				<div class="inputCont">
					<label for="festnetz">Telefon Festnetz:</label>
					<input class="data-input" id="festnetz" value="<?=$customer->getTelefonFestnetz()?>" autocomplete="none">
				</div>
			</div>
			<div class="width6">
				<div class="inputCont">
					<label for="mobil">Telefon Mobil:</label>
					<input class="data-input" id="mobil" value="<?=$customer->getTelefonMobil()?>" autocomplete="none">
				</div>
			</div>
		</div>
		<div class="row">
			<div class="width6">
				<div class="buttonCont">
					<button class="btn-primary" id="sendKundendaten" disabled onclick="kundendatenAbsenden()">Absenden</button>
					<button class="btn-primary" id="sendKundendaten" onclick="showMore(event)" data-show="more">Mehr</button>
				</div>
			</div>
		</div>
	</div>
	<div id="addNewAddress">
		<h4 class="font-bold">Neue Adresse hinzufügen</h4>
		<div class="row">
			<div class="width6">
				<div class="inputCont">
					<label for="newStrasse">Straße:</label>
					<input class="data-input" id="newStrasse" value="" autocomplete="none">
				</div>
			</div>
			<div class="width6">
				<div class="inputCont">
					<label for="newHausnr">Hausnummer:</label>
					<input class="data-input" id="newHausnr" value="" autocomplete="none">
				</div>
			</div>
		</div>
		<div class="row">
			<div class="width6">
				<div class="inputCont">
					<label for="newPlz">Postleitzahl:</label>
					<input class="data-input" id="newPlz" value="" autocomplete="none">
				</div>
			</div>
			<div class="width6">
				<div class="inputCont">
					<label for="newOrt">Ort:</label>
					<input class="data-input" id="newOrt" value="" autocomplete="none">
				</div>
			</div>
		</div>
		<div class="row">
			<div class="width6">
				<div class="inputCont">
					<label for="newZusatz">Zusatz:</label>
					<input class="data-input" id="newZusatz" value="" autocomplete="none">
				</div>
			</div>
			<div class="width6">
				<div class="inputCont">
					<label for="newCountry">Land:</label>
					<input class="data-input" id="newCountry" value="" autocomplete="none">
				</div>
			</div>
		</div>
		<div class="row">
			<div class="width6">
				<div class="buttonCont">
					<button class="btn-primary" onclick="sendAddressForm()">Absenden</button>
				</div>
			</div>
		</div>
	</div>

	<div id="notizen">
		<h3 class="font-bold">Notizen</h3>
		<textarea class="m-1 p-1 rounded-lg w-64 block" id="notesTextarea"><?=$customer->getNotizen()?></textarea>
		<button class="btn-primary" id="btnSendNotes" disabled>Abschicken</button>
	</div>
	<div id="fahrzeuge">
		<h3 class="font-bold">Fahrzeuge</h3>
		<?=$customer->getVehicles()?>
	</div>

	<div id="ansprechpartner">
		<h3 class="font-bold">Ansprechpartner</h3>
		<div id="contactPersonTable"></div>
	</div>
	<div id="farben">
		<h3 class="font-bold">Farben</h3>
		<div id="showFarben"><?=$customer->getColors()?></div>
	</div>
	<div id="auftraege">
		<h3 class="font-bold">Aufträge</h3>
		<a class="text-blue-500 font-semibold" href="<?=Link::getPageLink("neuer-auftrag")?>?kdnr=<?=$customerId?>">Neuen Auftrag erstellen</a>
		<?=$customer->getOrderCards()?>
	</div>
</div>
<!-- TODO: add addresses -->
<?php endif; ?>