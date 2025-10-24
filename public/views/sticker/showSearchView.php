<?php

use Src\Classes\Project\Icon;

?>

<div class="searchViewGrid">
    <div>
        <div class="relative">
            <input type="search" id="searchShopQuery" placeholder="Aufkleber suchen">
            <span id="searchShopBtn" class="absolute right-2"><?=Icon::getDefault("iconSearch")?></span>
        </div>
        <div id="showSearchResults" class="max-h-96 overflow-y-auto"></div>
    </div>
    <div>
        <?php foreach ($products as $product): ?>
        <label class="block">
            <input type="checkbox" checked data-article="<?=$product["id_product_reference"]?>">
            <span>Artikel <?=$product["id_product_reference"]?>: <?=$product["name"]?></span>
        </label>
        <?php endforeach; ?>
    </div>
</div>