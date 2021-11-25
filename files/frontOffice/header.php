<?php

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