<?php 
	require_once('classes/project/FormGenerator.php');
	require_once('classes/Upload.php');

	$id = isset($_GET["id"]) ? $_GET["id"] : -1;

	if ($id == -1) {
		$table = FormGenerator::createTable("produkt_varianten", false, true, "produkt", 1, false);
	}

	$p = new Produkt($id);
	$showFiles = Upload::getFilesProduct($id);
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
	<span style="display: none" id="product-id"><?=$id?></span>
<?php else: ?>
	<div id='tableContainer'><?=$table?></div>
<?php endif; ?>