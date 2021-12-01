<?php

$globalCSS =  Link::getGlobalCSS();
$globalFrontCSS =  Link::getGlobalFrontCSS();

$products = Produkt::getAllProducts();
$menutopitems = Navigation::getNavigationLinks("top");

$cart = Link::getFrontOfficeLink("cart");

?>
<!DOCTYPE>
<html>
<head>
    <link rel="stylesheet" href="<?=$globalCSS?>">
    <link rel="stylesheet" href="<?=$globalFrontCSS?>">
    <title><?=$title?> - Shop</title>
</head>
<body>
    <header>
    <a href="<?=$cart?>" class="cart">🛒</a>
    <nav class="menu-top">
        <ul>
            <?php foreach ($menutopitems as $m): ?>
                <li><a href="<?=$m->getItemLink()?>"><?=$m->getItemName()?></a></li>
            <?php endforeach; ?>
        </ul>
    </nav>
    </header>
    <main>