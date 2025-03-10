<?php

use MaxBrennemann\PhpUtilities\Tools;

use Classes\Link;

use Classes\Project\Kunde;

$customerId = (int) Tools::get("id");

if ($customerId == 0): ?>
	<p>Kundennummer nicht gefunden oder ungültig.</p>
	<p><a href="<?=Link::getPageLink("neuer-kunde")?>" class="link-primary">Hier</a> kannst Du einen neuen Kunden anlegen.</p>
<?php

else:
	$customer = new Kunde($customerId);
	$orderCount = $customer->getOrderIds();
	$orderCount = count($orderCount);
?>
<div class="mt-4 bg-gray-100 p-4 rounded-lg">
	<h2 class="font-bold">Kundenübersicht für <?=$customer->getFirmenname()?> (<?=$customer->getKundennummer()?>)</h2>
	<p>Anzahl der Aufträge: <?=$orderCount?></p>
	<div class="mt-2">
		<button class="btn-primary-new" data-fun="createNewOrder" data-binding="true">Neuen Auftrag erstellen</button>
		<button class="btn-primary-new" disabled>Kunde zusammenführen</button>
		<button class="btn-delete" disabled>Kunde löschen</button>
	</div>
</div>
<div class="mt-4 grid grid-cols-2 gap-4">
	<div id="showKundendaten" class="bg-gray-100 p-4 rounded-lg">
		<h3 class="font-bold">Kundendaten</h3>
		<div>
			<div class="w-full flex flex-col">
				<label for="kdnr">Kundennummer:</label>
				<input disabled class="input-primary-new" id="kdnr" value="<?=$customer->getKundennummer()?>">
			</div>
		</div>
		<div class="w-full flex">
			<div class="flex flex-1 flex-col">
				<label for="vorname">Vorname:</label>
				<input class="input-primary-new" id="vorname" value="<?=$customer->getVorname()?>" autocomplete="none">
			</div>
			<div class="flex flex-col">
				<label for="nachname">Nachname:</label>
				<input class="input-primary-new" id="nachname" value="<?=$customer->getNachname()?>" autocomplete="none">
			</div>
		</div>
		<div class="w-full flex">
			<div class="flex flex-col">
				<label for="firmenname">Firmenname:</label>
				<input class="input-primary-new w-full" id="firmenname" value="<?=$customer->getFirmenname()?>" autocomplete="none">
			</div>
		</div>
		<div class="w-full flex">
			<div class="flex flex-1 flex-col">
				<label for="strasse">Straße:</label>
				<input class="input-primary-new" id="strasse" value="<?=$customer->getStrasse()?>" autocomplete="none">
			</div>
			<div class="flex flex-col">
				<label for="hausnr">Hausnummer:</label>
				<span id="addrCount" style="display: none">1/1</span>
				<input class="input-primary-new" id="hausnr" value="<?=$customer->getHausnummer()?>" autocomplete="none">
			</div>
		</div>
		<div class="w-full flex">
			<div class="flex flex-1 flex-col">
				<label for="plz">Postleitzahl:</label>
				<input class="input-primary-new" id="plz" value="<?=$customer->getPostleitzahl()?>" autocomplete="none">
			</div>
			<div class="flex flex-col">
				<label for="ort">Ort:</label>
				<input class="input-primary-new" id="ort" value="<?=$customer->getOrt()?>" autocomplete="none">
			</div>
		</div>
		<div class="w-full flex">
			<div class="flex flex-col">
				<label for="email">Email:</label>
				<input class="input-primary-new w-full" id="email" value="<?=$customer->getEmail()?>" autocomplete="none">
			</div>
		</div>
		<div class="w-full flex">
			<div class="flex flex-col">
				<label for="website">Website:</label>
				<input class="input-primary-new w-full" id="website" value="<?=$customer->getWebsite()?>" autocomplete="none">
			</div>
		</div>
		<div class="w-full flex">
			<div class="flex flex-1 flex-col">
				<label for="festnetz">Telefon Festnetz:</label>
				<input class="input-primary-new" id="festnetz" value="<?=$customer->getTelefonFestnetz()?>" autocomplete="none">
			</div>
			<div class="flex flex-col">
				<label for="mobil">Telefon Mobil:</label>
				<input class="input-primary-new" id="mobil" value="<?=$customer->getTelefonMobil()?>" autocomplete="none">
			</div>
		</div>
		<div class="w-full flex mt-2">
			<div class="flex flex-col">
				<button class="btn-primary-new" id="sendKundendaten" disabled>Absenden</button>
			</div>
		</div>
	</div>
	<div id="addNewAddress" class="bg-gray-100 p-4 rounded-lg">
		<h4 class="font-bold">Aktuelle Adressen</h4>
		<div id="addressTable"></div>
		<h4 class="font-bold">Neue Adresse hinzufügen</h4>
		<div class="row">
			<div class="width6">
				<div class="inputCont">
					<label for="newStrasse">Straße:</label>
					<input class="input-primary-new" id="newStrasse" value="" autocomplete="none">
				</div>
			</div>
			<div class="width6">
				<div class="inputCont">
					<label for="newHausnr">Hausnummer:</label>
					<input class="input-primary-new" id="newHausnr" value="" autocomplete="none">
				</div>
			</div>
		</div>
		<div class="row">
			<div class="width6">
				<div class="inputCont">
					<label for="newPlz">Postleitzahl:</label>
					<input class="input-primary-new" id="newPlz" value="" autocomplete="none">
				</div>
			</div>
			<div class="width6">
				<div class="inputCont">
					<label for="newOrt">Ort:</label>
					<input class="input-primary-new" id="newOrt" value="" autocomplete="none">
				</div>
			</div>
		</div>
		<div class="row">
			<div class="width6">
				<div class="inputCont">
					<label for="newZusatz">Zusatz:</label>
					<input class="input-primary-new" id="newZusatz" value="" autocomplete="none">
				</div>
			</div>
			<div class="width6">
				<div class="inputCont">
					<label for="newCountry">Land:</label>
					<input class="input-primary-new" id="newCountry" value="" autocomplete="none">
				</div>
			</div>
		</div>
		<div class="row">
			<div class="width6">
				<div class="buttonCont">
					<button class="btn-primary" id="sendAdress">Absenden</button>
				</div>
			</div>
		</div>
	</div>

	<div id="notizen" class="bg-gray-100 p-4 rounded-lg">
		<h3 class="font-bold">Notizen</h3>
		<textarea class="mt-1 p-1 rounded-lg w-64 block" id="notesTextarea"><?=$customer->getNotizen()?></textarea>
		<button class="btn-primary-new mt-2" id="btnSendNotes" disabled>Abschicken</button>
	</div>
	<div id="fahrzeuge" class="bg-gray-100 p-4 rounded-lg">
		<h3 class="font-bold">Fahrzeuge</h3>
		<?=$customer->getVehicles()?>
		<div id="vehiclesTable"></div>
	</div>

	<div id="ansprechpartner" class="bg-gray-100 col-span-2 p-4 rounded-lg">
		<h3 class="font-bold">Ansprechpartner</h3>
		<div id="contactPersonTable" class="w-full"></div>
	</div>
	<div id="farben" class="bg-gray-100 col-span-2 p-4 rounded-lg">
		<h3 class="font-bold">Farben</h3>
		<div id="colorTable"></div>
	</div>
	<div id="auftraege" class="bg-gray-100 col-span-2 p-4 rounded-lg">
		<h3 class="font-bold">Aufträge</h3>
		<button class="btn-primary-new mt-2" data-fun="createNewOrder" data-binding="true">Neuen Auftrag erstellen</button>
		<?=$customer->getOrderCards()?>
	</div>
</div>
<?php endif; ?>