<?php
	require_once('classes/project/FormGenerator.php');
	require_once('classes/Link.php');

	$diagram = isset($_GET['type']) ? $_GET['type'] : "default";
	$sqlQueries = [
		0 => 'SELECT DISTINCT COUNT(auftrag.Kundennummer) AS Anzahl, kunde.Vorname, kunde.Nachname, kunde.Firmenname FROM auftrag LEFT JOIN kunde ON kunde.Kundennummer = auftrag.Kundennummer GROUP BY auftrag.Kundennummer',
		1 => "SELECT CONCAT(mitarbeiter.Vorname, ' ', mitarbeiter.Nachname) AS `Mitarbeiter`, COUNT(*) AS 'Angenommene Aufträge' FROM auftrag LEFT JOIN mitarbeiter ON auftrag.AngenommenDurch = mitarbeiter.id GROUP BY `Mitarbeiter`"
	];
	$table = new FormGenerator("", "", "");

	switch ($diagram) {
		case "mitarbeiter":
			$data = DBAccess::selectQuery($sqlQueries[1]);
			$column_names = array(0 => array("COLUMN_NAME" => "Mitarbeiter"), 1 => array("COLUMN_NAME" => "Angenommene Aufträge"));
			$table = $table->createTableByData($data, $column_names);

			echo "<h4>Anzahl der Angenommenen Aufträge pro Mitarbeiter:</h4>";
			echo $table;
		break;
		default:
			$data = DBAccess::selectQuery($sqlQueries[0]);
			$column_names = array(0 => array("COLUMN_NAME" => "Anzahl"), 1 => array("COLUMN_NAME" => "Vorname"),
				2 => array("COLUMN_NAME" => "Nachname"), 3 => array("COLUMN_NAME" => "Firmenname"));
			$table = $table->createTableByData($data, $column_names);

			echo "<h4>Anzahl der Bestellungen pro Kunde:</h4>";
			echo $table;
	}
?>
<br><br>
<a href="<?=Link::getPageLink('diagramme')?>">Anzahl der Bestellungen pro Kunde</a><br>
<a href="<?=Link::getPageLink('diagramme')?>?type=mitarbeiter">Anzahl der Angenommenen Aufträge pro Mitarbeiter</a><br>