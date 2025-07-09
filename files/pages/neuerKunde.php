<form id="cForm" name="cForm"></form>
<form id="pForm" name="pForm"></form>
<div class="defCont">
	<h2 class="font-bold">Neuen Kunden anlegen</h2>
	<div class="text-sm font-medium text-center text-gray-500 border-b border-gray-200 dark:text-gray-400 dark:border-gray-700">
		<ul class="flex flex-wrap -mb-px">
			<li class="mr-2">
				<span class="inline-block p-2 text-blue-600 border-b-2 border-blue-600 rounded-t-lg active dark:text-blue-500 dark:border-blue-500 cursor-pointer" aria-current="page" id="showCompanies">Firmen und Vereine</span>
			</li>
			<li class="mr-2">
				<span class="inline-block p-2 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 cursor-pointer" id="showPersons">Privatkunden</span>
			</li>
		</ul>
	</div>
	<div class="mt-2 grid grid-cols-2" id="companyForm">
		<div>
			<label class="flex flex-col">
				Firmen- oder Vereinsname
				<input type="text" form="cForm" class="input-primary" name="customerName" autocomplete="some-unrecognised-value">
			</label>
			<label class="flex flex-col">
				Straße
				<input type="text" form="cForm" name="street" autocomplete="some-unrecognised-value" class="input-primary">
			</label>
			<label class="flex flex-col">
				Hausnummer
				<input type="text" form="cForm" name="houseNumber" autocomplete="some-unrecognised-value" class="input-primary">
			</label>
			<label class="flex flex-col">
				Adresszusatz
				<input type="text" form="cForm" name="addressAddition" autocomplete="some-unrecognised-value" class="input-primary">
			</label>
			<label class="flex flex-col">
				Postleitzahl
				<input type="number" form="cForm" name="plz" autocomplete="some-unrecognised-value" class="input-primary">
			</label>
			<label class="flex flex-col">
				Ort
				<input type="text" form="cForm" name="city" autocomplete="some-unrecognised-value" class="input-primary">
			</label>
			<label class="flex flex-col">
				Land
				<input type="text" form="cForm" name="country" autocomplete="some-unrecognised-value" class="input-primary">
			</label>
			<label class="flex flex-col">
				Email
				<input type="email" form="cForm" name="companyemail" autocomplete="some-unrecognised-value" class="input-primary">
			</label>
			<label class="flex flex-col">
				Telefon Festnetz
				<input type="tel" form="cForm" name="telfestnetz" autocomplete="some-unrecognised-value" class="input-primary">
			</label>
			<label class="flex flex-col">
				Telefon Mobil
				<input type="tel" form="cForm" name="telmobil" autocomplete="some-unrecognised-value" class="input-primary">
			</label>
			<label class="flex flex-col">
				Website
				<input type="url" form="cForm" name="website" autocomplete="some-unrecognised-value" class="input-primary">
			</label>
			<label class="flex flex-col">
				Notizen
				<textarea name="notes" form="cForm" class="input-primary"></textarea>
			</label>
		</div>
		<div>
			<p class="font-semibold mb-1">Kontaktdaten Ansprechpartner</p>
			<label class="flex flex-col">
				Anrede
				<select form="cForm" class="input-primary" name="anrede">
					<option value="0">Herr</option>
					<option value="1">Frau</option>
					<option value="5">Divers</option>
					<option value="2">Firma</option>
					<option value="3">Verein</option>
					<option value="4">Sonstiges</option>
				</select>
			</label>
			<label class="flex flex-col">
				Vorname
				<input form="cForm" type="text" name="contactPrename" autocomplete="some-unrecognised-value" class="input-primary">
			</label>
			<label class="flex flex-col">
				Nachname
				<input type="text" form="cForm" name="contactSurname" autocomplete="some-unrecognised-value" class="input-primary">
			</label>
			<label class="flex flex-col">
				Email
				<input type="email" form="cForm" name="emailaddress" autocomplete="some-unrecognised-value" class="input-primary">
			</label>
			<label class="flex flex-col">
				Telefon Durchwahl
				<input type="tel" form="cForm" name="phoneExtension" autocomplete="some-unrecognised-value" class="input-primary">
			</label>
			<label class="flex flex-col">
				Mobilnummer
				<input type="tel" form="cForm" name="mobileNumber" autocomplete="some-unrecognised-value" class="input-primary">
			</label>
		</div>
	</div>
	<div class="mt-2 hidden grid-cols-2" id="privateForm">
		<div>
			<label class="flex flex-col">
				Anrede
				<select form="pForm" class="input-primary" name="anrede">
					<option value="0">Herr</option>
					<option value="1">Frau</option>
					<option value="5">Divers</option>
					<option value="2">Firma</option>
					<option value="3">Verein</option>
					<option value="4">Sonstiges</option>
				</select>
			</label>
			<label class="flex flex-col">
				Vorname
				<input type="text" form="pForm" name="prename" autocomplete="some-unrecognised-value" class="input-primary">
			</label>
			<label class="flex flex-col">
				Nachname
				<input type="text" form="pForm" name="surname" autocomplete="some-unrecognised-value" class="input-primary">
			</label>
			<label class="flex flex-col">
				Email
				<input type="email" form="pForm" name="companyemail" autocomplete="some-unrecognised-value" class="input-primary">
			</label>
			<label class="flex flex-col">
				Telefon Festnetz
				<input type="tel" form="pForm" name="telfestnetz" autocomplete="some-unrecognised-value" class="input-primary">
			</label>
			<label class="flex flex-col">
				Telefon Mobil
				<input type="tel" form="pForm" name="telmobil" autocomplete="some-unrecognised-value" class="input-primary">
			</label>
			<label class="flex flex-col">
				Notizen
				<textarea name="notes" form="pForm" class="input-primary"></textarea>
			</label>
		</div>
		<div>
			<label class="flex flex-col">
				Straße
				<input type="text" form="pForm" name="street" autocomplete="some-unrecognised-value" class="input-primary">
			</label>
			<label class="flex flex-col">
				Hausnummer
				<input type="text" form="pForm" name="houseNumber" autocomplete="some-unrecognised-value" class="input-primary">
			</label>
			<label class="flex flex-col">
				Adresszusatz
				<input type="text" form="pForm" name="addressAddition" autocomplete="some-unrecognised-value" class="input-primary">
			</label>
			<label class="flex flex-col">
				Postleitzahl
				<input type="number" form="pForm" name="plz" autocomplete="some-unrecognised-value" class="input-primary">
			</label>
			<label class="flex flex-col">
				Ort
				<input type="text" form="pForm" name="city" autocomplete="some-unrecognised-value" class="input-primary">
			</label>
			<label class="flex flex-col">
				Land
				<input type="text" form="pForm" name="country" autocomplete="some-unrecognised-value" class="input-primary">
			</label>
		</div>
	</div>
	<button data-binding="true" data-fun="sendCustomerData" class="btn-primary mt-2">Neuen Kunden erstellen</button>
</div>