<?php
require_once('classes/project/modules/sticker/StickerTagManager.php');
require_once('classes/project/modules/sticker/StickerImage.php');
require_once('classes/project/modules/sticker/StickerCollection.php');
require_once('classes/project/modules/sticker/ChatGPTConnection.php');

$id = 0;

$stickerCollection = null;
$stickerTagManager = null;
$stickerChangelog = null;
$stickerImage = null;
$chatGPTConnection = null;

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    $stickerImage = new StickerImage($id);
    $stickerCollection = new StickerCollection($id);
    $stickerTagManager = new StickerTagManager($id, $stickerCollection->getName());
    $stickerChangelog = new StickerChangelog($id);
    $chatGPTConnection = new ChatGPTConnection($id);

    $priceType = $stickerCollection->getTextil()->getPriceType();
}

if ($id == 0): ?>
    <a href="<?=Link::getPageLink("sticker-overview")?>">Zur Motivübersicht</a>
<?php else: ?>
<script src="<?=Link::getResourcesShortLink("tableeditor.js", "js")?>"></script>
<?=$stickerCollection->checkProductErrorStatus() ? $stickerCollection->getErrorMessage() : ""?>
<div class="defCont cont1">
    <div>
        <h2 class="font-semibold">Motiv <input id="name" class="titleInput inline" value="<?=$stickerCollection->getName();?>">
            <?php if ($stickerCollection->getIsMarked() == "0"): ?>
            <span data-binding="true" data-fun="bookmark inline"><?=Icon::$iconBookmark?></span>
            <?php else: ?>
            <span data-binding="true" data-fun="unbookmark" class="bookmarked inline"><?=Icon::$iconUnbookmark?></span>
            <?php endif; ?>
        </h2>
        <p class="mt-2">Artikelnummer: <span id="motivId" data-variable="true"><?=$id?></span></p>
        <p>Erstellt am <input type="date" class="rounded-sm" id="creationDate" value="<?=$stickerCollection->getCreationDate()?>"></p>
        <button class="btn-primary mt-2" data-fun="transferAll" data-binding="true">Alles erstellen/ aktualisieren</button>
    </div>
    <div>
        <p>Weitere Dateien (SVGs, CorelDraw, ...):</p>
        <?=insertTemplate("classes/project/modules/sticker/views/stickerFileView.php", ["images" => $stickerImage->getGeneralImages(), "files" => $stickerImage->getFiles()])?>
        <div id="delete-menu">
            <div class="item">
                <?=Icon::$iconDelete?>
                <span>Löschen</span>
            </div>
        </div>
    </div>
