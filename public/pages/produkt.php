<?php

use Src\Classes\Controller\TemplateController;
use Src\Classes\Project\Produkt;
use MaxBrennemann\PhpUtilities\Tools;

?>
<?= TemplateController::getTemplate("productNav", ["current" => "produkt"]) ?>

<?php if (Tools::get("id") !== null): ?>
	<?= TemplateController::getTemplate("product", [
	    "product" => new Produkt(Tools::get("id")),
	    "showFiles" => Produkt::getFiles(Tools::get("id")),
	    "id" => Tools::get("id"),
	]); ?>
<?php else: ?>
	<?php $products = Produkt::getAllProducts(); ?>
	<section class="defCont">
		<div class="section-head">
			<h2 class="section-title">Produkte</h2>
			<span class="section-meta"><?= count($products) == 1 ? "1 Produkt" : count($products) . " Produkte" ?></span>
		</div>
		<div id="tableContainer" class="mb-4 overflow-x-auto"></div>
		<?php if (empty($products)): ?>
			<p class="text-sm text-text-muted">Es gibt noch keine Produkte.</p>
		<?php else: ?>
			<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
				<?php foreach ($products as $p) : ?>
					<a href="<?= $p->getProduktLink() ?>" class="sheet flex gap-3 no-underline text-text transition-colors hover:border-ink" data-product-id="<?= $p->getProductId() ?>">
						<?php $images = $p->getImages(); ?>
						<?php if (!empty($images)): ?>
							<span class="flex-none grid place-items-center w-16 h-16 bg-[#f6f7f8] rounded-control overflow-hidden" data-image-id="<?= $images[0]->getImageId() ?>">
								<img src="<?= $images[0]->getImageURL() ?>" alt="" class="max-w-full max-h-full object-contain">
							</span>
						<?php endif; ?>
						<span class="min-w-0 flex flex-col">
							<span class="font-heading font-bold text-ink"><?= $p->getBezeichnung() ?></span>
							<span class="text-sm text-text-muted line-clamp-2"><?= $p->getBeschreibung() ?></span>
							<span class="mt-auto pt-1 text-sm font-semibold tabular-nums"><?= $p->getPriceWithTax() ?> €</span>
						</span>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</section>
<?php endif; ?>
