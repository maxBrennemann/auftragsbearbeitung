<?php

require_once('classes/project/Produkt.php');

/* check if a specific product is requested */
$id = isset($_GET["id"]) ? $_GET["id"] : -1;

if ($id == -1) {
	$linker = new Link();
	$linker->addBaseLink("produkt");

	$productTable = new Table("produkt");
	$productTable->addLink($linker);

	$linker->setIterator("id", $productTable->getData(), "Nummer");

	$table = $productTable->getTable();
}

$p = new Produkt($id);
$showFiles = Produkt::getFiles($id);
$products = Produkt::getAllProducts();

?>
<div class="mt-4">
		<a class="link-button" href="<?= Link::getPageLink("attributes") ?>">Zu den Produktattributen</a>
		<a class="link-button" href="<?= Link::getPageLink("neues-produkt") ?>">Zum Produktformular</a>

<?php
/* konkrete Produktseite */
if ($id != -1) : ?>
	<div class="mt-4 defCont">
		<input type="text" class="productInfo input-primary font-bold" data-type="productTitle" value="<?= $p->getBezeichnung() ?>">

		<p class="mt-2"><textarea class="productInfo input-primary" data-type="productDescription"><?= $p->getBeschreibung() ?></textarea></p>

		<p><input type="number" class="productInfo input-primary" data-type="productPrice" step="0.01" value="<?= $p->getPrice() ?>"> €</p>

		<form class="fileUploader mt-2" method="post" enctype="multipart/form-data" data-target="product" name="auftragUpload">
			Dateien hinzufügen:
			<input type="file" name="uploadedFile" multiple>
			<input name="produkt" value="<?= $id ?>" hidden>
		</form>

		<div class="filesList defCont"></div>
		<div id="showFilePrev">
			<?= $showFiles ?>
		</div>

		<div id="addAttributeTable"></div>
		<button id="btnAddAttribute" class="btn-primary">Attribute hinzufügen</button>
		
		<span style="display: none" id="productId" data-id="<?= $id ?>"></span>
	</div>
	<div id="addAttributes" class="hidden z-20 h-2/6 w-2/6 fixed m-auto inset-x-0 inset-y-0 bg-white p-5 rounded-md shadow-2xl">
		<p class="underline">Attribute hinzufügen</p>
		<button class="close closeButton" id="btnToggle">×</button>
		<div class="grid grid-cols-3 mb-2 py-2 gap-1">
			<div class="border-r-2 overflow-y-scroll">
				<h3>Auswahl</h3>
				<select class="w-28" id="attributeSelector" multiple>
				</select>
				<button class="block btn-primary" id="btnAttributeGroupSelector">⟶</button>
			</div>
			<div class="border-r-2 overflow-y-scroll">
				<h3>Attribute</h3>
				<div id="showAttributeValues"></div>
				<button class="block btn-primary" id="btnAttributeSelector">⟶</button>
			</div>
			<div class="">
				<div id="addedValues">
					<h3>Hinzugefügt</h3>
				</div>
			</div>
		</div>
		<buton class="btn-primary mt-4 inline-block" id="btnSaveConfig">Übernehmen</buton>
		<buton class="btn-attention mt-4 inline-block" id="btnAbort">Abbrechen</buton>
	</div>
<?php else : ?>
	<div class="defCont mt-4">
		<div id='tableContainer'>
			<?= $table ?>
		</div>
		<h2 class="mt-4 font-bold">Produkte <span class="text-xs">(Link zur Frontpage)</span>:</h2>
		<div class="grid grid-cols-3 gap-3 mt-1">
			<?php foreach ($products as $p) : ?>
				<div class="bg-white border-2 rounded-lg p-4 border-gray-700 m-3" data-product-id="<?= $p->getProductId() ?>">
					<a href="<?= $p->getProduktLink() ?>">
						<h2 class="font-bold"><?= $p->getBezeichnung() ?></h2>
					</a>
					<p><?= $p->getBeschreibung() ?></p>
					<p><?= $p->getPriceWithTax() ?> €</p>
					<?php foreach ($p->getImages() as $i) : ?>
						<div data-image-id="<?= $i->getImageId() ?>">
							<img src="<?= $i->getImageURL() ?>" alt="" width="50px" height="auto">
						</div>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
<?php endif; ?>
</div>