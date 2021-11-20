<?php

require_once('classes/Link.php');
require_once('classes/project/Produkt.php');
require_once('classes/front/Navigation.php');

$globalCSS =  Link::getGlobalCSS();
$globalFrontCSS =  Link::getGlobalFrontCSS();

$products = Produkt::getAllProducts();
$menutopitems = Navigation::getNavigationLinks("top");

?>
<!DOCTYPE>
<html>
<head>
    <link rel="stylesheet" href="<?=$globalCSS?>">
    <link rel="stylesheet" href="<?=$globalFrontCSS?>">
    <title>Titel</title>
</head>
<body>
    <header>
    <button class="cart">🛒</button>
    <nav class="menu-top">
        <ul>
            <?php foreach ($menutopitems as $m): ?>
                <li><a href="<?=$m->getItemLink()?>"><?=$m->getItemName()?></a></li>
            <?php endforeach; ?>
        </ul>
    </nav>
    </header>
    <main>
    <div class="product-container">
        <?php foreach ($products as $p): ?>
            <div class="product-preview" data-product-id="<?=$p->getProductId()?>">
                <a href=""><h2><?=$p->getBezeichnung()?></h2></a>
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
    </main>
    <footer>
    </footer>
</body>
</html>