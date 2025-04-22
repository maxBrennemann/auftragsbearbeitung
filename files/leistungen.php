<?php

use Classes\Project\Models\Model;

$data = Model::init("leistung");
$data = $data->read();
?>

<h2 class="font-bold">Übersicht über die angebotenen Leistungen</h2>
<div class="grid grid-cols-3 gap-3" id="leistungen">
	<?php foreach ($data as $leistung): ?>
		<div class="defCont" data-service-id="<?= $leistung['Nummer'] ?>">
			<p class="font-semibold mb-2"><?= $leistung['Bezeichnung'] ?></p>

			<p>Bezeichnung</p>
			<input class="input-primary-new" value="<?= $leistung['Bezeichnung'] ?>">

			<p>Beschreibung</p>
			<input class="input-primary-new" value="<?= $leistung['Beschreibung'] ?>">

			<p>Quelle</p>
			<input class="input-primary-new" value="<?= $leistung['Quelle'] ?>">

			<p>Aufschlag (%)</p>
			<input class="input-primary-new" type="number" value="<?= $leistung['Aufschlag'] ?>">

			<div class="mt-2">
				<button class="btn-primary-new" data-fun="save" data-binding="true">Speichern</button>
				<button class="btn-delete" data-fun="delete" data-binding="true">Löschen</button>
			</div>
		</div>
	<?php endforeach; ?>
	<div class="defCont">
		<p class="font-semibold mb-2">Neue Leistung hinzufügen</p>

		<p>Bezeichnung</p>
		<input class="input-primary-new" id="addName">

		<p>Beschreibung</p>
		<input class="input-primary-new" id="addDescription">

		<p>Quelle</p>
		<input class="input-primary-new" id="addSource">

		<p>Aufschlag (%)</p>
		<input class="input-primary-new" type="number" id="addSurcharge">

		<div class="mt-2">
			<button class="btn-primary-new" data-fun="add" data-binding="true">Hinzufügen</button>
			<button class="btn-cancel" data-fun="cancel" data-binding="true">Abbrechen</button>
		</div>
	<div>
</div>