<?php 
	require_once('classes/project/FormGenerator.php');


	$table = FormGenerator::createTable("produkt_varianten", false, true, "produkt", 1, false);
	echo "<div id='tableContainer'>" . $table . "</div>";
?>