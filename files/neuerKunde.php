<div>
	<form>
		<input type="radio" name="oeffOderPriv" value="firma" checked onchange="switchOeffPriv(this)">Firma oder Vereinsname
		<input type="radio" name="oeffOderPriv" value="privat" onchange="switchOeffPriv(this)">Privat
		<div class="basicInfo">
			<p>
				<label>Firmenname
					<input class="dataInput" type="text" name="firmenname">
				</label>
			</p>
			<div class="specificData">
				<h4>Ansprechpartner:</h4>
				<p>
					<label>Anrede
						<select class="dataInput" id="selectAnrede" name="anredeAnspr">
							<option value="0">Herr</option>
							<option value="1">Frau</option>
							<option value="2">Firma</option>
						</select>
					</label>
				</p>
				<p>
					<label>Vorname
						<input class="dataInput" type="text" name="vornameAnspr">
					</label>
				</p>
				<p>
					<label>Nachname
						<input class="dataInput" type="text" name="nachnameAnspr">
					</label>
				</p>
				<p>
					<label>Email
						<input class="dataInput" type="email" name="emailAnspr">
					</label>
				</p>
				<p>
					<label>Durchwahl
						<input class="dataInput" type="text" name="telAnspr">
					</label>
				</p>
			</div>
		</div>
		<div class="basicInfo" style="display: none;">
			<p>
				<label>Anrede
					<select class="dataInput" id="selectAnrede" name="anrede">
						<option value="0">Herr</option>
						<option value="1">Frau</option>
						<option value="2">Firma</option>
					</select>
				</label>
			</p>
			<p>
				<label>Vorname
					<input class="dataInput" type="text" name="vorname">
				</label>
			</p>
			<p>
				<label>Nachname
					<input class="dataInput" type="text" name="nachname">
				</label>
			</p>
		</div>
		<p>
			<label>Straße
				<input class="dataInput" type="text" name="strasse" required>
			</label>
		</p>
		<p>
			<label>Hausnummer
				<input class="dataInput" type="text" name="hausnummer" required>
			</label>
		</p>
		<p>
			<label>Postleitzahl
				<input class="dataInput" type="number" name="plz" required>
			</label>
		</p>
		<p>
			<label>Ort
				<input class="dataInput" type="text" name="ort" required>
			</label>
		</p>
		<p>
			<label>Email
				<input class="dataInput" type="email" name="emailadress">
			</label>
		</p>
		<p>
			<label>Telefon Festnetz
				<input class="dataInput" type="tel" name="telfestnetz" pattern="[0-9]{5}\/[0-9]+">
			</label>
		</p>
		<p>
			<label>Telefon Mobil
				<input class="dataInput" type="tel" name="telmobil" pattern="[0][1-9]{3} [0-9]+">
			</label>
		</p>

		<input type="submit">
	</form>
</div>
<style>
	form p {
		display: block;
	}

	.dataInput {
		float: right;
	}

	.specificData {
		-webkit-appearance: textfield;
		border-style: inset;
		margin: 5px;
		padding: 10px;
	}
</style>