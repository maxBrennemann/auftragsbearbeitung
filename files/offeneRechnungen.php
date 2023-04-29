<?php
	require_once('classes/project/Rechnung.php');

    $showOffeneRechnungen = Rechnung::getOffeneRechnungen();
?>
<br>
<div id="table"><?=$showOffeneRechnungen?></div>