<?php
	require_once('classes/Link.php');
	require_once('classes/project/FormGenerator.php');
	require_once('classes/project/Rechnung.php');
	
	$rechnung =  Link::getPageLink("rechnung");
	$rechnungsnummer = 0;
	
	$target = isset($_GET["target"]) ? $_GET["target"] : -1;
	$id = isset($_GET["id"]) ? $_GET["id"] : -1;

	if ($target != -1) {
		switch ($target) {
			case "view":
				$rechnungsnummer = $id;
				$rechnung = new Rechnung($rechnungsnummer);
				break;
			case "create":
				$auftrag = new Auftrag($id);
				$posten = $auftrag->getAuftragsPostenCheckTable();
				$invoice_posten = $auftrag->getInvoicePostenTable();

				$_SESSION['currentInvoice_orderId'] = $id;
				break;
			default:
				break;
		}
	}

	$link = Link::getPageLink('pdf') . "?type=rechnung";
	$rechnungsPDF = "<iframe src=\"" . $link . "\" id=\"showOffer\"></iframe>";

if ($target == "create"): ?>
	<div class="defCont">
		<h3>Auftrag <span id="orderId"><?=$id?></span></h3>
		<p><u>Posten zum Auftrag:</u></p>
		<?=$posten?>
		<p><u>Rechnungsposten:</u></p>
		<?=$invoice_posten?>
	</div>
	<button onclick="check(true);">Alle Posten (ab)wählen</button>
	<button onclick="check();">Übernehmen</button>
	<?php if ($auftrag != null || $auftrag->getAuftragspostenData() != 0): ?>
	<button onclick="generatePDF();">Rechnung abschließen</button>
	<?php else: ?>
	<button disabled>Rechnung abschließen</button>
	<?php endif; ?>
	<button action="action" onclick="window.history.go(-1); return false; "type="submit">Abbrechen</button>
	<br>
	<span>
		<?=$rechnungsPDF?>
	</span>
<?php elseif ($target == "view"): ?>
	<div>Rechnung:</div>
	<span id="rechnungsnummer"><?=$rechnungsnummer;?></span>
	<button onclick="print('rechnungsnummer', 'Rechnung');">Rechnungsblatt generieren</button>
<?php else: ?>
	<p>Es ist ein unerwarteter Fehler aufgetreten.</p>
	<button action="action" onclick="window.history.go(-1); return false; "type="submit">Zurück</button>
<?php endif; ?>