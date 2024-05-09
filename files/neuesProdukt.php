<?php

require_once("classes/project/Produkt.php");

$quelle = Produkt::getSources();
$attributeGroups = DBAccess::selectQuery("SELECT * FROM attribute_group");

?>

<div class="mt-4">
	<a class="link-button" href="<?= Link::getPageLink("attributes") ?>">Zu den Produktattributetn</a>
	<a class="link-button" href="<?= Link::getPageLink("produkt") ?>">Zu den Produkten</a>
	<div class="defCont mt-4">
		<p>Produktname</p>
		<input type="text" class="input-primary" id="productName">

		<p>Marke</p>
		<input type="text" class="input-primary" id="productBrand">

		<p>Kategorie</p>
		<select id="category" class="input-primary">
			<option value="-1" selected disabled>Bitte auswählen</option>
			<?php foreach ($quelle as $q) : ?>
				<option value="<?= $q['id'] ?>"><?= $q['name'] ?></option>
			<?php endforeach; ?>
			<option value="addNew">Neue Option hinzufügen</option>
		</select>

		<p>Quelle</p>
		<select id="source" class="input-primary">
			<option value="-1" selected disabled>Bitte auswählen</option>
			<?php foreach ($quelle as $q) : ?>
				<option value="<?= $q['id'] ?>"><?= $q['name'] ?></option>
			<?php endforeach; ?>
			<option value="addNew">Neue Option hinzufügen</option>
		</select>

		<p>Verkaufspreis netto</p>
		<input type="number" step="0.01" class="input-primary" id="productPrice">

		<p>Einkaufspreis netto</p>
		<input type="number" step="0.01" class="input-primary" id="purchasingPrice">

		<p>Beschreibung</p>
		<textarea class="input-primary" id="productDescription"></textarea>

		<div>
			<button class="inline-block btn-primary" id="save">Produkt speichern</button>
			<button class="inline-block btn-attention" id="abort">Abbrechen</button>
		</div>

	</div>
	<div id="addSource" class="hidden z-20 h-1/4 w-1/4 fixed m-auto inset-x-0 inset-y-0">
		<div class="bg-gray-200 border-2 shadow-2xl p-3 border-gray-600 rounded-md">
			<p>Quellenname</p>
			<input type="text" class="input-primary" id="sourceName">

			<p>Beschreibung</p>
			<textarea class="input-primary" id="sourceDescription"></textarea>

			<div>
				<button class="inline-block btn-primary" id="saveSource">Quelle speichern</button>
				<button class="inline-block btn-attention" id="abortSource">Abbrechen</button>
			</div>
		</div>
	</div>
</div>