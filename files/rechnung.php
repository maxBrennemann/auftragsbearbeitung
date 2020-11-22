<?php
	require_once('classes/Link.php');
	require_once('classes/project/FormGenerator.php');
	require_once('classes/project/Rechnung.php');
	
	$rechnung =  Link::getPageLink("rechnung");
	$rechnungsnummer = 0;
	
	if(isset($_GET['id'])) {
		$rechnungsnummer = $_GET['id'];
		$rechnung = new Rechnung($rechnungsnummer);
	}

	$setInvoiceGenerator = false;
	if(isset($_GET['create'])) {
		$auftragsid = $_GET['create'];
		$setInvoiceGenerator = true;

		$auftrag = new Auftrag($auftragsid);
		$posten = $auftrag->getAuftragspostenAsTable();

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
	<button>Alle Posten übernehmen</button><button>Rechnung abschließen</button>
	<span><?=$rechnungsPDF?></span>
<?php else: ?>
	<div>Rechnung:</div>
	<span id="rechnungsnummer"><?=$rechnungsnummer;?></span>
	<button onclick="print('rechnungsnummer', 'Rechnung');">Rechnungsblatt generieren</button>
<?php endif; ?>