</div>
<div class="cont2">
    <section class="defCont">
        <p class="pHeading">Aufkleber
            <input class="titleInput hidden" value="<?=$stickerCollection->getAufkleber()->getAltTitle()?>" data-write="true" data-type="aufkleber" data-fun="changeAltTitle" placeholder="Alternativtitel">
            <button class="mr-1 p-1 border-none bg-slate-50" title="Alternativtitel hinzufügen" data-fun="addAltTitle" data-binding="true" data-type="aufkleber">
                <?=Icon::$iconEditText?>
            </button>
            <?php if ($stickerCollection->getAufkleber()->getActiveStatus()): ?>
                <button class="mr-1 p-1 border-none bg-slate-50" title="Artikel ausblenden/ einblenden" data-binding="true" data-fun="toggleProductVisibility" data-type="aufkleber">
                <?=Icon::$iconVisible?>
            </button>
            <?php else: ?>
                <button class="mr-1 p-1 border-none bg-slate-50" title="Artikel ausblenden/ einblenden" data-binding="true" data-fun="toggleProductVisibility" data-type="aufkleber">
                <?=Icon::$iconInvisible?>
            <?php endif; ?> 
            <button class="mr-1 p-1 border-none bg-slate-50" title="Produkte verknüpfen" data-binding="true" data-fun="shortcutProduct" data-type="aufkleber">
                <?=Icon::$iconConnectTo?>
            </button>
            <button class="mr-1 p-1 border-none bg-slate-50" title="Kategorien auswählen" data-binding="true" data-fun="chooseCategory">
                <?=Icon::$iconCategory?>
            </button>
            <button class="infoButton" data-info="8">i</button>
        </p>
        <div class="">
            Status:
            <?php if ($stickerCollection->getAufkleber()->isInShop()):?>
                <a title="Aufkleber ist im Shop" target="_blank" href="<?=$stickerCollection->getAufkleber()->getShopLink()?>">
                    <?=Icon::$iconInShop?>
                </a>
            <?php else: ?>
                <?=Icon::$iconNotInShop?>
            <?php endif; ?>
        </div>
        <div>
            <span>Aufkleber Plott</span>
            <span class="right">
                <label class="switch">
                    <input type="checkbox" id="plotted" <?=$stickerCollection->getAufkleber()->getIsPlotted() == 1 ? "checked" : ""?> data-variable="true">
                    <span class="slider round" data-binding="true" data-fun="toggleCheckbox"></span>
                </label>
            </span>
        </div>
        <div>
            <span>kurzfristiger Aufkleber</span>
            <span class="right">
                <label class="switch">
                    <input type="checkbox" id="short" <?=$stickerCollection->getAufkleber()->getIsShortTimeSticker() == 1 ? "checked" : ""?> data-variable="true">
                    <span class="slider round" data-binding="true" data-fun="toggleCheckbox"></span>
                </label>
            </span>
        </div>
        <div>
            <span>langfristiger Aufkleber</span>
            <span class="right">
                <label class="switch">
                    <input type="checkbox" id="long" <?=$stickerCollection->getAufkleber()->getIsLongTimeSticker() == 1 ? "checked" : ""?> data-variable="true">
                    <span class="slider round" data-binding="true" data-fun="toggleCheckbox"></span>
                </label>
            </span>
        </div>
        <div>
            <span>mehrteilig</span>
            <span class="right">
                <label class="switch">
                    <input type="checkbox" id="multi" <?=$stickerCollection->getAufkleber()->getIsMultipart() == 1 ? "checked" : ""?> data-variable="true">
                    <span class="slider round" data-binding="true" data-fun="toggleCheckbox"></span>
                </label>
            </span>
        </div>
        <div class="mt-2">
            <div>
                <h4>Kurzbeschreibung</h4>
                <?=insertTemplate("classes/project/modules/sticker/views/chatGPTstickerView.php", ["type" => "aufkleber", "text" => "short", "gpt" => $chatGPTConnection])?>
            </div>
            <textarea class="data-input" data-fun="productDescription" data-target="aufkleber" data-type="short" data-write="true"><?=$stickerCollection->getAufkleber()->getDescriptionShort()?></textarea>
            <div>
                <h4>Beschreibung</h4>
                <?=insertTemplate("classes/project/modules/sticker/views/chatGPTstickerView.php", ["type" => "aufkleber", "text" => "long", "gpt" => $chatGPTConnection])?>
            </div>
            <textarea class="data-input" data-fun="productDescription" data-target="aufkleber" data-type= "long" data-write="true"><?=$stickerCollection->getAufkleber()->getDescription()?></textarea>
        </div>
        <?=insertTemplate("classes/project/modules/sticker/views/stickerImageView.php", ["images" => $stickerImage->getAufkleberImages(), "imageCategory" => "aufkleber"])?>
        <button class="transferBtn btn-primary w-full" id="transferAufkleber" data-binding="true" <?=$stickerCollection->getAufkleber()->getIsPlotted() == 1 ? "" : "disabled"?>>Aufkleber übertragen</button>
    </section>
    <section class="defCont">
        <p class="pHeading">Wandtattoo
            <input class="titleInput hidden" value="<?=$stickerCollection->getWandtattoo()->getAltTitle()?>" data-write="true" data-type="wandtattoo" data-fun="changeAltTitle" placeholder="Alternativtitel">
            <button class="mr-1 p-1 border-none bg-slate-50" title="Alternativtitel hinzufügen" data-fun="addAltTitle" data-binding="true" data-type="wandtattoo">
                <?=Icon::$iconEditText?>
            </button>
            <?php if ($stickerCollection->getWandtattoo()->getActiveStatus()): ?>
                <button class="mr-1 p-1 border-none bg-slate-50" title="Artikel ausblenden/ einblenden" data-binding="true" data-fun="toggleProductVisibility" data-type="wandtattoo">
                <?=Icon::$iconVisible?>
            </button>
            <?php else: ?>
                <button class="mr-1 p-1 border-none bg-slate-50" title="Artikel ausblenden/ einblenden" data-binding="true" data-fun="toggleProductVisibility" data-type="wandtattoo">
                <?=Icon::$iconInvisible?>
            <?php endif; ?>
            <button class="mr-1 p-1 border-none bg-slate-50" title="Produkte verknüpfen" data-binding="true" data-fun="shortcutProduct" data-type="wandtattoo">
                <?=Icon::$iconConnectTo?>
            </button>
            <button class="mr-1 p-1 border-none bg-slate-50" title="Kategorien auswählen" data-binding="true" data-fun="chooseCategory">
                <?=Icon::$iconCategory?>
            </button>
            <button class="infoButton" data-info="9">i</button>
        </p>
        <div class="">
            Status: 
            <?php if ($stickerCollection->getWandtattoo()->isInShop()):?>
                <a title="Wandtattoo ist im Shop" target="_blank" href="<?=$stickerCollection->getWandtattoo()->getShopLink()?>">
                    <?=Icon::$iconInShop?>
                </a>
            <?php else: ?>
                <?=Icon::$iconNotInShop?>
            <?php endif; ?>
        </div>
        <div>
            <span>Wandtattoo</span>
            <span class="right">
                <label class="switch">
                    <input type="checkbox" id="wandtattoo" <?=$stickerCollection->getWandtattoo()->getIsWalldecal() == 1 ? "checked" : ""?> data-variable="true">
                    <span class="slider round" id="wandtattooClick" data-binding="true"></span>
                </label>
            </span>
        </div>
        <div class="mt-2">
            <div>
                <h4>Kurzbeschreibung</h4>
                <?=insertTemplate("classes/project/modules/sticker/views/chatGPTstickerView.php", ["type" => "wandtattoo", "text" => "short", "gpt" => $chatGPTConnection])?>
            </div>
            <textarea class="data-input" data-fun="" data-target="wandtattoo" data-type="short" data-write="true"><?=$stickerCollection->getWandtattoo()->getDescriptionShort()?></textarea>
            <div>
                <h4>Beschreibung</h4>
                <?=insertTemplate("classes/project/modules/sticker/views/chatGPTstickerView.php", ["type" => "wandtattoo", "text" => "long", "gpt" => $chatGPTConnection])?>
            </div>
            <textarea class="data-input" data-fun="productDescription" data-target="wandtattoo" data-type="long" data-write="true"><?=$stickerCollection->getWandtattoo()->getDescription()?></textarea>
        </div>
        <?=insertTemplate("classes/project/modules/sticker/views/stickerImageView.php", ["images" => $stickerImage->getWandtattooImages(), "imageCategory" => "wandtattoo"])?>
        <button class="transferBtn btn-primary w-full" id="transferWandtattoo" data-binding="true" <?=$stickerCollection->getWandtattoo()->getIsWalldecal() == 1 ? "" : "disabled"?>>Wandtattoo übertragen</button>
    </section>
    <section class="defCont">
        <p class="pHeading">Textil
            <input class="titleInput hidden" value="<?=$stickerCollection->getTextil()->getAltTitle()?>" data-write="true" data-type="textil" data-fun="changeAltTitle" placeholder="Alternativtitel">
            <button class="mr-1 p-1 border-none bg-slate-50" title="Alternativtitel hinzufügen" data-fun="addAltTitle" data-binding="true" data-type="textil">
                <?=Icon::$iconEditText?>
            </button>
            <?php if ($stickerCollection->getTextil()->getActiveStatus()): ?>
                <button class="mr-1 p-1 border-none bg-slate-50" title="Artikel ausblenden/ einblenden" data-binding="true" data-fun="toggleProductVisibility" data-type="textil">
                <?=Icon::$iconVisible?>
            </button>
            <?php else: ?>
                <button class="mr-1 p-1 border-none bg-slate-50" title="Artikel ausblenden/ einblenden" data-binding="true" data-fun="toggleProductVisibility" data-type="textil">
                <?=Icon::$iconInvisible?>
            <?php endif; ?> 
            <button class="mr-1 p-1 border-none bg-slate-50" title="Produkte verknüpfen" data-binding="true" data-fun="shortcutProduct" data-type="textil">
                <?=Icon::$iconConnectTo?>
            </button>
            <button class="mr-1 p-1 border-none bg-slate-50" title="Kategorien auswählen" data-binding="true" data-fun="chooseCategory">
                <?=Icon::$iconCategory?>
            </button>
            <button class="infoButton" data-info="10">i</button>
        </p>
        <div class="">
            Status: 
            <?php if ($stickerCollection->getTextil()->isInShop()):?>
                <a title="Textil ist im Shop" target="_blank" href="<?=$stickerCollection->getTextil()->getShopLink()?>">
                    <?=Icon::$iconInShop?>
                </a>
            <?php else: ?>
                <?=Icon::$iconNotInShop?>
            <?php endif; ?>
        </div>
        <div>
            <span>Textil</span>
            <span class="right">
                <label class="switch">
                    <input type="checkbox" id="textil" <?=$stickerCollection->getTextil()->getIsShirtcollection() == 1 ? "checked" : ""?> data-variable="true">
                    <span class="slider round" id="textilClick" data-binding="true"></span>
                </label>
            </span>
        </div>
        <div>
            <span>Einfärbbar</span>
            <span class="right">
                <label class="switch">
                    <input type="checkbox" <?=$stickerCollection->getTextil()->getIsColorable() == 1 ? "checked" : ""?> data-variable="true">
                    <span class="slider round" id="makeColorable" data-binding="true"></span>
                </label>
            </span>
        </div>
        <div>
            <span>Personalisierbar</span>
            <span class="right">
                <label class="switch">
                    <input type="checkbox" <?=$stickerCollection->getTextil()->getIsCustomizable() == 1 ? "checked" : ""?> data-variable="true">
                    <span class="slider round" id="makeCustomizable" data-binding="true"></span>
                </label>
            </span>
        </div>
        <div>
            <span>Im Konfigurator anzeigen</span>
            <span class="right">
                <label class="switch">
                    <input type="checkbox" <?=$stickerCollection->getTextil()->getIsForConfigurator() == 1 ? "checked" : ""?> data-variable="true">
                    <span class="slider round" id="makeForConfig" data-binding="true"></span>
                </label>
            </span>
        </div>
        <div>
            <object id="svgContainer" data="<?=$stickerImage->getSVGIfExists($stickerCollection->getTextil()->getIsColorable())?>" type="image/svg+xml" class="innerDefCont imageMovableContainer"></object>
            <?php if ($stickerCollection->getTextil()->getIsColorable() == 1): ?>
            <?php foreach ($stickerCollection->getTextil()->textilColors as $color):?>
            <button class="colorBtn" style="background:<?=$color["hexCol"]?>" title="<?=$color["name"]?>" data-binding="true" data-fun="changeColor" data-color="<?=$color["hexCol"]?>"></button>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div>
            <p>Preiskategorie</p>
            <div>
                <label class="block bg-white rounded-3xl p-1 pl-2 mt-1" title="Die Klebefux Standardkategorie für Textilmotive">
                    <input type="radio" name="textilPriceClass" value="58" data-binding="true" data-fun="changePreiskategorie" <?=$priceType == 58 ? "checked" : "" ?>>
                    <span>Klebefux Standard (20,52 €)</span>
                </label>
                <label class="block bg-white rounded-3xl p-1 pl-2 mt-1" title="Die Klebefux Premiumkategorie für Textilmotive">
                    <input type="radio" name="textilPriceClass" value="57" data-binding="true" data-fun="changePreiskategorie" <?=$priceType == 57 ? "checked" : "" ?>>
                    <span>Klebefux Plus (23,59 €)</span>
                </label>
                <label class="block bg-white rounded-3xl p-1 pl-2 mt-1" title="Die Gwandlaus Textilkategorie für einfache Motive">
                    <input type="radio" name="textilPriceClass" value="59" data-binding="true" data-fun="changePreiskategorie" <?=$priceType == 59 ? "checked" : "" ?>>
                    <span>Gwandlaus Minus (30,78 €)</span>
                </label>
                <label class="block bg-white rounded-3xl p-1 pl-2 mt-1" title="Die Gwandlaus Standardkategorie für Textilmotive">
                    <input type="radio" name="textilPriceClass" value="60" data-binding="true" data-fun="changePreiskategorie" <?=$priceType == 60 ? "checked" : "" ?>>
                    <span>Gwandlaus Standard (33,85 €)</span>
                </label>
            </div>
        </div>
        <div class="mt-2">
            <div>
                <h4>Kurzbeschreibung</h4>
                <?=insertTemplate("classes/project/modules/sticker/views/chatGPTstickerView.php", ["type" => "textil", "text" => "short", "gpt" => $chatGPTConnection])?>
            </div>
            <textarea class="data-input" data-fun="productDescription" data-target="textil" data-type="short" data-write="true"><?=$stickerCollection->getTextil()->getDescriptionShort()?></textarea>
            <div>
                <h4>Beschreibung</h4>
                <?=insertTemplate("classes/project/modules/sticker/views/chatGPTstickerView.php", ["type" => "textil", "text" => "long", "gpt" => $chatGPTConnection])?>
            </div>
            <textarea class="data-input" data-fun="productDescription" data-target="textil" data-type="long" data-write="true"><?=$stickerCollection->getTextil()->getDescription()?></textarea>
        </div>
        <?=insertTemplate("classes/project/modules/sticker/views/stickerImageView.php", ["images" => $stickerImage->getTextilImages(), "imageCategory" => "textil"])?>
        <button class="transferBtn btn-primary w-full" id="transferTextil" data-binding="true" <?=$stickerCollection->getTextil()->getIsShirtcollection() == 1 ? "" : "disabled"?>>Textil übertragen</button>
    </section>
