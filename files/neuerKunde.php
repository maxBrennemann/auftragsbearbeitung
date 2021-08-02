<?php
	require_once('classes/DBAccess.php');

	// TODO: test

	$isSent = isset($_GET['oeffOderPriv']);

	if($isSent) {
		$oeffOderPriv = $_GET['oeffOderPriv'];
		$firmenname = isset($_GET['firmenname']) ? isset($_GET['firmenname']) : "";
		$anredeAnspr = $_GET['anredeAnspr'];
		$vornameAnspr = $_GET['vornameAnspr'];
		$nachnameAnspr = $_GET['nachnameAnspr'];
		$telmobilAnspr = $_GET['telMobilAnspr'];
		$emailAnspr = $_GET['emailAnspr'];
		$telAnspr = $_GET['telAnspr'];
		$anrede = $_GET['anrede'];
		$vorname = $_GET['vorname'];
		$nachname = $_GET['nachname'];
		$strasse = $_GET['strasse'];
		$hausnummer = $_GET['hausnummer'];
		$plz = $_GET['plz'];
		$ort = $_GET['ort'];
		$zusatz = $_GET['zusatz'];
		$country = $_GET['country'];
		$email = $_GET['emailadress'];
		$telfestnetz = $_GET['telfestnetz'];
		$telmobil = $_GET['telmobil'];

		if ($plz == null) {
			$plz = 0;
		}

		$insertString = "INSERT INTO kunde (Firmenname, Anrede, Vorname, Nachname,";
		$insertString .= " Straße, Hausnummer, Postleitzahl, Ort, Email,";
		$insertString .= " TelefonFestnetz, TelefonMobil) VALUES";
		$insertString .= "('$firmenname', '$anrede', '$vorname', '$nachname', ";
		$insertString .= "'$strasse', '$hausnummer', $plz, '$ort', '$email', ";
		$insertString .= "'$telfestnetz', '$telmobil')";

		/* insert customer data */
		$insertString = "INSERT INTO kunde (Firmenname, Anrede, Vorname, Nachname, Email, TelefonFestnetz, TelefonMobil) VALUES ('$firmenname', '$anrede', '$vorname', '$nachname', '$email', '$telfestnetz', '$telmobil')";
		$newCustomerId = DBAccess::insertQuery($insertString);

		/* insert adress data */
		$insertString = "INSERT INTO adress (id_customer, strasse, hausnr, plz, ort, zusatz, country) VALUES ($newCustomerId, '$strasse', '$hausnummer', $plz, '$ort', '$zusatz', '$country')";
		$insertID = DBAccess::insertQuery($insertString);

		/* update customer data */
		DBAccess::updateQuery("UPDATE kunde SET id_adress_primary = $insertID WHERE Kundennummer = $newCustomerId");

		/* insert ansprechpartner data */
		if ($nachnameAnspr != "") {
			$kdnr = $newCustomerId;
			$insertString = "INSERT INTO ansprechpartner (Kundennummer, Vorname, Nachname,";
			$insertString .= " Email, Durchwahl, Mobiltelefonnummer) VALUES ($kdnr, '$vornameAnspr', ";
			$insertString .= "'$nachnameAnspr', '$emailAnspr', '$telAnspr', '$telmobilAnspr')";
			//$newCustomerId = DBAccess::insertQuery($insertString);
		}
	}

	if (!$isSent) :
