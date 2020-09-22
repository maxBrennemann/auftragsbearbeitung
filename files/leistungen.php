<?php
require_once('classes/DBAccess.php');

$data = DBAccess::selectQuery("SELECT * FROM leistung");
?>

<a href="#" class="abutton">Folie konfigurieren</a>
<a href="#" class="abutton">T-Shirt konfigurieren</a>

<div class="container">
	<?php foreach ($data as $leistung): ?>
		<div class="leistungsblock">
			<div class="leistungsHeader">
				<p><?=$leistung['Bezeichnung']?></p>
			</div>
			<p>Beschreibung: <?=$leistung['Beschreibung']?></p>
			<p>Quelle: <?=$leistung['Quelle']?></p>
			<p>Aufschlag: <?=$leistung['Aufschlag']?>%</p>
			<button onclick="remove('<?=$leistung['Nummer']?>')">🗑</button>
		</div>
	<?php endforeach; ?>
	<div class="leistungsblock">
		<div class="leistungsHeader">
			<p>Neue Leistung hinzufügen</p>
		</div>
		<div>
			<span>Bezeichnung: <input type="text"  maxlength="32" id="bezeichnung"></input></span><br>
			<span>Beschreibung: <input type="text" id="description"></input></span><br>
			<span>Quelle: <input type="text" maxlength="64" id="source"></input></span><br>
			<span>Aufschlag (%): <input type="number" id="aufschlag"></input></span><br>
		</div>
		<button onclick="add()">Hinzufügen</button>
	<div>
</div>