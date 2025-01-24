<?php

use Classes\Link;
use Classes\Project\Icon;

use Classes\Project\Modules\Sticker\StickerImage;
use Classes\Project\Modules\Sticker\StickerCollection;
use Classes\Project\Modules\Sticker\StickerTagManager;
use Classes\Project\Modules\Sticker\StickerChangelog;
use Classes\Project\Modules\Sticker\ChatGPTConnection;

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
}

if ($id == 0): ?>
    <a href="<?=Link::getPageLink("sticker-overview")?>">Zur Motivübersicht</a>
    <script>
        window.location.href = "<?=Link::getPageLink("sticker-overview")?>";
    </script>
<?php else: ?>
<div class="w-full">
<div class="cont1">
    <div class="defCont">
        <h2 class="font-semibold">Motiv <input id="name" class="titleInput inline bg-inherit border-b border-b-gray-600 pl-1" value="<?=$stickerCollection->getName();?>" title="Faceboook hat ein internes Limit für die Titellänge von 65 Zeichen">
            <?php if ($stickerCollection->getIsMarked() == "0"): ?>
                <span data-binding="true" data-fun="bookmark" data-status="unmarked" class="inline" title="Motiv markieren">
                    <?=Icon::get("iconBookmark", 18, 18, ["inline", "cursor-pointer"])?>
                </span>
            <?php else: ?>
                <span data-binding="true" data-fun="bookmark" data-status="marked" class="bookmarked inline cursor-pointer" title="Motiv markieren">
                    <?=Icon::get("iconUnbookmark", 18, 18, ["inline", "cursor-pointer"])?>
                </span>
            <?php endif; ?>
        </h2>
        <p class="mt-2 ml-2">Artikelnummer: <span id="motivId" data-variable="true"><?=$id?></span></p>
        <p class="ml-2">Erstellt am <input type="date" class="rounded-xs px-2" id="creationDate" value="<?=$stickerCollection->getCreationDate()?>"></p>
        <button class="btn-primary mt-4" data-fun="transferAll" data-binding="true">Alles erstellen/ aktualisieren</button>
    </div>
</div>
<div class="defCont hidden">
    <p>Dateien und Bilder</p>
    <?=insertTemplate("classes/Project/Modules/Sticker/Views/stickerFileView.php", ["images" => $stickerImage->getGeneralImages(), "files" => $stickerImage->getFiles()])?>
