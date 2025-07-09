<?php

use Classes\Link;
use Classes\Project\Kunde;
use MaxBrennemann\PhpUtilities\Tools;

$customerId = (int) Tools::get("id");

if ($customerId <= 0): ?>
	<p>Kundennummer nicht gefunden oder ungültig.</p>
	<p><a href="<?= Link::getPageLink("neuer-kunde") ?>" class="link-primary">Hier</a> kannst Du einen neuen Kunden anlegen.</p>
<?php else:
    $customer = new Kunde($customerId);
    $orderCount = $customer->getOrderIds();
    $orderCount = count($orderCount);
    ?>
	<div class="mt-4 bg-gray-100 p-4 rounded-lg">
		<h2 class="font-bold">Kundenübersicht für <?= $customer->getFirmenname() ?> (<?= $customer->getKundennummer() ?>)</h2>
		<p>Anzahl der Aufträge: <?= $orderCount ?></p>
		<div class="mt-2">
			<button class="btn-primary" data-fun="createNewOrder" data-binding="true">Neuen Auftrag erstellen</button>
			<button class="btn-primary" data-toggle="true" data-target="#moreOptions">Mehr Optionen</button>
			<span class="hidden" id="moreOptions">
				<button class="btn-primary" data-fun="mergeCustomer" data-binding="true">Kunde zusammenführen</button>
				<button class="btn-delete" data-fun="deleteCustomer" data-binding="true">Kunde löschen</button>
			</span>
		</div>
	</div>
	<div class="mt-4 grid grid-cols-2 gap-4">
		<div id="showKundendaten" class="bg-gray-100 p-4 rounded-lg col-span-2 2xl:col-span-1">
			<h3 class="font-bold">Kundendaten</h3>
			<div class="mt-2">
				<div class="w-full flex flex-col">
					<label for="idCustomer">Kundennummer:</label>
					<input disabled class="input-primary" id="idCustomer" value="<?= $customer->getKundennummer() ?>">
				</div>
			</div>
			<div class="w-full flex gap-8 mt-2">
				<div class="flex flex-col">
					<label for="prename">Vorname:</label>
					<input class="input-primary" id="prename" value="<?= $customer->getVorname() ?>" autocomplete="none">
				</div>
				<div class="flex flex-initial flex-col">
					<label for="lastname">Nachname:</label>
					<input class="input-primary" id="lastname" value="<?= $customer->getNachname() ?>" autocomplete="none">
				</div>
			</div>
			<div class="w-full flex mt-2">
				<div class="flex flex-col">
					<label for="companyname">Firmenname:</label>
					<input class="input-primary w-80" id="companyname" value="<?= $customer->getFirmenname() ?>" autocomplete="none">
				</div>
			</div>
			<div class="w-full flex mt-2">
				<div class="flex flex-col">
					<label for="email">Email:</label>
					<input class="input-primary w-80" id="email" value="<?= $customer->getEmail() ?>" autocomplete="none">
				</div>
			</div>
			<div class="w-full flex mt-2">
				<div class="flex flex-col">
					<label for="website">Website:</label>
					<input class="input-primary w-80" id="website" value="<?= $customer->getWebsite() ?>" autocomplete="none">
				</div>
			</div>
			<div class="w-full flex gap-8 mt-2">
				<div class="flex flex-col">
					<label for="phoneLandline">Telefon Festnetz:</label>
					<input class="input-primary" id="phoneLandline" value="<?= $customer->getTelefonFestnetz() ?>" autocomplete="none">
				</div>
				<div class="flex flex-initial flex-col">
					<label for="phoneMobile">Telefon Mobil:</label>
					<input class="input-primary" id="phoneMobile" value="<?= $customer->getTelefonMobil() ?>" autocomplete="none">
				</div>
			</div>
			<div class="w-full flex mt-2">
				<div class="flex flex-col">
					<label for="fax">Fax:</label>
					<input class="input-primary w-80" id="fax" value="<?= $customer->getFax() ?>" autocomplete="none">
				</div>
			</div>
			<div class="w-full flex mt-2">
				<div class="flex">
					<button class="btn-primary" data-binding="true" data-fun="saveCustomerData">Speichern</button>
					<button class="btn-cancel ml-2" data-binding="true" data-fun="resetCustomerData">Abbrechen</button>
				</div>
			</div>
		</div>

		<div id="ansprechpartner" class="bg-gray-100 col-span-2 p-4 rounded-lg">
			<h3 class="font-bold">Ansprechpartner</h3>
			<div id="contactPersonTable" class="w-full mt-2"></div>
		</div>

		<div id="addNewAddress" class="bg-gray-100 p-4 rounded-lg col-span-2 2xl:col-span-1">
			<h4 class="font-bold">Aktuelle Adressen</h4>
			<div id="addressTable" class="mt-2 2xl:overflow-x-auto"></div>
		</div>

		<div id="notizen" class="bg-gray-100 p-4 rounded-lg">
			<h3 class="font-bold">Notizen</h3>
			<textarea class="mt-2 w-full input-primary" oninput="this.style.height = '';this.style.height = this.scrollHeight + 'px'" data-write="true" data-fun="setCustomerNote"><?= $customer->getNotizen() ?></textarea>
		</div>

		<div id="fahrzeuge" class="bg-gray-100 p-4 rounded-lg">
			<h3 class="font-bold">Fahrzeuge</h3>
			<div id="vehiclesTable" class="mt-2"></div>
		</div>

		<div id="farben" class="bg-gray-100 col-span-2 p-4 rounded-lg">
			<h3 class="font-bold">Farben</h3>
			<div id="colorTable" class="mt-2"></div>
		</div>

		<div id="auftraege" class="bg-gray-100 col-span-2 p-4 rounded-lg">
			<h3 class="font-bold">Aufträge</h3>
			<button class="btn-primary mt-2" data-fun="createNewOrder" data-binding="true">Neuen Auftrag erstellen</button>
			<?= $customer->getOrderCards() ?>
		</div>
	</div>
<?php endif; ?>