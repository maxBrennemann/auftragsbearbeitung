<?php
	require_once('classes/Link.php');
	require_once('classes/project/FormGenerator.php');
	require_once('classes/project/Angebot.php');
	
	$rechnung =  Link::getPageLink("rechnung");
	$auftragsnummer = 0;
	
	if(isset($_GET['id'])) {
		$rechnungsnummer = $_GET['id'];
		$rechnung = new Angebot($auftragsnummer);
	}
?>

<?php if ($auftragsnummer == 0) : ?>
	<p>Auftragsnummer: <input type="number" min="1" oninput="document.getElementById('auftragsLink').href = '<?=$rechnung?>?show=' + this.value;">
	<a href="#" id="auftragsLink">Angebote anzeigen</a></p>
	<p>Angebotsnummer: <input type="number" min="1" oninput="document.getElementById('angebotsLink').href = '<?=$rechnung?>?show=' + this.value;">
	<a href="#" id="angebotsLink">Angebot anzeigen</a></p>
<?php else: ?>
	<div>Rechnung:</div>
	<span id="auftragsnummer"><?=$auftragsnummer;?></span>
	<button onclick="print('auftragsnummer', 'Angebot');">Angebot generieren</button>
<?php endif; ?>