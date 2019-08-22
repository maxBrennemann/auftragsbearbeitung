<?php 
	require_once('classes/project/Auftrag.php');

	$auftragsId = -1;
	if (isset($_GET['id'])) {
		$auftragsId = $_GET['id'];
		$Auftrag = new Auftrag();
	}
?>

<?php if ($auftragsId == -1) : ?>
	<input type="number" min="1" oninput="document.getElementById('auftragsLink').href = '<?=$auftragAnzeigen?>?id=' + this.value;">
	<a href="#" id="auftragsLink">Auftrag anzeigen</a>
<?php else: ?>
	<div></div>
<?php endif; ?>