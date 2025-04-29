<?php

use Classes\Link;
use MaxBrennemann\PhpUtilities\DBAccess;

use Classes\Project\Produkt;
use Classes\Project\Category;

$quelle = Produkt::getSources();
$categories = Category::getOneLayerRepresentation();
$attributeGroups = DBAccess::selectQuery("SELECT * FROM attribute_group");

?>

<div class="mt-4">
	<a class="link-primary" href="<?= Link::getPageLink("attributes") ?>">Zu den Produktattributen</a>
	<a class="link-primary ml-2" href="<?= Link::getPageLink("produkt") ?>">Zur Produktübersicht</a>

	<div class="defCont mt-4">
		<div>
			<p>Produktname:</p>
			<input type="text" class="input-primary w-64 mt-1 font-bold" id="productName">
		</div>

		<div class="mt-2">
			<p>Beschreibung:</p>
			<textarea class="input-primary w-64 mt-1" id="productDescription"></textarea>
		</div>

		<div class="mt-2">
			<p>Marke:</p>
			<input type="text" class="input-primary w-64 mt-1" id="productBrand">
		</div>

		<div class="mt-2">
			<p>Kategorie:</p>
			<select id="category" class="input-primary w-64 mt-1">
				<option value="-1" selected disabled>Bitte auswählen</option>
				<?php foreach ($categories as $c) : ?>
					<option value="<?= $c["id"] ?>"><?= $c["name"] ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="mt-2">
			<p>Quelle:</p>
			<select id="source" class="input-primary w-64 mt-1" data-write="true" data-fun="addSource">
				<option value="-1" selected disabled>Bitte auswählen</option>
				<?php foreach ($quelle as $q) : ?>
					<option value="<?= $q["id"] ?>"><?= $q["name"] ?></option>
				<?php endforeach; ?>
				<option value="addNew">Neue Option hinzufügen</option>
			</select>
		</div>

		<div class="mt-2">
			<p>Einkaufspreis netto [€]:</p>
			<input type="number" step="0.01" class="input-primary mt-1" id="purchasingPrice">
		</div>

		<div class="mt-2">
			<p>Verkaufspreis netto [€]:</p>
			<input type="number" step="0.01" class="input-primary mt-1" id="productPrice">
		</div>

		<div class="mt-3">
			<button class="inline-block btn-primary-new" data-binding="true" data-fun="save">Produkt speichern</button>
			<button class="inline-block btn-cancel" data-binding="true" data-fun="cancel">Abbrechen</button>
		</div>

	</div>
</div>

<template id="addSource">
	<div>
		<p class="font-semibold">Neue Quelle hinzufügen</p>
		<div>
			<p>Quellenname:</p>
			<input type="text" class="input-primary  w-64 mt-1" id="sourceName">
		</div>
		<div class="mt-2">
			<p>Beschreibung:</p>
			<textarea class="input-primary  w-64 mt-1" id="sourceDescription"></textarea>
		</div>
	</div>
</template>