<?php
require_once('classes/project/modules/sticker/StickerTagManager.php');
require_once('classes/project/modules/sticker/StickerImage.php');
require_once('classes/project/modules/sticker/StickerCollection.php');

$id = 0;

$stickerCollection = null;
$stickerTagManager = null;
$stickerChangelog = null;
$stickerImage = null;

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    $stickerImage = new StickerImage2($id);
    $stickerCollection = new StickerCollection($id);
    $stickerTagManager = new StickerTagManager($id, $stickerCollection->getName());
    $stickerChangelog = new StickerChangelog($id);
}

if ($id != 0):
?>
    <script src="<?=Link::getResourcesShortLink("sticker/productConnector.js", "js")?>"></script>
    <script src="<?=Link::getResourcesShortLink("sticker/tagManager.js", "js")?>"></script>
    <script src="<?=Link::getResourcesShortLink("sticker/imageManager.js", "js")?>"></script>
    <?=$stickerCollection->checkProductErrorStatus() ? $stickerCollection->getErrorMessage() : ""?>
    <div class="defCont cont1">
        <div>
            <h2>Motiv <input id="name" class="titleInput" value="<?=$stickerCollection->getName();?>">
                <?php if ($stickerCollection->getIsMarked() == "0"): ?>
                <span><svg onclick="bookmark(event)" style="width:24px; height:24px; vertical-align:middle;" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M12,15.39L8.24,17.66L9.23,13.38L5.91,10.5L10.29,10.13L12,6.09L13.71,10.13L18.09,10.5L14.77,13.38L15.76,17.66M22,9.24L14.81,8.63L12,2L9.19,8.63L2,9.24L7.45,13.97L5.82,21L12,17.27L18.18,21L16.54,13.97L22,9.24Z" />
                </svg></span>
                <?php else: ?>
                <span><svg onclick="unbookmark(event)" class="bookmarked" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M12,17.27L18.18,21L16.54,13.97L22,9.24L14.81,8.62L12,2L9.19,8.62L2,9.24L7.45,13.97L5.82,21L12,17.27Z" />
                </svg></span>
                <?php endif; ?>
            </h2>
            <p>Artikelnummer: <span id="motivId" data-variable="true"><?=$id?></span></p>
            <p>Erstellt am <input type="date" value="<?=$stickerCollection->getCreationDate()?>" onchange="changeDate(event)"></p>
            <div class="shopStatus">
                <div class="shopStatusIcon">
                   <?=Icon::$iconAddInShop?>
                </div> 
                <button class="newButton" data-id="4" data-fun="transferAll" data-binding="true">Alles erstellen/ aktualisieren</button>
            </div>
        </div>
        <div ondragover="">
            <p>Weitere Dateien:</p>
            <?=insertTemplate("classes/project/modules/sticker/views/stickerFileView.php", ["images" => $stickerImage->getUnspecificImages(), "files" => $stickerImage->getFiles()])?>
            <div id="delete-menu">
                <div class="item" onclick="deleteImage(-1)">
                    <svg style="width:24px;height:24px" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M19,4H15.5L14.5,3H9.5L8.5,4H5V6H19M6,19A2,2 0 0,0 8,21H16A2,2 0 0,0 18,19V7H6V19Z" />
                    </svg>
                    <span>Löschen</span>
                </div>
            </div>
        </div>
    </div>
    <div class="cont2">
        <section class="defCont">
            <p class="pHeading">Aufkleber
                <input class="titleInput invisible" value="<?=$stickerCollection->getAufkleber()->getAltTitle()?>" data-write="true" data-type="aufkleber" data-fun="changeAltTitle">
                <button title="Alternativtitel hinzufügen" data-fun="addAltTitle" data-binding="true" data-type="aufkleber">
                    <?=Icon::$iconEditText?>
                </button>
                <button class="infoButton" data-info="8">i</button>
                <button title="Artikel ausblenden/ einblenden" data-binding="true" data-fun="toggleProductVisibility" data-type="aufkleber">
                    <?=Icon::$iconVisible?>
                </button>
                <button title="Produkte verknüpfen" data-binding="true" data-fun="shortcutProduct" data-type="aufkleber">
                    <?=Icon::$iconConnectTo?>
                </button>
                <button title="Kategorien auswählen" data-binding="true" data-fun="chooseCategory">
                    <?=Icon::$iconCategory?>
                </button>
            </p>
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
            <div>
                <h4>Kurzbeschreibung <button class="iconGenerate" title="Textvorschlag erstellen" data-binding="true" data-fun="textGeneration"><?=Icon::$iconGenerate?></button></h4>
                <textarea class="data-input" data-fun="productDescription" data-target="1" data-type="short" data-write="true"><?=$stickerCollection->getAufkleber()->getDescriptionShort()?></textarea>
                <h4>Beschreibung</h4>
                <textarea class="data-input" data-fun="productDescription" data-target="1" data-type= "long" data-write="true"><?=$stickerCollection->getAufkleber()->getDescription()?></textarea>
            </div>
            <div class="shopStatus">
                <div class="shopStatusIcon" title="<?=$stickerCollection->getAufkleber()->isInShop() == 1 ? "Aufkleber ist im Shop" : "Aufkleber ist nicht im Shop" ?>">
                <?php if ($stickerCollection->getAufkleber()->isInShop()):?>
                <a title="Aufkleber ist im Shop" target="_blank" href="<?=$stickerCollection->getAufkleber()->getShopLink()?>">
                <?=Icon::$iconInShop?>
                </a>
                <?php else: ?>
                <?=Icon::$iconNotInShop?>
                <?php endif; ?>
                </div>
                <button class="transferBtn" id="transferAufkleber" data-binding="true" <?=$stickerCollection->getAufkleber()->getIsPlotted() == 1 ? "" : "disabled"?>>Aufkleber übertragen</button>
            </div>
            <?=insertTemplate("classes/project/modules/sticker/views/stickerImageView.php", ["images" => $stickerImage->getAufkleberImages(), "imageCategory" => "aufkleber"])?>
        </section>
        <section class="defCont">
            <p class="pHeading">Wandtattoo
                <input class="titleInput invisible" value="<?=$stickerCollection->getWandtattoo()->getAltTitle()?>?>" data-write="true" data-type="wandtattoo" data-fun="changeAltTitle">
                <button title="Alternativtitel hinzufügen" data-fun="addAltTitle" data-binding="true" data-type="wandtattoo">
                    <?=Icon::$iconEditText?>
                </button>
                <button class="infoButton" data-info="9">i</button>
                <button title="Artikel ausblenden/ einblenden" data-binding="true" data-fun="toggleProductVisibility" data-type="wandtattoo">
                    <?=Icon::$iconVisible?>
                </button>
                <button title="Produkte verknüpfen" data-binding="true" data-fun="shortcutProduct" data-type="wandtattoo">
                    <?=Icon::$iconConnectTo?>
                </button>
                <button title="Kategorien auswählen" data-binding="true" data-fun="chooseCategory">
                    <?=Icon::$iconCategory?>
                </button>
            </p>
            <div>
                <span>Wandtattoo</span>
                <span class="right">
                    <label class="switch">
                        <input type="checkbox" id="wandtattoo" <?=$stickerCollection->getWandtattoo()->getIsWalldecal() == 1 ? "checked" : ""?> data-variable="true">
                        <span class="slider round" id="wandtattooClick" data-binding="true"></span>
                    </label>
                </span>
            </div>
            <div>
                <h4>Kurzbeschreibung</h4>
                <textarea class="data-input" data-fun="productDescription" data-target="2" data-type="short" data-write="true"><?=$stickerCollection->getWandtattoo()->getDescriptionShort()?></textarea>
                <h4>Beschreibung</h4>
                <textarea class="data-input" data-fun="productDescription" data-target="2" data-type="long" data-write="true"><?=$stickerCollection->getWandtattoo()->getDescription()?></textarea>
            </div>
            <div class="shopStatus">
                <div class="shopStatusIcon" title="<?=$stickerCollection->getWandtattoo()->isInShop() == 1 ? "Wandtattoo ist im Shop" : "Wandtattoo ist nicht im Shop" ?>">
                <?php if ($stickerCollection->getWandtattoo()->isInShop()):?>
                <a title="Wandtattoo ist im Shop" target="_blank" href="<?=$stickerCollection->getWandtattoo()->getShopLink()?>">
                <?=Icon::$iconInShop?>
                </a>
                <?php else: ?>
                <?=Icon::$iconNotInShop?>
                <?php endif; ?>
                </div>
                <button class="transferBtn" id="transferWandtattoo" data-binding="true">Wandtattoo übertragen</button>
            </div>
            <?=insertTemplate("classes/project/modules/sticker/views/stickerImageView.php", ["images" => $stickerImage->getWandtattooImages(), "imageCategory" => "wandtattoo"])?>
        </section>
        <section class="defCont">
            <p class="pHeading">Textil
                <input class="titleInput invisible" value="<?=$stickerCollection->getTextil()->getAltTitle()?>" data-write="true" data-type="textil" data-fun="changeAltTitle">
                <button title="Alternativtitel hinzufügen" data-fun="addAltTitle" data-binding="true" data-type="textil">
                    <?=Icon::$iconEditText?>
                </button>
                <button class="infoButton" data-info="10">i</button>
                <button title="Artikel ausblenden/ einblenden" data-binding="true" data-fun="toggleProductVisibility" data-type="textil">
                    <?=Icon::$iconVisible?>
                </button>
                <button title="Produkte verknüpfen" data-binding="true" data-fun="shortcutProduct" data-type="textil">
                    <?=Icon::$iconConnectTo?>
                </button>
                <button title="Kategorien auswählen" data-binding="true" data-fun="chooseCategory">
                    <?=Icon::$iconCategory?>
                </button>
            </p>
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
                        <input type="checkbox" id="textil" <?=$stickerCollection->getTextil()->getIsColorable() == 1 ? "checked" : ""?> data-variable="true">
                        <span class="slider round" id="makeColorable" data-binding="true"></span>
                    </label>
                </span>
            </div>
            <div>
                <object id="svgContainer" data="<?=$stickerImage->getSVGIfExists()?>" type="image/svg+xml"></object>
                <br>
                <?php if ($stickerCollection->getTextil()->getIsColorable() == 1): ?>
                <?php foreach ($stickerCollection->getTextil()->textilColors as $color):?>
                <button class="colorBtn" style="background:<?=$color["hexCol"]?>" title="<?=$color["name"]?>" onclick="changeColor(event)" data-color="<?=$color["hexCol"]?>"></button>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div>
                <span>Preiskategorie:<br>
                    <input class="postenInput" id="preiskategorie">
                    <span id="preiskategorie_dropdown">▼</span>
                    <div class="selectReplacer" id="selectReplacerPreiskategorie">
                        <p class="optionReplacer" onclick="changePreiskategorie(event)" data-default-price="20,52" data-kategorie-id="58" title="Die Klebefux Standardkategorie für Textilmotive">Klebefux Standard</p>
                        <p class="optionReplacer" onclick="changePreiskategorie(event)" data-kategorie-id="57" data-default-price="23,59" title="Die Klebefux Premiumkategorie für Textilmotive">Klebefux Plus</p>
                        <p class="optionReplacer" onclick="changePreiskategorie(event)" data-kategorie-id="59" data-default-price="30,78" title="Die Gwandlaus Textilkategorie für einfache Motive">Gwandlaus Minus</p>
                        <p class="optionReplacer" onclick="changePreiskategorie(event)" data-kategorie-id="60" data-default-price="33,85" title="Die Gwandlaus Standardkategorie für Textilmotive">Gwandlaus Standard</p>
                    </div>
                </span>
                <span style="margin-left: 7px" id="showPrice"><?=$stickerCollection->getTextil()->getPriceTextilFormatted()?></span>
            </div>
            <div>
                <h4>Kurzbeschreibung</h4>
                <textarea class="data-input" data-fun="productDescription" data-target="3" data-type="short" data-write="true"><?=$stickerCollection->getTextil()->getDescriptionShort()?></textarea>
                <h4>Beschreibung</h4>
                <textarea class="data-input" data-fun="productDescription" data-target="3" data-type="long" data-write="true"><?=$stickerCollection->getTextil()->getDescription()?></textarea>
            </div>
            <div class="shopStatus">
                <div class="shopStatusIcon" title="<?=$stickerCollection->getTextil()->isInShop() == 1 ? "Textil ist im Shop" : "Textil ist nicht im Shop" ?>">
                <?php if ($stickerCollection->getTextil()->isInShop()):?>
                <a title="Textil ist im Shop" target="_blank" href="<?=$stickerCollection->getTextil()->getShopLink()?>">
                <?=Icon::$iconInShop?>
                </a>
                <?php else: ?>
                <?=Icon::$iconNotInShop?>
                <?php endif; ?>
                </div>
                <button class="transferBtn" id="transferTextil" data-binding="true">Textil übertragen</button>
            </div>
            <?=insertTemplate("classes/project/modules/sticker/views/stickerImageView.php", ["images" => $stickerImage->getTextilImages(), "imageCategory" => "textil"])?>
        </section>
    </div>
    <div class="defCont align-center">
        <h2 style="text-align: left;">Größen</h2>
        <div id="sizeTableWrapper"><?=$stickerCollection->getAufkleber()->getSizeTable()?></div>
        <div>
            <p>Aufkleberpreisklasse</p>
            <div>
                <label for="price1">Preisklasse 1 (günstiger)</label>
                <input id="price1" type="radio" name="priceClass" <?=$stickerCollection->getAufkleber()->getPriceClass() == 0 ? "checked" : ""?> onclick="changePriceclass(event)">
            </div>
            <div>
                <label for="price2">Preisklasse 2 (teurer)</label>
                <input id="price2" type="radio" name="priceClass" <?=$stickerCollection->getAufkleber()->getPriceClass() == 1 ? "checked" : ""?> onclick="changePriceclass(event)">
            </div>
        </div>
        <div id="previewSizeText"><?=$stickerCollection->getAufkleber()->getSizeSummary()?></div>
    </div>
    <div class="defCont">
        <h2>Tags<button class="infoButton" data-info="3">i</button></h2>
        <div>
            <?=$stickerTagManager->getTagsHTML()?>
            <input type="text" class="tagInput" maxlength="32" onkeydown="addTag(event)">
        </div>
        <p>Nicht erlaubt sind folgende Zeichen: !<;>;?=+#"°{}_$%.</p>
        <button onclick="loadTags()">Mehr Synonnyme laden</button>
        <button onclick="showTaggroupManager()">Taggruppen</button>
    </div>
    <div class="defCont">
        <h2>Weitere Infos</h2>
        <div class="revised">
            <span>Wurde der Artikel neu überarbeitet?<button class="infoButton" data-info="4">i</button></span>
            <span class="right">
                <label class="switch">
                    <input type="checkbox" id="revised" <?=$stickerCollection->getIsRevised() == 1 ? "checked" : ""?> data-variable="true">
                    <span class="slider round" id="revisedClick" data-binding="true"></span>
                </label>
            </span>
        </div>
        <p>Speicherort:<button class="infoButton" data-info="5">i</button></p>
        <div class="directoryContainer">
            <input id="dirInput" class="data-input directoryName" data-fun="speicherort" data-write="true" value="<?=$stickerCollection->getDirectory()?>">
            <button class="directoryIcon" onclick="copyToClipboard('dirInput')">
                <?=Icon::$iconDirectory?>
            </button>
        </div>
        <p>Zusätzliche Infos und Notizen:<button class="infoButton" data-info="6">i</button></p>
        <textarea class="data-input" data-fun="additionalInfo" data-write="true"><?=$stickerCollection->getAdditionalInfo()?></textarea>
        <div class="shopStatus">
            <div class="shopStatusIcon">
                <?=Icon::$iconAddInShop?>
            </div> 
            <button data-id="5" class="newButton marginTop30" data-fun="transferAll" data-binding="true">Alles erstellen/ aktualisieren</button>
        </div>
    </div>
    <div class="defCont">
        <h2>Produktexport</h2>
        <!-- TODO: facebook export überlegen, wo das angelegt wird und wie es funktioniert -->
        <button style="display: none" onclick="exportFacebook()">facebook export test</button>
        <form>
            <div class="exportContainer">
                Nach Facebook exportieren
                <span class="right">
                    <label class="switch">
                        <input type="checkbox" <?=$stickerCollection->getExportStatus("facebook") ? "checked" : ""?>>
                        <span class="slider round" id="revisedClick" data-binding="true" data-value="facebook" data-fun="exportToggle"></span>
                    </label>
                </span>
            </div>
            <div class="exportContainer">
                Nach Google exportieren
                <span class="right">
                    <label class="switch">
                        <input type="checkbox" <?=$stickerCollection->getExportStatus("google") ? "checked" : ""?>>
                        <span class="slider round" id="revisedClick" data-binding="true" data-value="google" data-fun="exportToggle"></span>
                    </label>
                </span>
            </div>
            <div class="exportContainer">
                Nach Amazon exportieren
                <span class="right">
                    <label class="switch">
                        <input type="checkbox" <?=$stickerCollection->getExportStatus("amazon") ? "checked" : ""?>>
                        <span class="slider round" id="revisedClick" data-binding="true" data-value="amazon" data-fun="exportToggle"></span>
                    </label>
                </span>
            </div>
            <div class="exportContainer">
                Nach Etsy exportieren
                <span class="right">
                    <label class="switch">
                        <input type="checkbox" <?=$stickerCollection->getExportStatus("etsy") ? "checked" : ""?>>
                        <span class="slider round" data-fun="exportToggle" data-binding="true" data-value="etsy"></span>
                    </label>
                </span>
            </div>
            <div class="exportContainer">
                Nach eBay exportieren
                <span class="right">
                    <label class="switch">
                        <input type="checkbox" <?=$stickerCollection->getExportStatus("ebay") ? "checked" : ""?>>
                        <span class="slider round" data-fun="exportToggle" data-binding="true" data-value="ebay"></span>
                    </label>
                </span>
            </div>
            <div class="exportContainer">
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
        <h2>Statistiken</h2>
        <!-- TODO: Statistiken von Google Analytics und Google Shopping, sowie von Google SearchConsole und shopintern einbinden -->
    </div>
    <div class="defCont">
        <h2>Changelog</h2>
        <?=$stickerChangelog->getTable()?>
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
<?php endif; ?>