?>
<div class="addcustomer">
	<form>
		<input type="radio" name="oeffOderPriv" value="firma" checked onchange="switchOeffPriv(this)">Firma oder Vereinsname
		<input type="radio" name="oeffOderPriv" value="privat" onchange="switchOeffPriv(this)">Privat
		<div class="basicInfo">
			<p>
				<label>Firmenname
					<input class="dataInput" type="text" name="firmenname" autocomplete="some-unrecognised-value">
				</label>
			</p>
			<div class="specificData">
				<h4>Ansprechpartner:</h4>
				<p>
					<label>Anrede
						<select class="dataInput" name="anredeAnspr">
							<option value="0">Herr</option>
							<option value="1">Frau</option>
							<option value="2">Firma</option>
						</select>
					</label>
				</p>
				<p>
					<label>Vorname
						<input class="dataInput" type="text" name="vornameAnspr" autocomplete="some-unrecognised-value">
					</label>
				</p>
				<p>
					<label>Nachname
						<input class="dataInput" type="text" name="nachnameAnspr" autocomplete="some-unrecognised-value">
					</label>
				</p>
				<p>
					<label>Email
						<input class="dataInput" type="email" name="emailAnspr" autocomplete="some-unrecognised-value">
					</label>
				</p>
				<p>
					<label>Durchwahl
						<input class="dataInput" type="text" name="telAnspr" autocomplete="some-unrecognised-value">
					</label>
				</p>
				<p>
					<label>Mobilnummer
						<input class="dataInput" type="text" name="telMobilAnspr" autocomplete="some-unrecognised-value">
					</label>
				</p>
			</div>
		</div>
		<div class="basicInfo" style="display: none;">
			<p>
				<label>Anrede
					<select class="dataInput" name="anrede">
						<option value="0">Herr</option>
						<option value="1">Frau</option>
						<option value="2">Firma</option>
					</select>
				</label>
			</p>
			<p>
				<label>Vorname
					<input class="dataInput" type="text" name="vorname" autocomplete="some-unrecognised-value">
				</label>
			</p>
			<p>
				<label>Nachname
					<input class="dataInput" type="text" name="nachname" autocomplete="some-unrecognised-value">
				</label>
			</p>
		</div>
		<p>
			<label>Straße
				<input class="dataInput" type="text" name="strasse" autocomplete="some-unrecognised-value">
			</label>
		</p>
		<p>
			<label>Hausnummer
				<input class="dataInput" type="text" name="hausnummer" autocomplete="some-unrecognised-value">
			</label>
		</p>
		<p>
			<label>Adresszusatz
				<input class="dataInput" type="text" name="zusatz" autocomplete="some-unrecognised-value">
			</label>
		</p>
		<p>
			<label>Postleitzahl
				<input class="dataInput" type="number" name="plz" autocomplete="some-unrecognised-value">
			</label>
		</p>
		<p>
			<label>Ort
				<input class="dataInput" type="text" name="ort" autocomplete="some-unrecognised-value">
			</label>
		</p>
		<p>
			<label>Land
				<input class="dataInput" type="text" name="country" autocomplete="some-unrecognised-value">
			</label>
		</p>
		<p>
			<label>Email
				<input class="dataInput" type="email" name="emailadress" autocomplete="some-unrecognised-value">
			</label>
		</p>
		<p>
			<label>Telefon Festnetz
				<input class="dataInput" type="tel" name="telfestnetz" pattern="[0-9]{5}\/[0-9]+" autocomplete="some-unrecognised-value">
				<br>
				<span class="hinweis">Muster: 09933/1234</span>
			</label>
		</p>
		<div class="basicInfo" style="display: none;">
			<p>
				<label>Telefon Mobil
					<input class="dataInput" type="tel" name="telmobil" pattern="[0][1-9]{3} [0-9]+" autocomplete="some-unrecognised-value">
					<br>
					<span class="hinweis">Muster: 0172 1234567</span>
				</label>
			</p>
		</div>

		<input type="submit" id="submitCustomer">
	</form>
</div>
<?php else: ?>
	<p>Kunde wurde hinzugefügt.</p>
	<a href="<?=Link::getPageLink("neuer-kunde");?>">Weiteren Kunden hinzufügen.</a>
	<br>
	<a href="<?=Link::getPageLink("kunde");?>?id=<?=$newCustomerId?>">Zum neuen Kunden.</a>
<?php endif; ?>
<style>
	form p {
		display: block;
	}

	.dataInput {
		float: right;
	}

	.specificData {
		margin: 5px;
		padding: 10px;
		border: 1px solid grey;
		border-radius: 6px;
	}

	.addcustomer {
		background: #eff0f1;
		border-radius: 6px;
		padding: 10px;
	}

	#submitCustomer {
		border-radius: 6px;
		background: #B2B2BE;
		border: none;
		padding: 15px;
	}

	.hinweis {
		font-size: 0.9em;
    	font-family: monospace;
	}
</style>