</div>
<div class="cont2">
    <section class="defCont">
        <p class="pHeading">Aufkleber
            <input class="titleInput hidden" value="<?=$stickerCollection->getAufkleber()->getAltTitle()?>" data-write="true" data-type="aufkleber" data-fun="changeAltTitle" placeholder="Alternativtitel">
            <button class="mr-1 p-1 border-none bg-slate-50" title="Alternativtitel hinzufügen" data-fun="addAltTitle" data-binding="true" data-type="aufkleber">
                <?=Icon::getDefault("iconEditText")?>
            </button>
            <?php if ($stickerCollection->getAufkleber()->getActiveStatus()): ?>
                <button class="mr-1 p-1 border-none bg-slate-50" title="Artikel ausblenden/ einblenden" data-binding="true" data-fun="toggleProductVisibility" data-type="aufkleber">
                <?=Icon::getDefault("iconVisible")?>
            </button>
            <?php else: ?>
                <button class="mr-1 p-1 border-none bg-slate-50" title="Artikel ausblenden/ einblenden" data-binding="true" data-fun="toggleProductVisibility" data-type="aufkleber">
                <?=Icon::getDefault("iconInvisible")?>
            <?php endif; ?> 
            <button class="mr-1 p-1 border-none bg-slate-50" title="Produkte verknüpfen" data-binding="true" data-fun="shortcutProduct" data-type="aufkleber">
                <?=Icon::getDefault("iconConnectTo")?>
            </button>
            <button class="mr-1 p-1 border-none bg-slate-50" title="Kategorien auswählen" data-binding="true" data-fun="chooseCategory">
                <?=Icon::getDefault("iconCategory")?>
            </button>
            <button class="infoButton ml-1" data-info="8">i</button>
        </p>
        <div class="">
            Status:
            <?php if ($stickerCollection->getAufkleber()->isInShop()):?>
                <a title="Aufkleber ist im Shop" target="_blank" href="<?=$stickerCollection->getAufkleber()->getShopLink()?>">
                    <?=Icon::getDefault("iconInShop")?>
                </a>
            <?php else: ?>
                <a title="Aufkleber ist nicht im Shop" href="#" onclick="return false;">
                    <?=Icon::getDefault("iconNotInShop")?>
                </a>
            <?php endif; ?>
        </div>
        <div class="hover:underline mt-1">
            <span>Aufkleber Plott</span>
            <span class="right">
                <label class="switch">
                    <input type="checkbox" id="plotted" <?=$stickerCollection->getAufkleber()->getIsPlotted() == 1 ? "checked" : ""?> data-variable="true">
                    <span class="slider round" data-binding="true" data-fun="toggleCheckbox"></span>
                </label>
            </span>
        </div>
        <div class="hover:underline mt-1">
            <span>kurzfristiger Aufkleber</span>
            <span class="right">
                <label class="switch">
                    <input type="checkbox" id="short" <?=$stickerCollection->getAufkleber()->getIsShortTimeSticker() == 1 ? "checked" : ""?> data-variable="true">
                    <span class="slider round" data-binding="true" data-fun="toggleCheckbox"></span>
                </label>
            </span>
        </div>
        <div class="hover:underline mt-1">
            <span>langfristiger Aufkleber</span>
            <span class="right">
                <label class="switch">
                    <input type="checkbox" id="long" <?=$stickerCollection->getAufkleber()->getIsLongTimeSticker() == 1 ? "checked" : ""?> data-variable="true">
                    <span class="slider round" data-binding="true" data-fun="toggleCheckbox"></span>
                </label>
            </span>
        </div>
        <div class="hover:underline mt-1">
            <span>mehrteilig</span>
            <span class="right">
                <label class="switch">
                    <input type="checkbox" id="multi" <?=$stickerCollection->getAufkleber()->getIsMultipart() == 1 ? "checked" : ""?> data-variable="true">
                    <span class="slider round" data-binding="true" data-fun="toggleCheckbox"></span>
                </label>
            </span>
        </div>
        <div class="">
            <details>
                <summary>Farbauswahl</summary>
                <div class="ml-2">
                    <!-- Farbauswahl einbauen -->
                    <label>
                        <input type="checkbox" checked>Standardfarbauswahl verwenden
                    </label>
                </div>
            </details>
        </div>
        <div class="my-2">
            <div class="my-2 flex">
                <p>Kurzbeschreibung</p>
                <?=insertTemplate("classes/Project/Modules/Sticker/Views/chatGPTstickerView.php", ["type" => "aufkleber", "text" => "short", "gpt" => $chatGPTConnection])?>
            </div>
            <textarea class="data-input" data-fun="productDescription" data-target="aufkleber" data-type="short" data-write="true"><?=$stickerCollection->getAufkleber()->getDescriptionShort()?></textarea>
            <div class="my-2 flex">
                <p>Beschreibung</p>
                <?=insertTemplate("classes/Project/Modules/Sticker/Views/chatGPTstickerView.php", ["type" => "aufkleber", "text" => "long", "gpt" => $chatGPTConnection])?>
            </div>
            <textarea class="data-input" data-fun="productDescription" data-target="aufkleber" data-type= "long" data-write="true"><?=$stickerCollection->getAufkleber()->getDescription()?></textarea>
        </div>
        <?=insertTemplate("classes/Project/Modules/Sticker/Views/stickerImageView.php", ["images" => $stickerImage->getAufkleberImages(), "imageCategory" => "aufkleber"])?>
        <button class="transferBtn btn-primary w-full" id="transferAufkleber" data-binding="true" <?=$stickerCollection->getAufkleber()->getIsPlotted() == 1 ? "" : "disabled"?>>Aufkleber übertragen</button>
    </section>
    <section class="defCont">
        <p class="pHeading">Wandtattoo
            <input class="titleInput hidden" value="<?=$stickerCollection->getWandtattoo()->getAltTitle()?>" data-write="true" data-type="wandtattoo" data-fun="changeAltTitle" placeholder="Alternativtitel">
            <button class="mr-1 p-1 border-none bg-slate-50" title="Alternativtitel hinzufügen" data-fun="addAltTitle" data-binding="true" data-type="wandtattoo">
                <?=Icon::getDefault("iconEditText")?>
            </button>
            <?php if ($stickerCollection->getWandtattoo()->getActiveStatus()): ?>
                <button class="mr-1 p-1 border-none bg-slate-50" title="Artikel ausblenden/ einblenden" data-binding="true" data-fun="toggleProductVisibility" data-type="wandtattoo">
                <?=Icon::getDefault("iconVisible")?>
            </button>
            <?php else: ?>
                <button class="mr-1 p-1 border-none bg-slate-50" title="Artikel ausblenden/ einblenden" data-binding="true" data-fun="toggleProductVisibility" data-type="wandtattoo">
                <?=Icon::getDefault("iconInvisible")?>
            <?php endif; ?>
            <button class="mr-1 p-1 border-none bg-slate-50" title="Produkte verknüpfen" data-binding="true" data-fun="shortcutProduct" data-type="wandtattoo">
                <?=Icon::getDefault("iconConnectTo")?>
            </button>
            <button class="mr-1 p-1 border-none bg-slate-50" title="Kategorien auswählen" data-binding="true" data-fun="chooseCategory">
                <?=Icon::getDefault("iconCategory")?>
            </button>
            <button class="infoButton ml-1" data-info="9">i</button>
        </p>
        <div>
            Status: 
            <?php if ($stickerCollection->getWandtattoo()->isInShop()):?>
                <a title="Wandtattoo ist im Shop" target="_blank" href="<?=$stickerCollection->getWandtattoo()->getShopLink()?>">
                    <?=Icon::getDefault("iconInShop")?>
                </a>
            <?php else: ?>
                <a title="Wandtattoo ist nicht im Shop" href="#" onclick="return false;">
                    <?=Icon::getDefault("iconNotInShop")?>
                </a>
            <?php endif; ?>
        </div>
        <div class="hover:underline">
            <span>Wandtattoo</span>
            <span class="right">
                <label class="switch">
                    <input type="checkbox" id="wandtattoo" <?=$stickerCollection->getWandtattoo()->getIsWalldecal() == 1 ? "checked" : ""?> data-variable="true">
                    <span class="slider round" id="wandtattooClick" data-binding="true"></span>
                </label>
            </span>
        </div>
        <div class="my-2">
            <div class="my-2 flex">
                <p>Kurzbeschreibung</p>
                <?=insertTemplate("classes/Project/Modules/Sticker/Views/chatGPTstickerView.php", ["type" => "wandtattoo", "text" => "short", "gpt" => $chatGPTConnection])?>
            </div>
            <textarea class="data-input" data-fun="productDescription" data-target="wandtattoo" data-type="short" data-write="true"><?=$stickerCollection->getWandtattoo()->getDescriptionShort()?></textarea>
            <div class="my-2 flex">
                <p>Beschreibung</p>
                <?=insertTemplate("classes/Project/Modules/Sticker/Views/chatGPTstickerView.php", ["type" => "wandtattoo", "text" => "long", "gpt" => $chatGPTConnection])?>
            </div>
            <textarea class="data-input" data-fun="productDescription" data-target="wandtattoo" data-type="long" data-write="true"><?=$stickerCollection->getWandtattoo()->getDescription()?></textarea>
        </div>
        <?=insertTemplate("classes/Project/Modules/Sticker/Views/stickerImageView.php", ["images" => $stickerImage->getWandtattooImages(), "imageCategory" => "wandtattoo"])?>
        <button class="transferBtn btn-primary w-full" id="transferWandtattoo" data-binding="true" <?=$stickerCollection->getWandtattoo()->getIsWalldecal() == 1 ? "" : "disabled"?>>Wandtattoo übertragen</button>
    </section>
    <section class="defCont">
        <p class="pHeading">Textil
            <input class="titleInput hidden" value="<?=$stickerCollection->getTextil()->getAltTitle()?>" data-write="true" data-type="textil" data-fun="changeAltTitle" placeholder="Alternativtitel">
            <button class="mr-1 p-1 border-none bg-slate-50" title="Alternativtitel hinzufügen" data-fun="addAltTitle" data-binding="true" data-type="textil">
                <?=Icon::getDefault("iconEditText")?>
            </button>
            <?php if ($stickerCollection->getTextil()->getActiveStatus()): ?>
                <button class="mr-1 p-1 border-none bg-slate-50" title="Artikel ausblenden/ einblenden" data-binding="true" data-fun="toggleProductVisibility" data-type="textil">
                <?=Icon::getDefault("iconVisible")?>
            </button>
            <?php else: ?>
                <button class="mr-1 p-1 border-none bg-slate-50" title="Artikel ausblenden/ einblenden" data-binding="true" data-fun="toggleProductVisibility" data-type="textil">
                <?=Icon::getDefault("iconInvisible")?>
            <?php endif; ?> 
            <button class="mr-1 p-1 border-none bg-slate-50" title="Produkte verknüpfen" data-binding="true" data-fun="shortcutProduct" data-type="textil">
                <?=Icon::getDefault("iconConnectTo")?>
            </button>
            <button class="mr-1 p-1 border-none bg-slate-50" title="Kategorien auswählen" data-binding="true" data-fun="chooseCategory">
                <?=Icon::getDefault("iconCategory")?>
            </button>
            <button class="infoButton ml-1" data-info="10">i</button>
        </p>
        <div class="">
            Status: 
            <?php if ($stickerCollection->getTextil()->isInShop()):?>
                <a title="Textil ist im Shop" target="_blank" href="<?=$stickerCollection->getTextil()->getShopLink()?>">
                    <?=Icon::getDefault("iconInShop")?>
                </a>
            <?php else: ?>
                <a title="Textil ist nicht im Shop" href="#" onclick="return false;">
                    <?=Icon::getDefault("iconNotInShop")?>
                </a>
            <?php endif; ?>
        </div>
        <div class="hover:underline mt-1">
            <span>Textil</span>
            <span class="right">
                <label class="switch">
                    <input type="checkbox" id="textil" <?=$stickerCollection->getTextil()->getIsShirtcollection() == 1 ? "checked" : ""?> data-variable="true">
                    <span class="slider round" id="textilClick" data-binding="true"></span>
                </label>
            </span>
        </div>
        <div class="hover:underline mt-1">
            <span>Einfärbbar</span>
            <span class="right">
                <label class="switch">
                    <input type="checkbox" <?=$stickerCollection->getTextil()->getIsColorable() == 1 ? "checked" : ""?> data-variable="true">
                    <span class="slider round" id="makeColorable" data-binding="true"></span>
                </label>
            </span>
        </div>
        <div class="hover:underline mt-1">
            <span>Personalisierbar</span>
            <span class="right">
                <label class="switch">
                    <input type="checkbox" <?=$stickerCollection->getTextil()->getIsCustomizable() == 1 ? "checked" : ""?> data-variable="true">
                    <span class="slider round" id="makeCustomizable" data-binding="true"></span>
                </label>
            </span>
        </div>
        <div class="hover:underline mt-1">
            <span>Im Konfigurator anzeigen</span>
            <span class="right">
                <label class="switch">
                    <input type="checkbox" <?=$stickerCollection->getTextil()->getIsForConfigurator() == 1 ? "checked" : ""?> data-variable="true">
                    <span class="slider round" id="makeForConfig" data-binding="true"></span>
                </label>
            </span>
        </div>
        <div class="grid grid-cols-2 mt-1">
            <div class="relative bg-gray-200 rounded-lg w-full h-24 cursor-pointer" id="svgDropZone">
                <p class="absolute inset-0 flex items-center justify-center select-none font-bold text-gray-500">SVG hochladen</p>
            </div>
            <object id="svgContainer" class="w-full h-24" data="<?=$stickerImage->getSVGIfExists($stickerCollection->getTextil()->getIsColorable())?>"></object>
        </div>
        <div class="mt-1">
            <?php if ($stickerCollection->getTextil()->getIsColorable() == 1): ?>
                <?php foreach ($stickerCollection->getTextil()->textilColors as $color):?>
                <button class="colorBtn" style="background:<?=$color["hexCol"]?>" title="<?=$color["name"]?>" data-binding="true" data-fun="changeColor" data-color="<?=$color["hexCol"]?>"></button>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="mb-2">
            <div class="my-2 flex">
                <p>Kurzbeschreibung</p>
                <?=insertTemplate("classes/Project/Modules/Sticker/Views/chatGPTstickerView.php", ["type" => "textil", "text" => "short", "gpt" => $chatGPTConnection])?>
            </div>
            <textarea class="data-input" data-fun="productDescription" data-target="textil" data-type="short" data-write="true"><?=$stickerCollection->getTextil()->getDescriptionShort()?></textarea>
            <div class="my-2 flex">
                <p>Beschreibung</p>
                <?=insertTemplate("classes/Project/Modules/Sticker/Views/chatGPTstickerView.php", ["type" => "textil", "text" => "long", "gpt" => $chatGPTConnection])?>
            </div>
            <textarea class="data-input" data-fun="productDescription" data-target="textil" data-type="long" data-write="true"><?=$stickerCollection->getTextil()->getDescription()?></textarea>
        </div>
        <?=insertTemplate("classes/Project/Modules/Sticker/Views/stickerImageView.php", ["images" => $stickerImage->getTextilImages(), "imageCategory" => "textil"])?>
        <button class="transferBtn btn-primary w-full hidden" id="transferTextil" data-binding="true" <?=$stickerCollection->getTextil()->getIsShirtcollection() == 1 ? "" : "disabled"?>>Textil übertragen</button>
    </section>
