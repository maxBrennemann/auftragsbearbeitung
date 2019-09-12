<?php 
	$kdnr = -1;
	if (isset($_GET['kdnr'])) {
		$kdnr = $_GET['kdnr'];
	}

	if (isset($_GET['showDetails'])) {
		if (isset($_GET['id'])) {
			$kdnr = $_GET['id'];
		}
	}
?>


<?php if ($kdnr != -1) : ?>
	<span>Kundennummer: <?=$kdnr?></span><br>
	<span>Auftragsbezeichnung: <input id="bezeichnung"></span><br>
	<span>Auftragsbeschreibung: <textarea id="beschreibung"></textarea></span><br>
	<span>Auftragstyp: <input id="typ"></span><br>
	<span>Termin: <input id="termin" type="date"></span><br>
	<span>Angenommen durch: <input id="angenommen"></span><br>
	<button onclick="auftragHinzufuegen()">Absenden</button>
<?php else: ?>
	<span>Kundennummer oder Suchen: <input id="kundensuche" onkeyup="performSearchEnter(event, this.value);"><button onclick="performSearchButton(event)">&#x1F50E;</button></span>
	<input type="number" min="1" id="auftragsnummer"><button onclick="print('auftragsnummer', 'Auftrag');">Auftragsblatt generieren</button>
	<span id="searchResults"></span>
<?php endif; ?>
