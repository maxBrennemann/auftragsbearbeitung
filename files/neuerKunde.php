<form id="cForm" name="cForm"></form>
<form id="pForm" name="pForm"></form>
<div class="defCont">
	<h2 class="font-bold">Neuen Kunden anlegen</h2>
	<div class="text-sm font-medium text-center text-gray-500 border-b border-gray-200 dark:text-gray-400 dark:border-gray-700">
		<ul class="flex flex-wrap -mb-px">
			<li class="mr-2">
				<span class="inline-block p-2 text-blue-600 border-b-2 border-blue-600 rounded-t-lg active dark:text-blue-500 dark:border-blue-500" aria-current="page" id="showCompanies">Firmen und Vereine</span>
			</li>
			<li class="mr-2">
				<span class="inline-block p-2 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300" id="showPersons">Privatkunden</span>
			</li>
		</ul>
	</div>
	<div class="mt-2 grid grid-cols-2" id="companyForm">
		<div>
			<label class="block">
				Firmen- oder Vereinsname
				<input type="text" form="cForm" class="block rounded-sm m-1 p-1 w-80" name="customerName" autocomplete="some-unrecognised-value">
			</label>
			<label class="block">
				Straße
				<input type="text" form="cForm" name="street" autocomplete="some-unrecognised-value" class="block rounded-sm m-1 p-1 w-80">
			</label>
			<label class="block">
				Hausnummer
				<input type="text" form="cForm" name="houseNumber" autocomplete="some-unrecognised-value" class="block rounded-sm m-1 p-1 w-80">
			</label>
			<label class="block">
				Adresszusatz
				<input type="text" form="cForm" name="addressAddition" autocomplete="some-unrecognised-value" class="block rounded-sm m-1 p-1 w-80">
			</label>
			<label class="block">
				Postleitzahl
				<input type="number" form="cForm" name="plz" autocomplete="some-unrecognised-value" class="block rounded-sm m-1 p-1 w-80">
			</label>
			<label class="block">
				Ort
				<input type="text" form="cForm" name="city" autocomplete="some-unrecognised-value" class="block rounded-sm m-1 p-1 w-80">
			</label>
			<label class="block">
				Land
				<input type="text" form="cForm" name="country" autocomplete="some-unrecognised-value" class="block rounded-sm m-1 p-1 w-80">
			</label>
			<label class="block">
				Email
				<input type="email" form="cForm" name="companyemail" autocomplete="some-unrecognised-value" class="block rounded-sm m-1 p-1 w-80">
			</label>
			<label class="block">
				Telefon Festnetz
				<input type="tel" form="cForm" name="telfestnetz" autocomplete="some-unrecognised-value" class="block rounded-sm m-1 p-1 w-80">
			</label>
			<label class="block">
				Telefon Mobil
				<input type="tel" form="cForm" name="telmobil" autocomplete="some-unrecognised-value" class="block rounded-sm m-1 p-1 w-80">
			</label>
			<label class="block">
				Notizen
				<textarea name="notes" form="cForm" class="block rounded-sm m-1 p-1 w-80"></textarea>
			</label>
		</div>
		<div>
			<p class="font-semibold mb-1">Kontaktdaten Ansprechpartner</p>
			<label class="block">
				Anrede
				<select form="cForm" class="block rounded-sm m-1 p-1 w-80" name="anrede">
					<option value="0">Herr</option>
					<option value="1">Frau</option>
					<option value="5">Divers</option>
					<option value="2">Firma</option>
					<option value="3">Verein</option>
					<option value="4">Sonstiges</option>
				</select>
			</label>
			<label class="block">
				Vorname
				<input form="cForm" type="text" name="contactPrename" autocomplete="some-unrecognised-value" class="block rounded-sm m-1 p-1 w-80">
			</label>
			<label class="block">
				Nachname
				<input type="text" form="cForm" name="contactSurname" autocomplete="some-unrecognised-value" class="block rounded-sm m-1 p-1 w-80">
			</label>
			<label class="block">
				Email
				<input type="email" form="cForm" name="emailaddress" autocomplete="some-unrecognised-value" class="block rounded-sm m-1 p-1 w-80">
			</label>
			<label class="block">
				Telefon Durchwahl
				<input type="tel" form="cForm" name="phoneExtension" autocomplete="some-unrecognised-value" class="block rounded-sm m-1 p-1 w-80">
			</label>
			<label class="block">
				Mobilnummer
				<input type="tel" form="cForm" name="mobileNumber" autocomplete="some-unrecognised-value" class="block rounded-sm m-1 p-1 w-80">
			</label>
		</div>
	</div>
	<div class="mt-2 hidden grid-cols-2" id="privateForm">
		<div>
			<label class="block">
				Anrede
				<select form="pForm" class="block rounded-sm m-1 p-1 w-80" name="anrede">
					<option value="0">Herr</option>
					<option value="1">Frau</option>
					<option value="5">Divers</option>
					<option value="2">Firma</option>
					<option value="3">Verein</option>
					<option value="4">Sonstiges</option>
				</select>
			</label>
			<label class="block">
				Vorname
				<input type="text" form="pForm" name="prename" autocomplete="some-unrecognised-value" class="block rounded-sm m-1 p-1 w-80">
			</label>
			<label class="block">
				Nachname
				<input type="text" form="pForm" name="surname" autocomplete="some-unrecognised-value" class="block rounded-sm m-1 p-1 w-80">
			</label>
			<label class="block">
				Email
				<input type="email" form="pForm" name="companyemail" autocomplete="some-unrecognised-value" class="block rounded-sm m-1 p-1 w-80">
			</label>
			<label class="block">
				Telefon Festnetz
				<input type="tel" form="pForm" name="telfestnetz" autocomplete="some-unrecognised-value" class="block rounded-sm m-1 p-1 w-80">
			</label>
			<label class="block">
				Telefon Mobil
				<input type="tel" form="pForm" name="telmobil" autocomplete="some-unrecognised-value" class="block rounded-sm m-1 p-1 w-80">
			</label>
			<label class="block">
				Notizen
				<textarea name="notes" form="pForm" class="block rounded-sm m-1 p-1 w-80"></textarea>
			</label>
		</div>
		<div>
			<label class="block">
				Straße
				<input type="text" form="pForm" name="street" autocomplete="some-unrecognised-value" class="block rounded-sm m-1 p-1 w-80">
			</label>
			<label class="block">
				Hausnummer
				<input type="text" form="pForm" name="houseNumber" autocomplete="some-unrecognised-value" class="block rounded-sm m-1 p-1 w-80">
			</label>
			<label class="block">
				Adresszusatz
				<input type="text" form="pForm" name="addressAddition" autocomplete="some-unrecognised-value" class="block rounded-sm m-1 p-1 w-80">
			</label>
			<label class="block">
				Postleitzahl
				<input type="number" form="pForm" name="plz" autocomplete="some-unrecognised-value" class="block rounded-sm m-1 p-1 w-80">
			</label>
			<label class="block">
				Ort
				<input type="text" form="pForm" name="city" autocomplete="some-unrecognised-value" class="block rounded-sm m-1 p-1 w-80">
			</label>
			<label class="block">
				Land
				<input type="text" form="pForm" name="country" autocomplete="some-unrecognised-value" class="block rounded-sm m-1 p-1 w-80">
			</label>
		</div>
	</div>
	<button id="addNewCustomer" class="btn-primary">Neuen Kunden erstellen</button>
</div>