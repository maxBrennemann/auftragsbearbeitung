<?php
	require_once('classes/Link.php');
	require_once('classes/project/FormGenerator.php');
	require_once('classes/project/Rechnung.php');
	
	$rechnung =  Link::getPageLink("rechnung");
	$rechnungsnummer = 0;
	
	/* shows old invoice */
	if(isset($_GET['id'])) {
		$rechnungsnummer = $_GET['id'];
		$rechnung = new Rechnung($rechnungsnummer);
	}

	$auftrag = null;

	$setInvoiceGenerator = false;
	if(isset($_GET['create'])) {
		$auftragsid = $_GET['create'];
		$setInvoiceGenerator = true;

		$auftrag = new Auftrag($auftragsid);
		$posten = $auftrag->getAuftragsPostenCheckTable();

		$_SESSION['currentInvoice_orderId'] = $auftragsid;
	}

	$link = Link::getPageLink('pdf')  . "?type=rechnung";
	$rechnungsPDF = "<iframe src=\"" . $link . "\" id=\"showOffer\"></iframe>";

if ($setInvoiceGenerator): ?>
	<div class="defCont">
		<h3>Auftrag <?=$auftragsid?></h3>
		<p>Posten zum Auftrag:</p>
		<?=$posten?>
	</div>
	<button onclick="closeOrder();">Alle Posten übernehmen</button>
	<button onclick="closeOrder();">Ausgewählte Posten übernehmen</button>
	<?php if ($auftrag == null || $auftrag->getAuftragspostenData() == 0): ?>
	<button onclick="generatePDF();">Rechnung abschließen</button>
	<?php else: ?>
	<button disabled>Rechnung abschließen</button>
	<?php endif; ?>
	<button action="action" onclick="window.history.go(-1); return false; "type="submit">Abbrechen</button>
	<span><?=$rechnungsPDF?></span>
<?php else: ?>
	<div>Rechnung:</div>
	<span id="rechnungsnummer"><?=$rechnungsnummer;?></span>
	<button onclick="print('rechnungsnummer', 'Rechnung');">Rechnungsblatt generieren</button>
<?php endif; ?>