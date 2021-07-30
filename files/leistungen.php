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
			<p><span>Beschreibung:</span> <?=$leistung['Beschreibung']?></p>
			<p><span>Quelle:</span> <?=$leistung['Quelle']?></p>
			<p><span>Aufschlag:</span> <?=$leistung['Aufschlag']?>%</p>
			<button onclick="remove('<?=$leistung['Nummer']?>')">🗑</button>
		</div>
	<?php endforeach; ?>
	<div class="leistungsblock">
		<div class="leistungsHeader">
			<p>Neue Leistung hinzufügen</p>
		</div>
		<div>
			<p>
				<span>Bezeichnung:</span>
				<br>
				<input type="text"  maxlength="32" id="bezeichnung"></input>
			</p>
			<p>
				<span>Beschreibung:</span>
				<br>
				<textarea id="description"></textarea>
			</p>
			<p>
				<span>Quelle:</span>
				<br>
				<input type="text" maxlength="64" id="source"></input>
			</p>
			<p>
				<span>Aufschlag (%):</span>
				<br>
				<input type="number" id="aufschlag"></input>
			</p>
		</div>
		<button onclick="add()">Hinzufügen</button>
	<div>
</div>