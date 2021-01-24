<?php
	require_once('classes/project/FormGenerator.php');
	require_once('classes/project/Statistics.php');
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

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.1.6/Chart.bundle.min.js"></script>
<canvas id="showGraph"></canvas>
<script>
	var ctx = document.getElementById("showGraph").getContext('2d');
	var myChart = new Chart(ctx, {
		type: 'bar',
		data: {
			labels: <?=$labels?>,
			datasets: [{
				label: 'Umsatz pro Monat',
				data: <?=$data?>,
				backgroundColor: [
					'rgba(255, 99, 132, 0.2)',
					'rgba(54, 162, 235, 0.2)',
					'rgba(255, 206, 86, 0.2)',
					'rgba(75, 192, 192, 0.2)',
					'rgba(153, 102, 255, 0.2)',
					'rgba(255, 159, 64, 0.2)'
				],
				borderColor: [
					'rgba(255, 99, 132, 1)',
					'rgba(54, 162, 235, 1)',
					'rgba(255, 206, 86, 1)',
					'rgba(75, 192, 192, 1)',
					'rgba(153, 102, 255, 1)',
					'rgba(255, 159, 64, 1)'
				],
				borderWidth: 1
			}]
		},
		options: {
			scales: {
				yAxes: [{
					ticks: {
						beginAtZero: true
					}
				}]
			}
		}
	});
</script>