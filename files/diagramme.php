<?php
	require_once('classes/project/FormGenerator.php');

	$d = new DateTime('first day of this month');
	echo $d->format("Y-m-d");

	$sqlQueries = [
		0 => 'SELECT DISTINCT COUNT(auftrag.Kundennummer) AS Anzahl, kunde.Vorname, kunde.Nachname, kunde.Firmenname FROM auftrag LEFT JOIN kunde ON kunde.Kundennummer = auftrag.Kundennummer GROUP BY auftrag.Kundennummer',
		1 => ''
	];

	$table = new FormGenerator("", "", "");
	$data = DBAccess::selectQuery($sqlQueries[0]);
	$column_names = array(0 => array("COLUMN_NAME" => "Anzahl"), 1 => array("COLUMN_NAME" => "Vorname"),
		2 => array("COLUMN_NAME" => "Nachname"), 3 => array("COLUMN_NAME" => "Firmenname"));
	$table = $table->createTableByData($data, $column_names);

	echo "<h4>Anzahl der Bestellungen pro Kunde:</h4>";
	echo $table;
?>