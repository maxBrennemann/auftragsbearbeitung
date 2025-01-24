<?php

use MaxBrennemann\PhpUtilities\DBAccess;

$data = DBAccess::selectQuery("SELECT * FROM leistung");
?>
<a href="#" class="font-bold">Folie konfigurieren</a>
<a href="#" class="font-bold">T-Shirt konfigurieren</a>
<div class="grid grid-cols-3 gap-4" id="leistungen">
	<?php foreach ($data as $leistung): ?>
		<div class="leistungen border-none defCont p-4 rounded-lg" data-remove-id="<?=$leistung['Nummer']?>">
			<p class="font-bold mb-2">
				<input class="bg-inherit w-fit" value="<?=$leistung['Bezeichnung']?>">
			</p>
			<p>Beschreibung
				<input class="w-full block m-1 text-slate-600 rounded-lg p-2" value="<?=$leistung['Beschreibung']?>">
			</p>
			<p>Quelle
				<input class="block m-1 text-slate-600 rounded-lg p-2" value="<?=$leistung['Quelle']?>">
			</p>
			<p>Aufschlag (%)
				<input class="block m-1 text-slate-600 rounded-lg p-2" type="number" value="<?=$leistung['Aufschlag']?>">
			</p>
			<button class="px-4 py-2 m-1 font-semibold text-sm bg-blue-200 text-slate-600 rounded-lg shadow-sm border-none">Löschen</button>
			<button class="px-4 py-2 m-1 font-semibold text-sm bg-blue-200 text-slate-600 rounded-lg shadow-sm border-none">Speichern</button>
		</div>
	<?php endforeach; ?>
	<div class="border-none defCont p-4 rounded-lg">
		<p class="font-bold">Neue Leistung hinzufügen</p>
		<label>
			Bezeichnung
			<input class="w-full block m-1 text-slate-600 rounded-lg p-2" type="text" maxlength="32" id="bezeichnung">
		</label>
		<label>
			Beschreibung
			<textarea class="block m-1 text-slate-600 rounded-lg p-2" id="description"></textarea>
		</label>
		<label>
			Quelle
			<input class="block m-1 text-slate-600 rounded-lg p-2" type="text" id="source">
		</label>
		<label>
			Aufschlag (%)
			<input class="block m-1 text-slate-600 rounded-lg p-2" type="number" id="aufschlag">
		</label>
		<button class="px-4 py-2 m-1 font-semibold text-sm bg-blue-200 text-slate-600 rounded-lg shadow-sm border-none" id="addNew">Hinzufügen</button>
		<button class="px-4 py-2 m-1 font-semibold text-sm bg-blue-200 text-slate-600 rounded-lg shadow-sm border-none" id="cancleNew">Abbrechen</button>
	<div>
</div>