</div>
<div class="defCont">
    <h2 class="mb-2 font-bold">Größen</h2>
    <div id="sizeTableWrapper">
        <?=$stickerCollection->getAufkleber()->getSizeTable()?>
    </div>
    <div class="grid grid-cols-2 mt-2">
        <div class="innerDefCont">
            <p class="font-semibold">Neue Breite hinzufügen</p>
            <label>
                <p>Breite</p>
                <input type="text" id="newWidth" class="w-48 rounded-md p-2">
            </label>
            <label>
                <p>Preis</p>
                <input type="text" id="newPrice" class="w-48 rounded-md p-2">
            </label>
            <button class="btn-primary block mt-2" data-binding="true" data-fun="addNewWidth">Hinzufügen</button>
        </div>
        <div class="innerDefCont">
            <p class="font-semibold">Aufkleberpreisklasse</p>
            <div>
                <label for="price1">
                    <input id="price1" type="radio" name="priceClass" <?=$stickerCollection->getAufkleber()->getPriceClass() == 0 ? "checked" : ""?>>
                    <span>Preisklasse 1 (günstiger)</span>
                </label>
            </div>
            <div>
                <label for="price2">
                    <input id="price2" type="radio" name="priceClass" <?=$stickerCollection->getAufkleber()->getPriceClass() == 1 ? "checked" : ""?>>
                    <span>Preisklasse 2 (teurer)</span>
                </label>
            </div>
        </div>
    </div>
