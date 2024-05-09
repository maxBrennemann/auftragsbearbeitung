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

/* konkrete Produktseite */
if ($id != -1) : ?>
	<div class="mt-4">
		<h2 class="font-bold"><input type="text" class="productInfo" data-type="productTitle" value="<?= $p->getBezeichnung() ?>"></h2>

		<p>
			<textarea class="productInfo" data-type="productDescription"><?= $p->getBeschreibung() ?></textarea>
		</p>

		<p><input type="number" class="productInfo" data-type="productPrice" step="0.01" value="<?= $p->getPrice() ?>"> €</p>

		<form class="fileUploader" method="post" enctype="multipart/form-data" data-target="product" name="auftragUpload">
			Dateien hinzufügen:
			<input type="file" name="uploadedFile" multiple>
			<input name="produkt" value="<?= $id ?>" hidden>
		</form>

		<div class="filesList defCont"></div>

		<div id="showFilePrev">
			<?= $showFiles ?>
		</div>

		<div id="addAttributeTable"></div>
		<button onclick="getHTMLForAttributes();" class="btn-primary">Attribute hinzufügen</button>
		<button onclick="sendAttributeTable();" class="btn-primary">Abschicken</button>
		<span style="display: none" id="productId" data-id="<?= $id ?>"></span>
	</div>
<?php else : ?>
	<div class="mt-4">
		<a class="link-button" href="<?= Link::getPageLink("attributes") ?>">Zu den Produktattributetn</a>
		<a class="link-button" href="<?= Link::getPageLink("neues-produkt") ?>">Zum Produktformular</a>

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
	</div>
<?php endif; ?>