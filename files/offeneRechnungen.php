<?php
	require_once('classes/project/Rechnung.php');

    $showOffeneRechnungen = Rechnung::getOffeneRechnungen();
?>
<br>
<div id="table"><?=$showOffeneRechnungen?></div>
<style>
    header {
        z-index: 2;
    }

    #table {
        max-height: 1500px;
        overflow: auto;
    }

    table {
        display: table;
        position: relative;
        text-align: left;
        z-index: 1;
    }

    tbody {
        display: table-header-group;
    }

	table th {
        position: -webkit-sticky;
		position: sticky;
        top: 0;
	}

</style>