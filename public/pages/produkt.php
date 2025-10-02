<?php

use Src\Classes\Link;
use Src\Classes\Project\Produkt;
use MaxBrennemann\PhpUtilities\Tools;

?>

<div class="mt-4 w-full">
	<a class="link-primary" href="<?= Link::getPageLink("attributes") ?>">Zu den Produktattributen</a>
	<a class="link-primary ml-2" href="<?= Link::getPageLink("neues-produkt") ?>">Zum Produktformular</a>

	<?php if (Tools::get("id") !== null): ?>
		<a class="link-primary ml-2" href="<?= Link::getPageLink("produkt") ?>">Zur Produktübersicht</a>
		<?= \Src\Classes\Controller\TemplateController::getTemplate("product", [
            "product" => new Produkt(Tools::get("id")),
            "showFiles" => Produkt::getFiles(Tools::get("id")),
            "id" => Tools::get("id"),
        ]); ?>
	<?php else: ?>
		<div class="defCont mt-4">
			<div id="tableContainer"></div>
			<h2 class="mt-4 font-bold">Produkte:</h2>
			<div class="grid grid-cols-3 gap-3 mt-1">
				<?php foreach (Produkt::getAllProducts() as $p) : ?>
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