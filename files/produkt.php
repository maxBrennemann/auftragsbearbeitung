<?php 
	require_once('classes/project/FormGenerator.php');
	require_once('classes/project/Produkt.php');
	require_once('classes/Upload.php');

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
	$showFiles = Upload::getFilesProduct($id);
	$products = Produkt::getAllProducts();

?>
<?php if ($id != -1): ?>
	<span>
		<h2><?=$p->getBezeichnung()?></h2>
		<button>✎</button>
	</span>

	<br>
    <span>
		<p><?=$p->getBeschreibung()?></p>
		<button>✎</button>
	</span>
	<br>
	<span>
		<p><?=$p->getPreisBrutto()?> €</p>
		<button>✎</button>
	</span>
	<br>
	<form class="fileUploader" method="post" enctype="multipart/form-data" data-target="product" name="auftragUpload">
		Dateien hinzufügen:
		<input type="file" name="uploadedFile" multiple>
		<input name="produkt" value="<?=$id?>" hidden>
	</form>
	<div class="filesList defCont"></div>
	<div id="showFilePrev">
		<?=$showFiles?>
	</div>
	<style>
		main span > * {
			display: inline;
		}
	</style>
	<span>Attribute hinzufügen<br>
			<button onclick="getHTMLForAttributes();">Hinzufügen</button>
	</span>
	<div id="addAttributeTable"></div>
	<span style="display: none" id="product-id"><?=$id?></span>
<?php else: ?>
	<div id='tableContainer'><?=$table?></div>

	<div class="product-container">
		<?php foreach ($products as $p): ?>
			<div class="product-preview" data-product-id="<?=$p->getProductId()?>">
				<a href="<?=$p->getProduktLink()?>"><h2><?=$p->getBezeichnung()?></h2></a>
				<p><?=$p->getBeschreibung()?></p>
				<p><?=$p->getPreisBrutto()?> €</p>
				<button>In den Warenkorb</button>
				<?php foreach ($p->getImages() as $i): ?>
					<div data-image-id="<?=$i->getImageId()?>">
						<img src="<?=$i->getImageURL()?>" alt="" width="50px" height="auto">
					</div>
				<?php endforeach; ?>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>