<?php
require_once('classes/DBAccess.php');

$data = DBAccess::selectQuery("SELECT * FROM leistung");
?>

<a href="#" class="abutton">Folie konfigurieren</a>
<a href="#" class="abutton">T-Shirt konfigurieren</a>

<div class="container">
	<?php foreach ($data as $leistung): ?>
		<div class="leistungsblock">
			<h4><?=$leistung['Bezeichnung']?></h4>
			<p>Beschreibung: <?=$leistung['Beschreibung']?></p>
			<p>Quelle: <?=$leistung['Quelle']?></p>
			<p>Aufschlag: <?=$leistung['Aufschlag']?>%</p>
			<button onclick="remove('<?=$leistung['Nummer']?>')">🗑</button>
		</div>
	<?php endforeach; ?>
	<div class="leistungsblock">
		<span>Bezeichnung: <input type="text"  maxlength="32"></input></span><br>
		<span>Beschreibung: <input type="text"></input></span><br>
		<span>Quelle: <input type="text" maxlength="64"></input></span><br>
		<span>Aufschlag: <input type="number"></input></span><br>
		<button onclick="add()">Hinzufügen</button>
	<div>
</div>