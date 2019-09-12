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