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