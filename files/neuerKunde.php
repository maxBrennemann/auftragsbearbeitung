<?php 
require_once('classes/project/FormGenerator.php');

$getTableData = array();
if (isset($_POST['getTable'])) {
	$type = $_POST['getTable'];
	$column_names = DBAccess::selectColumnNames($type);

	for ($i = 0; $i < sizeof($column_names); $i++) {
		$showColumnName = $column_names[$i]["COLUMN_NAME"];
		$data = $_POST[$showColumnName];
		array_push($getTableData, $data);
	}

	FormGenerator::insertData($type, $getTableData);
	$table = FormGenerator::createTable("kunde", true, true, "neuerKunde");
	echo $table;
} else {
	$table = FormGenerator::createTable("kunde", true, true, "neuerKunde");
	echo "<div id='tableContainer'>" . $table . "</div>";
}
?>

<? if (!isset($_POST['getTable'])): ?>
	<h3>Ansprechpartner</h3>
	<div id="ansprechpartnerTable" style="display: none">
		<table>
			<tr>
				<th>Vorname</th>
				<th>Nachname</th>
				<th>Email</th>
				<th>Durchwahl</th>
			</tr>
			<tr>
				<td class="ansprTableCont" contenteditable="true" data-col="vorname"></td>
				<td class="ansprTableCont" contenteditable="true" data-col="nachname"></td>
				<td class="ansprTableCont" contenteditable="true" data-col="email"></td>
				<td class="ansprTableCont" contenteditable="true" data-col="durchwahl"></td>
			</tr>
		</table>
	</div>
	<button onclick="addDataToDB()">Hinzufügen</button>
<? endif; ?>
<div>
	<form>
		<p>
			<label>Firmenname
				<input class="dataInput" type="text" name="firmenname">
			</label>
		</p>
		<p>
			<label>Anrede
				<select id="selectAnrede">
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
		<p>
			<label>Straße
				<input class="dataInput" type="text" name="strasse">
			</label>
		</p>
		<p>
			<label>Hausnummer
				<input class="dataInput" type="text" name="hausnummer">
			</label>
		</p>
		<p>
			<label>Postleitzahl
				<input class="dataInput" type="number" name="plz">
			</label>
		</p>
		<p>
			<label>Ort
				<input class="dataInput" type="text" name="ort">
			</label>
		</p>
		<p>
			<label>Email
				<input class="dataInput" type="email" name="emailadress">
			</label>
		</p>
		<p>
			<label>Telefon Festnetz
				<input class="dataInput" type="tel" name="telfestnetz" pattern="[0-9]{5}\/[0-9]{+}">
			</label>
		</p>
		<p>
			<label>Telefon Mobil
				<input class="dataInput" type="text" name="telmobil">
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
</style>