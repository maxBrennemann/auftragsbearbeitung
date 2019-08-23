<?php 
require_once('classes/project/FormGenerator.php');

$getTableData = array();
if (isset($_POST['getTable'])) {
	$type = $_POST['getTable'];
	$column_names = DBAccess::selectQuery("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = N'${type}'");

	for ($i = 0; $i < sizeof($column_names); $i++) {
		$showColumnName = $column_names[$i]["COLUMN_NAME"];
		$data = $_POST[$showColumnName];
		array_push($getTableData, $data);
	}

	FormGenerator::insertData($type, $getTableData);
	$table = FormGenerator::createTable("auftrag", true, true, "neuerAuftrag");
	echo $table;
} else {
	$table = FormGenerator::createTable("auftrag", true, true, "neuerAuftrag");
	echo "<div id='tableContainer'>" . $table . "</div>";
}

if(!isset($_POST['getTable'])) : ?>
	<input type="number" min="1" id="auftragsnummer">
	<button onclick="print('auftragsnummer', 'Auftrag');">Auftragsblatt generieren</button>
<?php endif ?>
