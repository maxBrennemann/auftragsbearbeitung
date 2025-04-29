<div class="mt-4 defCont w-full">
    <div>
        <p>Produktname:</p>
        <input type="text" class="productInfo input-primary w-64 mt-1 font-bold" data-type="productTitle" value="<?= $p->getBezeichnung() ?>">
    </div>

    <div class="mt-2">
        <p>Beschreibung:</p>
        <textarea class="productInfo input-primary w-64 mt-1" data-type="productDescription"><?= $p->getBeschreibung() ?></textarea>
    </div>

    <div class="mt-2">
        <p>Preis [€]:</p>
        <input type="number" class="productInfo input-primary mt-1" data-type="productPrice" step="0.01" value="<?= $p->getPrice() ?>">
    </div>

    <div class="mt-2 w-64">
        <?= Classes\Project\TemplateController::getTemplate("uploadFile", [
            "target" => "product",
        ]); ?>
    </div>

    <div id="showFilePrev" class="mt-2">
        <?= $showFiles ?>
    </div>

    <div id="addAttributeTable" class="mt-2"></div>
    <button data-binding="true" data-fun="addAttributes" class="btn-primary-new mt-2">Attribute hinzufügen</button>
</div>

<template id="addAttributes">
    <p class="underline">Attribute hinzufügen</p>
    <div class="grid grid-cols-3 mb-2 py-2 gap-1">
        <div class="border-r-2 overflow-y-scroll">
            <h3>Auswahl</h3>
            <select class="w-28" id="attributeSelector" multiple>
            </select>
            <button class="block btn-primary" data-fun="btnAttributeGroupSelector" data-binding="true">⟶</button>
        </div>
        <div class="border-r-2 overflow-y-scroll">
            <h3>Attribute</h3>
            <div id="showAttributeValues"></div>
            <button class="block btn-primary" data-fun="btnAttributeSelector" data-binding="true">⟶</button>
        </div>
        <div class="">
            <div id="addedValues">
                <h3>Hinzugefügt</h3>
            </div>
        </div>
    </div>
    <buton class="btn-primary mt-4 inline-block" id="btnSaveConfig">Übernehmen</buton>
</template>