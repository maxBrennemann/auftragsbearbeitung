<?php
	require_once('classes/project/Statistics.php');
	require_once('classes/Link.php');

	$diagram = isset($_GET['type']) ? $_GET['type'] : "default";
	$sqlQueries = [
		0 => 'SELECT DISTINCT COUNT(auftrag.Kundennummer) AS Anzahl, kunde.Vorname, kunde.Nachname, kunde.Firmenname FROM auftrag LEFT JOIN kunde ON kunde.Kundennummer = auftrag.Kundennummer GROUP BY auftrag.Kundennummer',
		1 => "SELECT CONCAT(mitarbeiter.Vorname, ' ', mitarbeiter.Nachname) AS `Mitarbeiter`, COUNT(*) AS 'Angenommene Aufträge' FROM auftrag LEFT JOIN mitarbeiter ON auftrag.AngenommenDurch = mitarbeiter.id GROUP BY `Mitarbeiter`"
	];

	switch ($diagram) {
		case "mitarbeiter":
			$data = DBAccess::selectQuery($sqlQueries[1]);
			$column_names = array(
				0 => array("COLUMN_NAME" => "Mitarbeiter"), 
				1 => array("COLUMN_NAME" => "Angenommene Aufträge")
			);
			$table = new Table();
			$table->createByData($data, $column_names);

			echo "<h4>Anzahl der Angenommenen Aufträge pro Mitarbeiter:</h4>";
			echo "<div id=\"tableContainer\">" . $table->getTable() . "</div>";
		break;
		default:
			$data = DBAccess::selectQuery($sqlQueries[0]);
			$column_names = array(
				0 => array("COLUMN_NAME" => "Anzahl"), 
				1 => array("COLUMN_NAME" => "Vorname"),
				2 => array("COLUMN_NAME" => "Nachname"), 
				3 => array("COLUMN_NAME" => "Firmenname")
			);
			$table = new Table();
			$table->createByData($data, $column_names);

			echo "<h4>Anzahl der Bestellungen pro Kunde:</h4>";
			echo "<div id=\"tableContainer\">" . $table->getTable() . "</div>";
	}

	/* prepares the data for the diagram */
	$sqlData = Statistics::getVolumeByMonth();
	$labels = "[";
	$data = "[";
	foreach ($sqlData as $d) {
		$labels .= "'" . $d['Monat'] . "', ";
		$data .= $d['Volume'] . ", ";
	}
	$labels = substr($labels, 0, -2);
	$labels .= "]";
	$data = substr($data, 0, -2);
	$data .= "]";
?>
<br><br>
<a href="<?=Link::getPageLink('diagramme')?>">Anzahl der Bestellungen pro Kunde</a><br>
<a href="<?=Link::getPageLink('diagramme')?>?type=mitarbeiter">Anzahl der Angenommenen Aufträge pro Mitarbeiter</a><br>
<script>
	var labels = <?=$labels?>;
	var data = <?=$data?>;
</script>
<canvas id="showGraph"></canvas>
<style>
	 header {
        z-index: 2;
    }

	#tableContainer {
		position: relative;
		max-height: 500px;
		overflow: auto;
	}

	table {
        display: table;
        position: relative;
        text-align: left;
        z-index: 1;
    }

    tbody {
        display: table-header-group;
    }

	table th {
        position: -webkit-sticky;
		position: sticky;
        top: 0;
	}
</style>