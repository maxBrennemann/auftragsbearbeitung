<?php
	require_once('classes/project/Rechnung.php');

    $showOffeneRechnungen = Rechnung::getOffeneRechnungen();
?>
<?=$showOffeneRechnungen?>