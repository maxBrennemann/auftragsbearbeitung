<?php

use Src\Classes\Controller\TemplateController;
use Src\Classes\Project\Category;
use Src\Classes\Project\Icon;
use Src\Classes\Project\Produkt;
use MaxBrennemann\PhpUtilities\DBAccess;

$quelle = Produkt::getSources();
$categories = Category::getOneLayerRepresentation();
$attributeGroups = DBAccess::selectQuery("SELECT * FROM attribute_group");

?>
<?= TemplateController::getTemplate("productNav", ["current" => "neues-produkt"]) ?>

<section class="defCont max-w-3xl">
	<div class="section-head">
		<h2 class="section-title">Neues Produkt</h2>
	</div>

	<div class="field-grid">
		<label class="field col-span-full">
			<span class="field-label">Produktname</span>
			<input type="text" class="input-primary" id="productName">
		</label>
		<label class="field col-span-full">
			<span class="field-label">Beschreibung</span>
			<textarea class="input-primary" id="productDescription"></textarea>
		</label>
		<label class="field">
			<span class="field-label">Marke</span>
			<input type="text" class="input-primary" id="productBrand">
		</label>
		<label class="field">
			<span class="field-label">Kategorie</span>
			<select id="category" class="input-primary">
				<option value="-1" selected disabled>Bitte auswählen</option>
				<?php foreach ($categories as $c) : ?>
					<option value="<?= $c["id"] ?>"><?= $c["name"] ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<label class="field">
			<span class="field-label">Quelle</span>
			<select id="source" class="input-primary" data-write="true" data-fun="addSource">
				<option value="-1" selected disabled>Bitte auswählen</option>
				<?php foreach ($quelle as $q) : ?>
					<option value="<?= $q["id"] ?>"><?= $q["name"] ?></option>
				<?php endforeach; ?>
				<option value="addNew">Neue Quelle anlegen …</option>
			</select>
		</label>
		<label class="field">
			<span class="field-label">Einkaufspreis netto [€]</span>
			<input type="number" step="0.01" class="input-primary" id="purchasingPrice">
		</label>
		<label class="field">
			<span class="field-label">Verkaufspreis netto [€]</span>
			<input type="number" step="0.01" class="input-primary" id="productPrice">
		</label>
	</div>

	<div class="flex gap-2 mt-5">
		<button class="btn-primary" data-binding="true" data-fun="save"><?= Icon::get("check") ?>Produkt speichern</button>
		<button class="btn-secondary" data-binding="true" data-fun="cancel">Abbrechen</button>
	</div>
</section>

<template id="addSource">
	<div class="w-96 max-w-full flex flex-col gap-3">
		<p class="section-title">Neue Quelle anlegen</p>
		<label class="field">
			<span class="field-label">Name der Quelle</span>
			<input type="text" class="input-primary" id="sourceName">
		</label>
		<label class="field">
			<span class="field-label">Beschreibung</span>
			<textarea class="input-primary" id="sourceDescription"></textarea>
		</label>
	</div>
</template>