</div>
<div class="defCont">
    <h2 class="mb-2 font-bold">Größen</h2>
    <div id="sizeTableWrapper">
        <table>
            <thead>
                <tr>
                    <th>Breite in [cm]</th>
                    <th>Höhe in [cm]</th>
                    <th>Preis in [€]</th>
                    <th>Kosten in [€]</th>
                    <th>Aktion</th>
                </tr>
            </thead>
            <tbody id="sizeTableAnchor"></tbody>
        </table>
    </div>
    <div class="grid grid-cols-2 mt-2">
        <div class="innerDefCont bg-gray-200">
            <p class="font-semibold" id="sizeActionTextAnchor">Neue Breite hinzufügen</p>
            <label>
                <p id="sizeInputTextAnchor">Breite in [cm]</p>
                <input type="number" id="sizeInputAnchor" class="w-48 rounded-md p-2">
            </label>
            <label>
                <p>Preis in [€]</p>
                <input type="number" id="sizePriceAnchor" class="w-48 rounded-md p-2">
            </label>
            <div id="sizeBtnAddAnchor">
                <button class="btn-primary-new block mt-2" id="sizeBtnAdd">Hinzufügen</button>
            </div>
            <div class="hidden" id="sizeBtnEditAnchor">
                <button class="btn-primary-new block mt-2" id="sizeBtnEdit">Speichern</button>
                <button class="btn-cancel block ml-2 mt-2" id="sizeBtnCancel">Abbrechen</button>
            </div>
        </div>
        <div class="innerDefCont bg-gray-200">
            <p class="font-semibold">Aufkleberpreisklasse</p>
            <div>
                <label>
                    <input id="sizesPrice1" type="radio" name="priceClass">
                    <span>Preisklasse 1 (günstiger)</span>
                </label>
            </div>
            <div>
                <label>
                    <input id="sizesPrice2" type="radio" name="priceClass">
                    <span>Preisklasse 2 (teurer)</span>
                </label>
            </div>
        </div>
    </div>
