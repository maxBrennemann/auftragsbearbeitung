<?php
    require_once('classes/project/StickerImage.php');
    require_once('classes/project/ProductCrawler.php');
    require_once('classes/project/StickerShopDBController.php');

    /* temp */
    require_once('classes/project/modules/sticker/StickerTagManager.php');
    require_once('classes/project/modules/sticker/StickerImage.php');

    $id = 0;
    $stickerImage = null;
    $stickerTagManager = null;
    $stickerChangelog = null;

    if (isset($_GET['id'])) {
        $id = (int) $_GET['id'];
        $stickerImage = new StickerImage($id);
        do {
            if ($stickerImage->getId() == 0) {
                $id = 0;
                break;
            }

            $images = $stickerImage->getImages();
            $mainImage = $images[0];
            $getDownloadResources = $stickerImage->getFiles();
        } while (0);

        $stickerTagManager = new StickerTagManager($id, $stickerImage->getName());
        $stickerChangelog = new StickerChangelog($id);
        $si = new StickerImage2($id);
    }

    if ($id != 0 && $stickerImage != null):
?>
    <div class="defCont cont1">
        <div>
            <h2>Motiv <input id="name" class="titleInput" value="<?=$stickerImage->getName();?>">
                <?php if ($stickerImage->data["is_marked"] == "0"): ?>
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
            <p>Erstellt am <input type="date" value="<?=$stickerImage->data["creation_date"]?>" onchange="changeDate(event)"></p>
            <div class="lds-ring productLoader" id="productLoader4"><div></div><div></div><div></div><div></div></div>
            <div class="shopStatus">
                <div class="shopStatusIcon">
                   <?=Icon::$iconAddInShop?>
                </div> 
                <button class="newButton" data-id="4" data-fun="transferAll" data-binding="true">Alles erstellen/ aktualisieren</button>
            </div>
        </div>
        <div>
            <p>Weitere Dateien:</p>
            <?=$getDownloadResources?>
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
                <input class="titleInput invisible" value="<?=$stickerImage->getAltTitle("aufkleber")?>" data-write="true" data-type="aufkleber" data-fun="changeAltTitle">
                <button class="addAltTitle" title="Alternativtitel hinzufügen" data-fun="addAltTitle" data-binding="true" data-type="aufkleber">
                    <?=Icon::$iconEditText?>
                </button>
                <button class="infoButton" data-info="8">i</button>
                <button class="addAltTitle" title="Artikel ausblenden/ einblenden" data-binding="true" data-fun="toggleProductVisibility" data-type="aufkleber">
                    <?=Icon::$iconVisible?>
                </button>
            </p>
            <div>
                <span>Aufkleber Plott</span>
                <span class="right">
                    <label class="switch">
                        <input type="checkbox" id="plotted" <?=$stickerImage->data["is_plotted"] == 1 ? "checked" : ""?> data-variable="true">
                        <span class="slider round" data-binding="true" data-fun="toggleCheckbox"></span>
                    </label>
                </span>
            </div>
            <div>
                <span>kurzfristiger Aufkleber</span>
                <span class="right">
                    <label class="switch">
                        <input type="checkbox" id="short" <?=$stickerImage->data["is_short_time"] == 1 ? "checked" : ""?> data-variable="true">
                        <span class="slider round" data-binding="true" data-fun="toggleCheckbox"></span>
                    </label>
                </span>
            </div>
            <div>
                <span>langfristiger Aufkleber</span>
                <span class="right">
                    <label class="switch">
                        <input type="checkbox" id="long" <?=$stickerImage->data["is_long_time"] == 1 ? "checked" : ""?> data-variable="true">
                        <span class="slider round" data-binding="true" data-fun="toggleCheckbox"></span>
                    </label>
                </span>
            </div>
            <div>
                <span>mehrteilig</span>
                <span class="right">
                    <label class="switch">
                        <input type="checkbox" id="multi" <?=$stickerImage->data["is_multipart"] == 1 ? "checked" : ""?> data-variable="true">
                        <span class="slider round" data-binding="true" data-fun="toggleCheckbox"></span>
                    </label>
                </span>
            </div>
            <div>
                <h4>Kurzbeschreibung</h4>
                <textarea class="data-input" data-fun="productDescription" data-target="1" data-type="short" data-write="true"><?=$stickerImage->descriptions[1]["short"]?></textarea>
                <h4>Beschreibung</h4>
                <textarea class="data-input" data-fun="productDescription" data-target="1" data-type= "long" data-write="true"><?=$stickerImage->descriptions[1]["long"]?></textarea>
            </div>
            <div class="shopStatus">
                <div class="shopStatusIcon" title="<?=$stickerImage->isInShop("aufkleber") == 1 ? "Aufkleber ist im Shop" : "Aufkleber ist nicht im Shop" ?>">
                <?php if ($stickerImage->isInShop("aufkleber")):?>
                <a title="Aufkleber ist im Shop" target="_blank" href="<?=$stickerImage->getShopProducts("aufkleber", "link")?>">
                <svg style="width:24px;height:24px" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M20 6H4V4H20V6M15.69 14H14V15.69C13.37 16.64 13 17.77 13 19C13 19.34 13.04 19.67 13.09 20H4V14H3V12L4 7H20L21 12V13.35C20.37 13.13 19.7 13 19 13C17.77 13 16.64 13.37 15.69 14M12 14H6V18H12V14M21.34 15.84L17.75 19.43L16.16 17.84L15 19L17.75 22L22.5 17.25L21.34 15.84Z" />
                </svg>
                </a>
                <?php else: ?>
                <svg style="width:24px;height:24px" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M4 4H20V6H4V4M15.46 16.88L16.88 15.46L19 17.59L21.12 15.47L22.54 16.88L20.41 19L22.54 21.12L21.12 22.54L19 20.41L16.88 22.54L15.46 21.12L17.59 19L15.47 16.88M4 7H20L21 12V13.34C20.33 13.09 19.62 12.96 18.91 12.96C17.71 12.96 16.54 13.33 15.54 14H14V15.53C13.3 16.53 12.92 17.73 12.92 18.95L13 20H4V14H3V12L4 7M6 14V18H12V14H6Z" />
                </svg>
                <?php endif; ?>
                </div>
                <button class="transferBtn" id="transferAufkleber" data-binding="true" <?=$stickerImage->data["is_plotted"] == 1 ? "" : "disabled"?>>Aufkleber übertragen</button>
            </div>
            <?=insertTemplate("classes/project/modules/sticker/views/stickerImageView.php", ["images" => $si->getAufkleberImages(), "imageCategory" => "aufkleber"])?>
        </section>
        <section class="defCont">
            <p class="pHeading">Wandtattoo
                <input class="titleInput invisible" value="<?=$stickerImage->getAltTitle("wandtattoo")?>" data-write="true" data-type="wandtattoo" data-fun="changeAltTitle">
                <button class="addAltTitle" title="Alternativtitel hinzufügen" data-fun="addAltTitle" data-binding="true" data-type="wandtattoo">
                    <?=Icon::$iconEditText?>
                </button>
                <button class="infoButton" data-info="9">i</button>
                <button class="addAltTitle" title="Artikel ausblenden/ einblenden" data-binding="true" data-fun="toggleProductVisibility" data-type="wandtattoo">
                    <?=Icon::$iconVisible?>
                </button>
            </p>
            <div>
                <span>Wandtattoo</span>
                <span class="right">
                    <label class="switch">
                        <input type="checkbox" id="wandtattoo" <?=$stickerImage->data["is_walldecal"] == 1 ? "checked" : ""?> data-variable="true">
                        <span class="slider round" id="wandtattooClick" data-binding="true"></span>
                    </label>
                </span>
            </div>
            <div>
                <h4>Kurzbeschreibung</h4>
                <textarea class="data-input" data-fun="productDescription" data-target="2" data-type="short" data-write="true"><?=$stickerImage->descriptions[2]["short"]?></textarea>
                <h4>Beschreibung</h4>
                <textarea class="data-input" data-fun="productDescription" data-target="2" data-type="long" data-write="true"><?=$stickerImage->descriptions[2]["long"]?></textarea>
            </div>
            <div class="shopStatus">
                <div class="shopStatusIcon" title="<?=$stickerImage->isInShop("wandtattoo") == 1 ? "Wandtattoo ist im Shop" : "Wandtattoo ist nicht im Shop" ?>">
                <?php if ($stickerImage->isInShop("wandtattoo")):?>
                <a title="Wandtattoo ist im Shop" target="_blank" href="<?=$stickerImage->getShopProducts("wandtattoo", "link")?>">
                <svg style="width:24px;height:24px" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M20 6H4V4H20V6M15.69 14H14V15.69C13.37 16.64 13 17.77 13 19C13 19.34 13.04 19.67 13.09 20H4V14H3V12L4 7H20L21 12V13.35C20.37 13.13 19.7 13 19 13C17.77 13 16.64 13.37 15.69 14M12 14H6V18H12V14M21.34 15.84L17.75 19.43L16.16 17.84L15 19L17.75 22L22.5 17.25L21.34 15.84Z" />
                </svg>
                </a>
                <?php else: ?>
                <svg style="width:24px;height:24px" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M4 4H20V6H4V4M15.46 16.88L16.88 15.46L19 17.59L21.12 15.47L22.54 16.88L20.41 19L22.54 21.12L21.12 22.54L19 20.41L16.88 22.54L15.46 21.12L17.59 19L15.47 16.88M4 7H20L21 12V13.34C20.33 13.09 19.62 12.96 18.91 12.96C17.71 12.96 16.54 13.33 15.54 14H14V15.53C13.3 16.53 12.92 17.73 12.92 18.95L13 20H4V14H3V12L4 7M6 14V18H12V14H6Z" />
                </svg>
                <?php endif; ?>
                </div>
                <button class="transferBtn" id="transferWandtattoo" data-binding="true">Wandtattoo übertragen</button>
            </div>
            <?=insertTemplate("classes/project/modules/sticker/views/stickerImageView.php", ["images" => $si->getWandtattooImages(), "imageCategory" => "wandtattoo"])?>
        </section>
        <section class="defCont">
            <p class="pHeading">Textil
                <input class="titleInput invisible" value="<?=$stickerImage->getAltTitle("textil")?>" data-write="true" data-type="textil" data-fun="changeAltTitle">
                <button class="addAltTitle" title="Alternativtitel hinzufügen" data-fun="addAltTitle" data-binding="true" data-type="textil">
                    <?=Icon::$iconEditText?>
                </button>
                <button class="infoButton" data-info="10">i</button>
                <button class="addAltTitle" title="Artikel ausblenden/ einblenden" data-binding="true" data-fun="toggleProductVisibility" data-type="textil">
                    <?=Icon::$iconVisible?>
                </button>
            </p>
            <div>
                <span>Textil</span>
                <span class="right">
                    <label class="switch">
                        <input type="checkbox" id="textil" <?=$stickerImage->data["is_shirtcollection"] == 1 ? "checked" : ""?> data-variable="true">
                        <span class="slider round" id="textilClick" data-binding="true"></span>
                    </label>
                </span>
            </div>
            <div>
                <span>Einfärbbar</span>
                <span class="right">
                    <label class="switch">
                        <input type="checkbox" id="textil" <?=$stickerImage->data["is_colorable"] == 1 ? "checked" : ""?> data-variable="true">
                        <span class="slider round" id="makeColorable" data-binding="true"></span>
                    </label>
                </span>
            </div>
            <div>
                <object id="svgContainer" data="<?=$stickerImage->getSVGIfExists()?>" type="image/svg+xml"></object>
                <br>
                <?php if ($stickerImage->data["is_colorable"] == 1): ?>
                <?php foreach ($stickerImage->textilColors as $color):?>
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
                <span style="margin-left: 7px" id="showPrice"><?=$stickerImage->getPriceTextilFormatted()?></span>
            </div>
            <div>
                <h4>Kurzbeschreibung</h4>
                <textarea class="data-input" data-fun="productDescription" data-target="3" data-type="short" data-write="true"><?=$stickerImage->descriptions[3]["short"]?></textarea>
                <h4>Beschreibung</h4>
                <textarea class="data-input" data-fun="productDescription" data-target="3" data-type="long" data-write="true"><?=$stickerImage->descriptions[3]["long"]?></textarea>
            </div>
            <div class="shopStatus">
                <div class="shopStatusIcon" title="<?=$stickerImage->isInShop("textil") == 1 ? "Textil ist im Shop" : "Textil ist nicht im Shop" ?>">
                <?php if ($stickerImage->isInShop("textil")):?>
                <a title="Textil ist im Shop" target="_blank" href="<?=$stickerImage->getShopProducts("textil", "link")?>">
                <?=Icon::$iconInShop?>
                </a>
                <?php else: ?>
                <?=Icon::$iconNotInShop?>
                <?php endif; ?>
                </div>
                <button class="transferBtn" id="transferTextil" data-binding="true">Textil übertragen</button>
            </div>
            <?=insertTemplate("classes/project/modules/sticker/views/stickerImageView.php", ["images" => $si->getTextilImages(), "imageCategory" => "textil"])?>
        </section>
    </div>
    <div class="defCont align-center">
        <h2 style="text-align: left;">Größen</h2>
        <div id="sizeTableWrapper"><?=$stickerImage->getSizeTable()?></div>
        <div>
            <p>Aufkleberpreisklasse</p>
            <div>
                <label for="price1">Preisklasse 1 (günstiger)</label>
                <input id="price1" type="radio" name="priceClass" <?=$stickerImage->data["price_class"] == 0 ? "checked" : ""?> onclick="changePriceclass(event)">
            </div>
            <div>
                <label for="price2">Preisklasse 2 (teurer)</label>
                <input id="price2" type="radio" name="priceClass" <?=$stickerImage->data["price_class"] == 1 ? "checked" : ""?> onclick="changePriceclass(event)">
            </div>
        </div>
        <div id="previewSizeText"><?=$stickerImage->data["size_summary"]?></div>
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
                    <input type="checkbox" id="revised" <?=$stickerImage->data["is_revised"] == 1 ? "checked" : ""?> data-variable="true">
                    <span class="slider round" id="revisedClick" data-binding="true"></span>
                </label>
            </span>
        </div>
        <p>Speicherort:<button class="infoButton" data-info="5">i</button></p>
        <div class="directoryContainer">
            <input id="dirInput" class="data-input directoryName" data-fun="speicherort" data-write="true" value="<?=$stickerImage->data["directory_name"]?>">
            <button class="directoryIcon" onclick="copyToClipboard('dirInput')">
                <svg style="width:24px;height:24px" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M19,20H4C2.89,20 2,19.1 2,18V6C2,4.89 2.89,4 4,4H10L12,6H19A2,2 0 0,1 21,8H21L4,8V18L6.14,10H23.21L20.93,18.5C20.7,19.37 19.92,20 19,20Z" />
                </svg>
            </button>
        </div>
        <p>Zusätzliche Infos und Notizen:<button class="infoButton" data-info="6">i</button></p>
        <textarea class="data-input" data-fun="additionalInfo" data-write="true"><?=$stickerImage->data["additional_info"]?></textarea>
        <div class="lds-ring productLoader" id="productLoader5"><div></div><div></div><div></div><div></div></div>
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
            <label>
                <input id="exportFb" name="exportFb" type="checkbox">    
                Nach Facebook exportieren<button class="infoButton" data-info="5">i</button><!-- TODO: neuen info text hinzufügen -->
            </label>
            <label>
                <input id="exportGoogle" name="exportGoogle" type="checkbox">    
                Nach Google exportieren
            </label>
            <label>
                <input id="exportAmazon" name="exportAmazon" type="checkbox">    
                Nach Amazon exportieren
            </label>
            <label>
                <input id="exportEtsy" name="exportEtsy" type="checkbox">    
                Nach Etsy exportieren
            </label>
            <label>
                <input id="exportEbay" name="exportEbay" type="checkbox">    
                Nach eBay exportieren
            </label>
            <label>
                <input id="exportPinterest" name="exportPinterest" type="checkbox">    
                Nach Pinterest exportieren
            </label>
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
<?php endif; ?>