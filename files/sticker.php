<?php

use Classes\Link;
use Classes\Project\Icon;

use Classes\Sticker\StickerImage;
use Classes\Sticker\StickerCollection;
use Classes\Sticker\StickerChangelog;
use Classes\Sticker\ChatGPTConnection;

use Classes\Project\TemplateController;

$id = 0;

$stickerCollection = null;
$stickerChangelog = null;
$stickerImage = null;
$chatGPTConnection = null;

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    $stickerImage = new StickerImage($id);
    $stickerCollection = new StickerCollection($id);
    $stickerChangelog = new StickerChangelog($id);
    $chatGPTConnection = new ChatGPTConnection($id);
}

if ($id == 0): ?>
    <a href="<?= Link::getPageLink("sticker-overview") ?>">Zur Motivübersicht</a>
    <script>
        window.location.href = "<?= Link::getPageLink("sticker-overview") ?>";
    </script>
<?php else: ?>
    <div class="w-full grid gap-2 grid-cols-3">
        <div class="defCont col-span-3">
            <h2 class="font-semibold inline-flex items-center">Motiv
                <input id="stickerName" data-write="true" data-input="true" class="input-primary w-96 ml-1" value="<?= $stickerCollection->getName(); ?>" title="Faceboook hat ein internes Limit für die Titellänge von 65 Zeichen">
                <?php if ($stickerCollection->getIsMarked() == "0"): ?>
                    <span data-binding="true" data-fun="bookmark" data-status="unmarked" class="inline ml-1" title="Motiv markieren">
                        <?= Icon::get("iconBookmark", 18, 18, ["inline", "cursor-pointer"]) ?>
                    </span>
                <?php else: ?>
                    <span data-binding="true" data-fun="bookmark" data-status="marked" class="bookmarked inline cursor-pointer ml-1" title="Motiv markieren">
                        <?= Icon::get("iconUnbookmark", 18, 18, ["inline", "cursor-pointer"]) ?>
                    </span>
                <?php endif; ?>
            </h2>
            <p class="ml-2 mt-2">Artikelnummer: <span id="motivId" data-variable="true"><?= $id ?></span></p>
            <p class="ml-2 mt-2">Erstellt am <input type="date" class="input-primary" id="creationDate" data-write="true" value="<?= $stickerCollection->getCreationDate() ?>"></p>
            <button class="btn-primary mt-4" data-fun="transferAll" data-binding="true">Alles erstellen/ aktualisieren</button>
        </div>
        <div class="defCont col-span-2 lg:col-span-1">
            <p class="font-bold">Aufkleber
                <?php if ($stickerCollection->getAufkleber()->getActiveStatus()): ?>
                    <button class="mx-1 btn-primary-small" title="Artikel ausblenden/ einblenden" data-binding="true" data-fun="toggleProductVisibility" data-type="aufkleber">
                        <?= Icon::getDefault("iconVisible") ?>
                    </button>
                <?php else: ?>
                    <button class="mr-1 btn-primary-small" title="Artikel ausblenden/ einblenden" data-binding="true" data-fun="toggleProductVisibility" data-type="aufkleber">
                        <?= Icon::getDefault("iconInvisible") ?>
                    <?php endif; ?>
                    <button class="mr-1 btn-primary-small" title="Produkte verknüpfen" data-binding="true" data-fun="shortcutProduct" data-type="aufkleber">
                        <?= Icon::getDefault("iconConnectTo") ?>
                    </button>
                    <button class="mr-1 btn-primary-small" title="Kategorien auswählen" data-binding="true" data-fun="chooseCategory">
                        <?= Icon::getDefault("iconCategory") ?>
                    </button>
                    <button class="infoButton ml-1" data-info="8">i</button>
            </p>
            <div class="mt-2">
                <p>Alternativtitel</p>
                <input class="input-primary mt-1" value="<?= $stickerCollection->getAufkleber()->getAltTitle() ?>" data-write="true" data-type="aufkleber" data-fun="changeAltTitle" placeholder="Alternativtitel">
            </div>
            <div class="mt-2">
                Status:
                <?php if ($stickerCollection->getAufkleber()->isInShop()): ?>
                    <a title="Aufkleber ist im Shop" target="_blank" href="<?= $stickerCollection->getAufkleber()->getShopLink() ?>">
                        <?= Icon::getDefault("iconInShop") ?>
                    </a>
                <?php else: ?>
                    <a title="Aufkleber ist nicht im Shop" href="#" onclick="return false;">
                        <?= Icon::getDefault("iconNotInShop") ?>
                    </a>
                <?php endif; ?>
            </div>
            <div class="mt-2">
                <?= TemplateController::getTemplate("inputSwitch", [
                    "id" => "plotted",
                    "name" => "Aufkleber Plott",
                    "value" => $stickerCollection->getAufkleber()->getIsPlotted() == 1 ? "checked" : "",
                    "binding" => "toggleCheckbox",
                ]); ?>
            </div>
            <div class="mt-2">
                <?= TemplateController::getTemplate("inputSwitch", [
                    "id" => "short",
                    "name" => "kurzfristiger Aufkleber",
                    "value" => $stickerCollection->getAufkleber()->getIsShortTimeSticker() == 1 ? "checked" : "",
                    "binding" => "toggleCheckbox",
                ]); ?>
            </div>
            <div class="mt-2">
                <?= TemplateController::getTemplate("inputSwitch", [
                    "id" => "short",
                    "name" => "langfristiger Aufkleber",
                    "value" => $stickerCollection->getAufkleber()->getIsLongTimeSticker() == 1 ? "checked" : "",
                    "binding" => "toggleCheckbox",
                ]); ?>
            </div>
            <div class="mt-2">
                <?= TemplateController::getTemplate("inputSwitch", [
                    "id" => "short",
                    "name" => "mehrteilig",
                    "value" => $stickerCollection->getAufkleber()->getIsMultipart() == 1 ? "checked" : "",
                    "binding" => "toggleCheckbox",
                ]); ?>
            </div>
            <div class="mt-2">
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
            <div class="mt-2">
                <div class="mt-2 flex">
                    <p>Kurzbeschreibung</p>
                    <?= TemplateController::getTemplate("sticker/chatGPTsticker", [
                        "type" => "aufkleber",
                        "text" => "short",
                        "gpt" => $chatGPTConnection
                    ]); ?>
                </div>
                <textarea class="input-primary w-full mt-1" data-fun="productDescription" data-target="aufkleber" data-type="short" data-write="true"><?= $stickerCollection->getAufkleber()->getDescriptionShort() ?></textarea>
                <div class="mt-2 flex">
                    <p>Beschreibung</p>
                    <?= TemplateController::getTemplate("sticker/chatGPTsticker", [
                        "type" => "aufkleber",
                        "text" => "long",
                        "gpt" => $chatGPTConnection
                    ]); ?>
                </div>
                <textarea class="input-primary w-full mt-1" data-fun="productDescription" data-target="aufkleber" data-type="long" data-write="true"><?= $stickerCollection->getAufkleber()->getDescription() ?></textarea>
            </div>
            <div class="mt-2">
                <?= TemplateController::getTemplate("sticker/stickerImage", [
                    "images" => $stickerImage->getAufkleberImages(),
                    "imageCategory" => "aufkleber",
                ]); ?>
            </div>
            <button class="transferBtn btn-primary w-full mt-2" id="transferAufkleber" data-binding="true" <?= $stickerCollection->getAufkleber()->getIsPlotted() == 1 ? "" : "disabled" ?>>Aufkleber übertragen</button>
        </div>

        <div class="defCont col-span-2 lg:col-span-1">
            <p class="font-bold">Wandtattoo
                <?php if ($stickerCollection->getWandtattoo()->getActiveStatus()): ?>
                    <button class="mx-1 btn-primary-small" title="Artikel ausblenden/ einblenden" data-binding="true" data-fun="toggleProductVisibility" data-type="wandtattoo">
                        <?= Icon::getDefault("iconVisible") ?>
                    </button>
                <?php else: ?>
                    <button class="mr-1 btn-primary-small" title="Artikel ausblenden/ einblenden" data-binding="true" data-fun="toggleProductVisibility" data-type="wandtattoo">
                        <?= Icon::getDefault("iconInvisible") ?>
                    <?php endif; ?>
                    <button class="mr-1 btn-primary-small" title="Produkte verknüpfen" data-binding="true" data-fun="shortcutProduct" data-type="wandtattoo">
                        <?= Icon::getDefault("iconConnectTo") ?>
                    </button>
                    <button class="mr-1 btn-primary-small" title="Kategorien auswählen" data-binding="true" data-fun="chooseCategory">
                        <?= Icon::getDefault("iconCategory") ?>
                    </button>
                    <button class="infoButton ml-1" data-info="9">i</button>
            </p>
            <div class="mt-2">
                <p>Alternativtitel</p>
                <input class="input-primary mt-1" value="<?= $stickerCollection->getWandtattoo()->getAltTitle() ?>" data-write="true" data-type="wandtattoo" data-fun="changeAltTitle" placeholder="Alternativtitel">
            </div>
            <div class="mt-2">
                Status:
                <?php if ($stickerCollection->getWandtattoo()->isInShop()): ?>
                    <a title="Wandtattoo ist im Shop" target="_blank" href="<?= $stickerCollection->getWandtattoo()->getShopLink() ?>">
                        <?= Icon::getDefault("iconInShop") ?>
                    </a>
                <?php else: ?>
                    <a title="Wandtattoo ist nicht im Shop" href="#" onclick="return false;">
                        <?= Icon::getDefault("iconNotInShop") ?>
                    </a>
                <?php endif; ?>
            </div>
            <div class="mt-2">
                <?= TemplateController::getTemplate("inputSwitch", [
                    "id" => "wandtattoo",
                    "name" => "Wandtattoo",
                    "value" => $stickerCollection->getWandtattoo()->getIsWalldecal() == 1 ? "checked" : "",
                    "binding" => "wandtattooClick",
                ]); ?>
            </div>
            <div class="mt-2">
                <div class="mt-2 flex">
                    <p>Kurzbeschreibung</p>
                    <?= TemplateController::getTemplate("sticker/chatGPTsticker", [
                        "type" => "wandtattoo",
                        "text" => "short",
                        "gpt" => $chatGPTConnection
                    ]); ?>
                </div>
                <textarea class="input-primary w-full mt-1" data-fun="productDescription" data-target="wandtattoo" data-type="short" data-write="true"><?= $stickerCollection->getWandtattoo()->getDescriptionShort() ?></textarea>
                <div class="mt-2 flex">
                    <p>Beschreibung</p>
                    <?= TemplateController::getTemplate("sticker/chatGPTsticker", [
                        "type" => "wandtattoo",
                        "text" => "long",
                        "gpt" => $chatGPTConnection
                    ]); ?>
                </div>
                <textarea class="input-primary w-full mt-1" data-fun="productDescription" data-target="wandtattoo" data-type="long" data-write="true"><?= $stickerCollection->getWandtattoo()->getDescription() ?></textarea>
            </div>
            <div class="mt-2">
                <?= TemplateController::getTemplate("sticker/stickerImage", [
                    "images" => $stickerImage->getWandtattooImages(),
                    "imageCategory" => "wandtattoo",
                ]); ?>
            </div>
            <button class="transferBtn btn-primary w-full mt-2" id="transferWandtattoo" data-binding="true" <?= $stickerCollection->getWandtattoo()->getIsWalldecal() == 1 ? "" : "disabled" ?>>Wandtattoo übertragen</button>
        </div>

        <div class="defCont col-span-2 lg:col-span-1">
            <p class="font-bold">Textil
                <?php if ($stickerCollection->getTextil()->getActiveStatus()): ?>
                    <button class="mx-1 btn-primary-small" title="Artikel ausblenden/ einblenden" data-binding="true" data-fun="toggleProductVisibility" data-type="textil">
                        <?= Icon::getDefault("iconVisible") ?>
                    </button>
                <?php else: ?>
                    <button class="mr-1 btn-primary-small" title="Artikel ausblenden/ einblenden" data-binding="true" data-fun="toggleProductVisibility" data-type="textil">
                        <?= Icon::getDefault("iconInvisible") ?>
                    <?php endif; ?>
                    <button class="mr-1 btn-primary-small" title="Produkte verknüpfen" data-binding="true" data-fun="shortcutProduct" data-type="textil">
                        <?= Icon::getDefault("iconConnectTo") ?>
                    </button>
                    <button class="mr-1 btn-primary-small" title="Kategorien auswählen" data-binding="true" data-fun="chooseCategory">
                        <?= Icon::getDefault("iconCategory") ?>
                    </button>
                    <button class="infoButton ml-1" data-info="10">i</button>
            </p>
            <div class="mt-2">
                <p>Alternativtitel</p>
                <input class="input-primary mt-1" value="<?= $stickerCollection->getTextil()->getAltTitle() ?>" data-write="true" data-type="wandtattoo" data-fun="changeAltTitle" placeholder="Alternativtitel">
            </div>
            <div class="mt-2">
                Status:
                <?php if ($stickerCollection->getTextil()->isInShop()): ?>
                    <a title="Textil ist im Shop" target="_blank" href="<?= $stickerCollection->getTextil()->getShopLink() ?>">
                        <?= Icon::getDefault("iconInShop") ?>
                    </a>
                <?php else: ?>
                    <a title="Textil ist nicht im Shop" href="#" onclick="return false;">
                        <?= Icon::getDefault("iconNotInShop") ?>
                    </a>
                <?php endif; ?>
            </div>
            <div class="mt-2">
                <?= TemplateController::getTemplate("inputSwitch", [
                    "id" => "textil",
                    "name" => "Textil",
                    "value" => $stickerCollection->getTextil()->getIsShirtcollection() == 1 ? "checked" : "",
                    "binding" => "textilClick",
                ]); ?>
            </div>
            <div class="mt-2">
                <?= TemplateController::getTemplate("inputSwitch", [
                    "id" => "makeColorable",
                    "name" => "Einfärbbar",
                    "value" => $stickerCollection->getTextil()->getIsColorable() == 1 ? "checked" : "",
                    "binding" => "makeColorable",
                ]); ?>
            </div>
            <div class="mt-2">
                <?= TemplateController::getTemplate("inputSwitch", [
                    "id" => "makeCustomizable",
                    "name" => "Personalisierbar",
                    "value" => $stickerCollection->getTextil()->getIsCustomizable() == 1 ? "checked" : "",
                    "binding" => "makeCustomizable",
                ]); ?>
            </div>
            <div class="mt-2">
                <?= TemplateController::getTemplate("inputSwitch", [
                    "id" => "makeForConfig",
                    "name" => "Im Konfigurator anzeigen",
                    "value" => $stickerCollection->getTextil()->getIsForConfigurator() == 1 ? "checked" : "",
                    "binding" => "makeForConfig",
                ]); ?>
            </div>
            <div class="mt-2">
                <?php if ($stickerCollection->getTextil()->getIsColorable() == 1): ?>
                    <?php foreach ($stickerCollection->getTextil()->textilColors as $color): ?>
                        <button class="colorBtn" style="background:<?= $color["hexCol"] ?>" title="<?= $color["name"] ?>" data-binding="true" data-fun="changeColor" data-color="<?= $color["hexCol"] ?>"></button>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="mt-2">
                <div class="mt-2 flex">
                    <p>Kurzbeschreibung</p>
                    <?= TemplateController::getTemplate("sticker/chatGPTsticker", [
                        "type" => "textil",
                        "text" => "short",
                        "gpt" => $chatGPTConnection
                    ]); ?>
                </div>
                <textarea class="input-primary w-full mt-1" data-fun="productDescription" data-target="textil" data-type="short" data-write="true"><?= $stickerCollection->getTextil()->getDescriptionShort() ?></textarea>
                <div class="mt-2 flex">
                    <p>Beschreibung</p>
                    <?= TemplateController::getTemplate("sticker/chatGPTsticker", [
                        "type" => "textil",
                        "text" => "long",
                        "gpt" => $chatGPTConnection
                    ]); ?>
                </div>
                <textarea class="input-primary w-full mt-1" data-fun="productDescription" data-target="textil" data-type="long" data-write="true"><?= $stickerCollection->getTextil()->getDescription() ?></textarea>
            </div>
            <div class="mt-2">
                <?= TemplateController::getTemplate("sticker/stickerImage", [
                    "images" => $stickerImage->getTextilImages(),
                    "imageCategory" => "textil",
                ]); ?>
            </div>
            <button class="transferBtn btn-primary w-full hidden mt-2" id="transferTextil" data-binding="true" <?= $stickerCollection->getTextil()->getIsShirtcollection() == 1 ? "" : "disabled" ?>>Textil übertragen</button>
        </div>

        <div class="defCont col-span-2">
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
                        <button class="btn-primary block mt-2" id="sizeBtnAdd">Hinzufügen</button>
                    </div>
                    <div class="hidden" id="sizeBtnEditAnchor">
                        <button class="btn-primary block mt-2" id="sizeBtnEdit">Speichern</button>
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
                    <?php foreach ($stickerCollection->getTextil()->getProducts() as $product): ?>
                        <div class="productContainer inline-block bg-white rounded-3xl p-3 hover:underline">
                            <div class="flex">
                                <span class="flex-1"><?= $product['name'] ?></span>
                                <span class="">
                                    Aktiv
                                    <label class="ml-1 switch">
                                        <input type="checkbox" class="textiles-switches" data-id="<?= $product['id'] ?>" <?= $product['activated'] ? 'checked' : '' ?>>
                                        <span class="slider round"></span>
                                    </label>
                                </span>
                            </div>
                            <div>
                                <input type="number" step="0.01" class="textiles-prices" data-id="<?= $product['id'] ?>" value="<?= $product['price'] ?>">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="defCont col-span-2">
            <h2 class="font-semibold">Tags<button class="infoButton ml-1" data-info="3">i</button></h2>
            <div class="mt-2" id="tagManager"></div>
            <div class="mt-2">
                <input type="text" class="input-primary" maxlength="32" id="tagInput" placeholder="Tag eingeben">
                <button type="button" class="btn-primary" id="addNewTag" data-binding="true" title="Mit Hashtag oder Button hinzufügen">Hinzufügen</button>
                <p class="mt-1.5">Nicht erlaubt sind folgende Zeichen: <span class="text-red-600 font-semibold">!<;>;?=+#"°{}_$%.</spa></p>
            </div>
            <div class="mt-2">
                <button id="loadSynonyms" data-binding="true" class="btn-primary">Mehr Synonnyme laden</button>
                <button id="showTaggroupManager" data-binding="true" class="btn-primary">Taggruppen</button>
            </div>
        </div>

        <div class="defCont">
            <h2 class="font-semibold">Weitere Infos</h2>
            <div class="mt-2 inline-flex items-center">
                <?= TemplateController::getTemplate("inputSwitch", [
                    "name" => "Motiv neu überarbeitet?",
                    "value" => $stickerCollection->getIsRevised() == 1 ? "checked" : "",
                    "binding" => "revised",
                ]); ?>
                <button class="infoButton ml-1" data-info="4">i</button>
            </div>
            <p class="mt-2">Speicherort:<button class="infoButton ml-1" data-info="5">i</button></p>
            <div class="mt-2">
                <input id="dirInput" class="input-primary w-full" data-write="true" value="<?= $stickerCollection->getDirectory() ?>">
            </div>
            <p class="mt-2">Zusätzliche Infos und Notizen:<button class="infoButton ml-1" data-info="6">i</button></p>
            <textarea class="input-primary mt-2 w-full" data-fun="additionalInfo" data-write="true"><?= $stickerCollection->getAdditionalInfo() ?></textarea>
        </div>

        <div class="defCont col-span-3">
            <h2 class="font-semibold mb-2">Produktexport</h2>
            <div class="mt-2">
                <?= TemplateController::getTemplate("inputSwitch", [
                    "id" => "facebook",
                    "name" => "Nach Facebook exportieren",
                    "value" => $stickerCollection->getExportStatus("facebook") ? "checked" : "",
                    "binding" => "exportToggle",
                ]); ?>
            </div>
            <div class="mt-2">
                <?= TemplateController::getTemplate("inputSwitch", [
                    "id" => "google",
                    "name" => "Nach Google exportieren",
                    "value" => $stickerCollection->getExportStatus("google") ? "checked" : "",
                    "binding" => "exportToggle",
                ]); ?>
            </div>
            <div class="mt-2">
                <?= TemplateController::getTemplate("inputSwitch", [
                    "id" => "amazon",
                    "name" => "Nach Amazon exportieren",
                    "value" => $stickerCollection->getExportStatus("amazon") ? "checked" : "",
                    "binding" => "exportToggle",
                ]); ?>
            </div>
            <div class="mt-2">
                <?= TemplateController::getTemplate("inputSwitch", [
                    "id" => "etsy",
                    "name" => "Nach Etsy exportieren",
                    "value" => $stickerCollection->getExportStatus("etsy") ? "checked" : "",
                    "binding" => "exportToggle",
                ]); ?>
            </div>
            <div class="mt-2">
                <?= TemplateController::getTemplate("inputSwitch", [
                    "id" => "eBay",
                    "name" => "Nach eBay exportieren",
                    "value" => $stickerCollection->getExportStatus("ebay") ? "checked" : "",
                    "binding" => "exportToggle",
                ]); ?>
            </div>
            <div class="mt-2">
                <?= TemplateController::getTemplate("inputSwitch", [
                    "id" => "pinterest",
                    "name" => "Nach Pinterest exportieren",
                    "value" => $stickerCollection->getExportStatus("pinterest") ? "checked" : "",
                    "binding" => "exportToggle",
                ]); ?>
            </div>
        </div>

        <div class="defCont col-span-2">
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
                <?= $stickerChangelog->getTable() ?>
            </details>
        </div>

        <template id="icon-file">
            <?= Icon::getDefault("iconFile") ?>
        </template>
        <template id="icon-corel">
            <?= Icon::iconCorel() ?>
        </template>
        <template id="icon-letterplot">
            <?= Icon::iconLetterPlott() ?>
        </template>
        <template id="icon-visible">
            <?= Icon::getDefault("iconVisible") ?>
        </template>
        <template id="icon-invisible">
            <?= Icon::getDefault("iconInvisible") ?>
        </template>
    </div>
<?php endif; ?>