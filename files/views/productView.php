<?php

/** 
 * @var Classes\Project\Produkt $product 
 */

?>
<div class="mt-4 defCont w-full">
    <div>
        <p>Produktnummer:</p>
        <input type="text" class="productInfo input-primary mt-1" disabled value="<?= $product->getProductId() ?>">
    </div>

    <div class="mt-2">
        <p>Produktname:</p>
        <input type="text" class="productInfo input-primary w-64 mt-1 font-bold" data-type="productTitle" value="<?= $product->getBezeichnung() ?>">
    </div>

    <div class="mt-2">
        <p>Beschreibung:</p>
        <textarea class="productInfo input-primary w-64 mt-1" data-type="productDescription"><?= $product->getBeschreibung() ?></textarea>
    </div>

    <div class="mt-2">
        <p>Preis [€]:</p>
        <input type="number" class="productInfo input-primary mt-1" data-type="productPrice" step="0.01" value="<?= $product->getPrice() ?>">
    </div>

    <div class="mt-2 w-64">
        <?= Classes\Project\TemplateController::getTemplate("uploadFile", [
            "target" => "product",
            "accept" => "image/*",
        ]); ?>
    </div>

    <div id="showFilePrev" class="mt-2">
        <?= $showFiles ?>
    </div>

    <div id="addAttributeTable" class="mt-2"></div>
    <button data-binding="true" data-fun="addAttributes" class="btn-primary mt-2">Attribute hinzufügen</button>
</div>

<template id="addAttributes">
    <div>
        <p class="font-bold">Attribute hinzufügen</p>
        <div class="grid grid-cols-3 mb-2 py-2 gap-1 h-64">
            <div class="border-r-2 w-48 flex flex-col">
                <p class="font-semibold">Auswahl</p>
                <select class="overflow-y-scroll h-40 mt-2" id="attributeSelector" multiple>
                </select>
                <div class="mt-auto">
                    <button class="btn-primary" data-fun="btnAttributeGroupSelector" data-binding="true">Übernehmen</button>
                </div>
            </div>
            <div class="ml-2 border-r-2 w-48 flex flex-col">
                <p class="font-semibold">Attribute</p>
                <div id="showAttributeValues" class="mt-2 overflow-y-scroll h-40"></div>
                <div class="mt-auto">
                    <button class="btn-primary" data-fun="btnAttributeSelector" data-binding="true">Übernehmen</button>
                </div>
            </div>
            <div id="addedValues" class="ml-2 w-48 flex flex-col">
                <p class="font-semibold">Hinzugefügt</p>
            </div>
        </div>
    </div>
</template>