</div>
<div class="defCont">
    <h2 class="font-semibold">Textilien</h2>
    <div class="mt-2">
        <div>
            <?php foreach ($stickerCollection->getTextil()->getProducts() as $product):?>
                <div class="productContainer inline-block bg-white rounded-3xl p-3 hover:underline">
                    <div class="flex">
                        <span class="flex-1"><?=$product['name']?></span>
                        <span class="">
                            Aktiv
                            <label class="ml-1 switch">
                                <input type="checkbox" class="textiles-switches" data-id="<?=$product['id']?>" <?=$product['activated'] ? 'checked' : '' ?>>
                                <span class="slider round"></span>
                            </label>
                        </span>
                    </div>
                    <div>
                        <input type="number" step="0.01" class="textiles-prices" data-id="<?=$product['id']?>" value="<?=$product['price']?>">
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<div class="defCont">
    <h2 class="font-semibold">Tags<button class="infoButton ml-1" data-info="3">i</button></h2>
    <div class="mt-2">
        <?=$stickerTagManager->getTagsHTML()?>
    </div>
    <div class="my-2">
        <input type="text" class="input-primary-new" maxlength="32" id="tagInput" placeholder="Tag eingeben">
        <button type="button" class="btn-primary-new" id="addNewTag" title="Mit Hashtag oder Button hinzufügen">Hinzufügen</button>
        <p class="italic">Nicht erlaubt sind folgende Zeichen: !<;>;?=+#"°{}_$%.</p>
    </div>
    <button id="loadSynonyms" class="btn-primary-new">Mehr Synonnyme laden</button>
    <button id="showTaggroupManager" class="btn-primary-new">Taggruppen</button>