</div>
<div class="defCont">
    <h2 class="font-semibold">Tags<button class="infoButton" data-info="3">i</button></h2>
    <div class="mt-2">
        <?=$stickerTagManager->getTagsHTML()?>
        <input type="text" class="tagInput" maxlength="32" id="tagInput">
    </div>
    <p class="italic">Nicht erlaubt sind folgende Zeichen: !<;>;?=+#"°{}_$%.</p>
    <button id="loadSynonyms" class="btn-primary">Mehr Synonnyme laden</button>
    <button id="showTaggroupManager" class="btn-primary">Taggruppen</button>
</div>
<div class="defCont">
    <h2 class="font-semibold">Weitere Infos</h2>
    <div class="mt-2">
        <span>Wurde der Artikel neu überarbeitet?<button class="infoButton" data-info="4">i</button></span>
        <span class="float-right">
            <label class="switch">
                <input type="checkbox" id="revised" <?=$stickerCollection->getIsRevised() == 1 ? "checked" : ""?> data-variable="true">
                <span class="slider round" data-binding="true"></span>
            </label>
        </span>
    </div>
    <p class="mt-2">Speicherort:<button class="infoButton" data-info="5">i</button></p>
    <div class="directoryContainer mt-2">
        <input id="dirInput" class="data-input directoryName" data-fun="speicherort" data-write="true" value="<?=$stickerCollection->getDirectory()?>">
        <button class="directoryIcon" data-binding="true" data-fun="copyToClipboard">
            <?=Icon::$iconDirectory?>
        </button>
    </div>
    <p class="mt-2">Zusätzliche Infos und Notizen:<button class="infoButton" data-info="6">i</button></p>
    <textarea class="data-input mt-2" data-fun="additionalInfo" data-write="true"><?=$stickerCollection->getAdditionalInfo()?></textarea>
    <button class="btn-primary mt-2" data-fun="transferAll" data-binding="true">Alles erstellen/ aktualisieren</button>
