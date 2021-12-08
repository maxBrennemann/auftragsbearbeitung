<?php

require_once('classes/front/Breadcrumb.php');

$globalCSS =  Link::getGlobalCSS();
$globalFrontCSS =  Link::getGlobalFrontCSS();

$products = Produkt::getAllProducts();
$menutopitems = Navigation::getNavigationLinks("top");

$cart = Link::getFrontOfficeLink("cart");

?>
<!DOCTYPE html>
<html>
<head>
    <!--<link rel="stylesheet" href="<?=$globalCSS?>">-->
    <link rel="stylesheet" href="<?=$globalFrontCSS?>">
    <title><?=$title?> - Shop</title>
</head>
<body>
    <header>
        <div class="header-container">
        <nav class="menu-top">
            <ul>
                <?php foreach ($menutopitems as $m): ?>
                    <li><a href="<?=$m->getItemLink()?>"><?=$m->getItemName()?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>
        <div>
            <span>Suche: <input type="text"></span>
        </div>
        <div class="cart-wrapper">
            <a href="<?=$cart?>" class="cart">🛒</a>
                </div>
        </div>
    </header>
    <main>
        <nav>
            <?=Breadcrumb::getNav()?>
        </nav>