</div>
<div class="defCont">
    <h2 class="font-semibold">Weitere Infos</h2>
    <div class="mt-2">
        <span>Wurde der Artikel neu überarbeitet?<button class="infoButton ml-1" data-info="4">i</button></span>
        <span class="float-right">
            <label class="switch">
                <input type="checkbox" id="revised" <?=$stickerCollection->getIsRevised() == 1 ? "checked" : ""?> data-variable="true">
                <span class="slider round" data-binding="true"></span>
            </label>
        </span>
    </div>
    <p class="mt-2">Speicherort:<button class="infoButton ml-1" data-info="5">i</button></p>
    <div class="directoryContainer mt-2">
        <input id="dirInput" class="data-input directoryName" data-fun="speicherort" data-write="true" value="<?=$stickerCollection->getDirectory()?>">
        <button class="directoryIcon" data-binding="true" data-fun="copyToClipboard">
            <?=Icon::getDefault("iconDirectory")?>
        </button>
    </div>
    <p class="mt-2">Zusätzliche Infos und Notizen:<button class="infoButton ml-1" data-info="6">i</button></p>
    <textarea class="data-input mt-2" data-fun="additionalInfo" data-write="true"><?=$stickerCollection->getAdditionalInfo()?></textarea>
    <button class="btn-primary mt-2" data-fun="transferAll" data-binding="true">Alles erstellen/ aktualisieren</button>
