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
	if(isset($_GET['create'])) {
		$nextId = Rechnung::getNextNumber();
		$auftragsid = $_GET['create'];
		echo "Rechnung $nextId wird erstellt";
		DBAccess::updateQuery("UPDATE auftrag SET Rechnungsnummer = $nextId WHERE Auftragsnummer = $auftragsid");
	}
?>

<?php if ($rechnungsnummer == 0) : ?>
	<p>Auftragsnummer: <input type="number" min="1" oninput="document.getElementById('rechnungsLink').href = '<?=$rechnung?>?create=' + this.value;"></p>
	<a href="#" id="rechnungsLink">Rechnung generieren</a>
<?php else: ?>
	<div>Rechnung:</div>
	<span id="rechnungsnummer"><?=$rechnungsnummer;?></span>
	<button onclick="print('rechnungsnummer', 'Rechnung');">Rechnungsblatt generieren</button>
<?php endif; ?>