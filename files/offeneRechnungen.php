<?php
	require_once('classes/project/Rechnung.php');

    $showOffeneRechnungen = Rechnung::getOffeneRechnungen();
?>
<span id="table"><?=$showOffeneRechnungen?></span>