</div>
<div class="defCont">
    <h2 class="font-semibold mb-2">Produktexport</h2>
    <form>
        <div class="exportContainer inline-block bg-white rounded-3xl p-3 hover:underline">
            Nach Facebook exportieren
            <span class="right">
                <label class="switch">
                    <input type="checkbox" <?=$stickerCollection->getExportStatus("facebook") ? "checked" : ""?>>
                    <span class="slider round" data-binding="true" data-value="facebook" data-fun="exportToggle"></span>
                </label>
            </span>
        </div>
        <br>
        <div class="exportContainer inline-block bg-white rounded-3xl p-3 hover:underline">
            Nach Google exportieren
            <span class="right">
                <label class="switch">
                    <input type="checkbox" <?=$stickerCollection->getExportStatus("google") ? "checked" : ""?>>
                    <span class="slider round" data-binding="true" data-value="google" data-fun="exportToggle"></span>
                </label>
            </span>
        </div>
        <br>
        <div class="exportContainer inline-block bg-white rounded-3xl p-3 hover:underline">
            Nach Amazon exportieren
            <span class="right">
                <label class="switch">
                    <input type="checkbox" <?=$stickerCollection->getExportStatus("amazon") ? "checked" : ""?>>
                    <span class="slider round" data-binding="true" data-value="amazon" data-fun="exportToggle"></span>
                </label>
            </span>
        </div>
        <br>
        <div class="exportContainer inline-block bg-white rounded-3xl p-3 hover:underline">
            Nach Etsy exportieren
            <span class="right">
                <label class="switch">
                    <input type="checkbox" <?=$stickerCollection->getExportStatus("etsy") ? "checked" : ""?>>
                    <span class="slider round" data-fun="exportToggle" data-binding="true" data-value="etsy"></span>
                </label>
            </span>
        </div>
        <br>
        <div class="exportContainer inline-block bg-white rounded-3xl p-3 hover:underline">
            Nach eBay exportieren
            <span class="right">
                <label class="switch">
                    <input type="checkbox" <?=$stickerCollection->getExportStatus("ebay") ? "checked" : ""?>>
                    <span class="slider round" data-fun="exportToggle" data-binding="true" data-value="ebay"></span>
                </label>
            </span>
        </div>
        <br>
        <div class="exportContainer inline-block bg-white rounded-3xl p-3 hover:underline">
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
    <div>
        <label>
            Von
            <input type="date" id="statsStart" class="input-primary">
        </label>
        <label>
            Bis
            <input type="date" id="statsEnd" class="input-primary">
        </label>
    </div>
    <div class="mt-2">
        <h3>Google SearchConsole</h3>
        <div id="google-searchconsole">

        </div>
    </div>
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
    <?=Icon::getDefault("iconFile")?>
</template>
<template id="icon-corel">
    <?=Icon::iconCorel()?>
</template>
<template id="icon-letterplot">
    <?=Icon::iconLetterPlott()?>
</template>
<template id="icon-visible">
    <?=Icon::getDefault("iconVisible")?>
</template>
<template id="icon-invisible">
    <?=Icon::getDefault("iconInvisible")?>
</template>
<template id="templateImageRow">
    <tr>
        <td><img class="imgPreview cursor-pointer" data-file-id="" src="" alt=""></td>
        <td><input class="px-2 bg-inherit w-32 imageDescription" type="text" maxlength="125" placeholder="Beschreibung" data-write="true" data-fun="updateImageDescription" data-file-id=""></td>
        <td></td>
        <td>
            <button class="p-1 mr-1 actionButton deleteImage deleteImage" title="Löschen" data-file-id="" data-binding="true" data-fun="deleteImage"><?=Icon::getDefault("iconDelete")?></button>
            <button class="p-1 mr-1 actionButton moveRow" title="Verschieben" onmousedown="moveInit(event)" onmouseup="moveRemove(event)" data-file-id=""><?=Icon::getDefault("iconMove")?></button>
        </td>
    </tr>
</template>
<?php endif; ?>
</div>