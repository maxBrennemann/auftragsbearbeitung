<?php

use Classes\Models\Leistung;

$data = Leistung::all();
?>

<h2 class="font-bold">Übersicht über die angebotenen Leistungen</h2>
<div class="grid grid-cols-3 gap-3" id="leistungen">
	<?php foreach ($data as $leistung): ?>
		<div class="defCont" data-service-id="<?= $leistung['Nummer'] ?>">
			<p class="font-semibold mb-2"><?= $leistung['Bezeichnung'] ?></p>

			<p>Bezeichnung</p>
			<input class="input-primary" value="<?= $leistung['Bezeichnung'] ?>">

			<p>Beschreibung</p>
			<input class="input-primary" value="<?= $leistung['Beschreibung'] ?>">

			<p>Quelle</p>
			<input class="input-primary" value="<?= $leistung['Quelle'] ?>">

			<p>Aufschlag (%)</p>
			<input class="input-primary" type="number" value="<?= $leistung['Aufschlag'] ?>">

			<div class="mt-2">
				<button class="btn-primary" data-fun="save" data-binding="true">Speichern</button>
				<button class="btn-delete" data-fun="delete" data-binding="true">Löschen</button>
			</div>
		</div>
	<?php endforeach; ?>
	<div class="defCont">
		<p class="font-semibold mb-2">Neue Leistung hinzufügen</p>

		<p>Bezeichnung</p>
		<input class="input-primary" id="addName">

		<p>Beschreibung</p>
		<input class="input-primary" id="addDescription">

		<p>Quelle</p>
		<input class="input-primary" id="addSource">

		<p>Aufschlag (%)</p>
		<input class="input-primary" type="number" id="addSurcharge">

		<div class="mt-2">
			<button class="btn-primary" data-fun="add" data-binding="true">Hinzufügen</button>
			<button class="btn-cancel" data-fun="cancel" data-binding="true">Abbrechen</button>
		</div>
	<div>
</div>