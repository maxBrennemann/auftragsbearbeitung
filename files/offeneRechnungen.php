<?php

use Classes\Project\Rechnung;

$showOffeneRechnungen = Rechnung::getOffeneRechnungen();
?>
<br>
<div id="table"><?=$showOffeneRechnungen?></div>