<?php

/**
 * @var \Src\Classes\Project\Produkt $product
 */

?>
<div class="mx-2.5 mt-2 mb-4">
    <span class="group-label text-text-muted">Produkt <?= $product->getProductId() ?></span>
    <h2 class="page-title"><?= $product->getBezeichnung() ?></h2>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2">
    <section class="defCont">
        <div class="section-head">
            <h2 class="section-title">Produktdaten</h2>
            <span class="section-meta">Änderungen werden sofort gespeichert</span>
        </div>
        <div class="field-grid">
            <label class="field">
                <span class="field-label">Produktnummer</span>
                <input type="text" class="productInfo input-primary" disabled value="<?= $product->getProductId() ?>">
            </label>
            <label class="field">
                <span class="field-label">Preis [€]</span>
                <input type="number" class="productInfo input-primary" data-type="productPrice" step="0.01" value="<?= $product->getPrice() ?>">
            </label>
            <label class="field col-span-full">
                <span class="field-label">Produktname</span>
                <input type="text" class="productInfo input-primary" data-type="productTitle" value="<?= $product->getBezeichnung() ?>">
            </label>
            <label class="field col-span-full">
                <span class="field-label">Beschreibung</span>
                <textarea class="productInfo input-primary" data-type="productDescription"><?= $product->getBeschreibung() ?></textarea>
            </label>
        </div>
    </section>

    <section class="defCont">
        <div class="section-head">
            <h2 class="section-title">Bilder</h2>
        </div>
        <?= \Src\Classes\Controller\TemplateController::getTemplate("uploadFile", [
            "target" => "product",
            "accept" => "image/*",
        ]); ?>
        <div id="showFilePrev" class="mt-3">
            <?= $showFiles ?>
        </div>
    </section>
</div>

<section class="defCont">
    <div class="section-head">
        <h2 class="section-title">Attribute</h2>
        <button data-binding="true" data-fun="addAttributes" class="btn-secondary btn-sm"><?= \Src\Classes\Project\Icon::get("plus", 14) ?>Attribute hinzufügen</button>
    </div>
    <div id="addAttributeTable" class="overflow-x-auto"></div>
</section>

<template id="addAttributes">
    <div class="w-[40rem] max-w-full">
        <p class="section-title mb-3">Attribute hinzufügen</p>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="sheet flex flex-col gap-2">
                <span class="group-label">1. Eigenschaft wählen</span>
                <select class="input-primary w-full h-40" id="attributeSelector" multiple aria-label="Eigenschaften"></select>
                <button class="btn-secondary btn-sm mt-auto self-start" data-fun="btnAttributeGroupSelector" data-binding="true">Übernehmen</button>
            </div>
            <div class="sheet flex flex-col gap-2">
                <span class="group-label">2. Werte wählen</span>
                <div id="showAttributeValues" class="overflow-y-auto h-40"></div>
                <button class="btn-secondary btn-sm mt-auto self-start" data-fun="btnAttributeSelector" data-binding="true">Übernehmen</button>
            </div>
            <div id="addedValues" class="sheet flex flex-col gap-1">
                <span class="group-label">Hinzugefügt</span>
            </div>
        </div>
    </div>
</template>