</div>
<div class="defCont">
    <h2 class="font-semibold mb-2">Produktexport</h2>
    <form>
        <div class="exportContainer inline-block bg-white rounded-3xl p-3">
            Nach Facebook exportieren
            <span class="right">
                <label class="switch">
                    <input type="checkbox" <?=$stickerCollection->getExportStatus("facebook") ? "checked" : ""?>>
                    <span class="slider round" data-binding="true" data-value="facebook" data-fun="exportToggle"></span>
                </label>
            </span>
        </div>
        <br>
        <div class="exportContainer inline-block bg-white rounded-3xl p-3">
            Nach Google exportieren
            <span class="right">
                <label class="switch">
                    <input type="checkbox" <?=$stickerCollection->getExportStatus("google") ? "checked" : ""?>>
                    <span class="slider round" data-binding="true" data-value="google" data-fun="exportToggle"></span>
                </label>
            </span>
        </div>
        <br>
        <div class="exportContainer inline-block bg-white rounded-3xl p-3">
            Nach Amazon exportieren
            <span class="right">
                <label class="switch">
                    <input type="checkbox" <?=$stickerCollection->getExportStatus("amazon") ? "checked" : ""?>>
                    <span class="slider round" data-binding="true" data-value="amazon" data-fun="exportToggle"></span>
                </label>
            </span>
        </div>
        <br>
        <div class="exportContainer inline-block bg-white rounded-3xl p-3">
            Nach Etsy exportieren
            <span class="right">
                <label class="switch">
                    <input type="checkbox" <?=$stickerCollection->getExportStatus("etsy") ? "checked" : ""?>>
                    <span class="slider round" data-fun="exportToggle" data-binding="true" data-value="etsy"></span>
                </label>
            </span>
        </div>
        <br>
        <div class="exportContainer inline-block bg-white rounded-3xl p-3">
            Nach eBay exportieren
            <span class="right">
                <label class="switch">
                    <input type="checkbox" <?=$stickerCollection->getExportStatus("ebay") ? "checked" : ""?>>
                    <span class="slider round" data-fun="exportToggle" data-binding="true" data-value="ebay"></span>
                </label>
            </span>
        </div>
        <br>
        <div class="exportContainer inline-block bg-white rounded-3xl p-3">
            Nach Pinterest exportieren
            <span class="right">
                <label class="switch">
                    <input type="checkbox" <?=$stickerCollection->getExportStatus("pinterest") ? "checked" : ""?>>
                    <span class="slider round" data-fun="exportToggle" data-binding="true" data-value="pinterest"></span>
                </label>
            </span>
        </div>
    </form>
</div>
<div class="defCont">
    <h2 class="font-semibold mb-2">Statistiken</h2>
    <!-- TODO: Statistiken von Google Analytics und Google Shopping, sowie von Google SearchConsole und shopintern einbinden -->
</div>
<div class="defCont">
    <h2 class="font-semibold mb-2">Changelog</h2>
    <details>
        <?=$stickerChangelog->getTable()?>
    </details>
</div>
<div>
    <span id="showUploadProgress"></span>
</div>
<template id="icon-file">
    <?=Icon::$iconFile?>
</template>
<template id="icon-corel">
    <?=Icon::iconCorel()?>
</template>
<template id="icon-letterplot">
    <?=Icon::iconLetterPlott()?>
</template>
<template id="icon-visible">
    <?=Icon::$iconVisible?>
</template>
<template id="icon-invisible">
    <?=Icon::$iconInvisible?>
</template>
<?php endif; ?>