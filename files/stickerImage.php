<?php
    require_once('classes/project/StickerImage.php');
    require_once('classes/project/StickerShopDBController.php');

    $id = 0;
    $stickerImage = null;
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
    }

    if ($id != 0):
?>
    <div class="defCont cont1">
        <div>
            <h2>Motiv <span id="name"><?=$stickerImage->getName();?></span><button class="actionButton" data-binding="true" id="editName">✎</button>
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
            <p>Erstellt am <?=$stickerImage->getDate()?><button class="actionButton" data-binding="true" id="editDate">✎</button></p>
            <div class="lds-ring productLoader" id="productLoader4"><div></div><div></div><div></div><div></div></div>
            <button class="newButton" data-id="4" data-fun="transferAll" data-binding="true">Alles aktualisieren/ generieren</button>
            <div class="imageBigContainer">
                <img src="<?=$mainImage["link"]?>" alt="<?=$mainImage["alt"]?>" title="<?=$mainImage["alt"]?>" class="imageBig" data-image-id="<?=$mainImage["id"]?>">
                <div class="imageTypes">
                    <label>Aufkleberbild: <input type="checkbox" id="aufkleberbild" <?=$mainImage["is_aufkleber"] == 1 ? "checked" : ""?> onchange="changeImageParameters(event)"></label>
                    <label>Wandtattoobild: <input type="checkbox" id="wandtattoobild" <?=$mainImage["is_wandtattoo"] == 1 ? "checked" : ""?> onchange="changeImageParameters(event)"></label>
                    <label>Textilbild: <input type="checkbox" id="textilbild" <?=$mainImage["is_textil"] == 1 ? "checked" : ""?> onchange="changeImageParameters(event)"></label>
                    <button onclick="deleteImage()">Löschen</button>
                    <a href="<?=$mainImage["link"]?>" download="<?=$mainImage["alt"]?>" title="<?=$mainImage["alt"]?>">Herunterladen</a>
                </div>
            </div>
            <div class="imageContainer">
                <?php foreach ($images as $image): ?>
                <img src="<?=$image["link"]?>" class="imagePrev" alt="<?=$image["alt"]?>" title="<?=$image["alt"]?>" data-image-id="<?=$image["id"]?>" onclick="changeImage(event)" data-is-aufkleber="<?=$image["is_aufkleber"]?>" data-is-wandtattoo="<?=$image["is_wandtattoo"]?>" data-is-textil="<?=$image["is_textil"]?>">
                <?php endforeach; ?>
            </div>
        </div>
        <div>
            <?=$getDownloadResources?>
            <div id="delete-menu">
                <div class="item">
                    <svg style="width:24px;height:24px" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M19,4H15.5L14.5,3H9.5L8.5,4H5V6H19M6,19A2,2 0 0,0 8,21H16A2,2 0 0,0 18,19V7H6V19Z" />
                    </svg>
                    <span onclick="deleteImage(0)">Löschen</span>
                </div>
            </div>
        </div>
        <hr style="width: 100%;">
        <div>
            <form class="fileUploader" method="post" enctype="multipart/form-data" data-target="motiv" id="uploadFilesMotive" name="motivUpload">
                <input type="number" name="motivNumber" min="1" value="<?=$id?>" required hidden>
                <input id="motivname" name="motivname" required value="<?=$stickerImage->getName()?>" hidden>
                <input name="motiv" hidden>
            </form>
            <p>Hier Dateien per Drag&Drop ablegen oder 
                <label class="uploadWrapper">
                    <input type="file" name="uploadedFile" multiple class="fileUploadBtn" form="uploadFilesMotive">
                    hier hochladen
                </label>
            </p>
            <div class="filesList defCont"></div>
            <div id="showFilePrev"></div>
        </div>
    </div>
    <div class="defCont cont2">
        <section class="innerDefCont">
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
            <button id="transferAufkleber" data-binding="true" <?=$stickerImage->data["is_plotted"] == 1 ? "" : "disabled"?>>Aufkleber übertragen</button>
            <div class="loaderOrSymbol">
                <a target="_blank" href="<?=$stickerImage->getShopProducts("aufkleber", "link")?>" id="productStatus">
                    <div>
                        <div class="lds-ring productLoader" id="productLoader5"><div></div><div></div><div></div><div></div></div>
                        <span><?php if($stickerImage->isInShop("aufkleber")):?>✓ <?php else:?>x <?php endif;?></span>
                    </div>        
                    <p>Aufkleber ist im Shop</p>
                </a>
            </div>
            <div>
                <h4>Kurzbeschreibung</h4>
                <textarea class="data-input" data-fun="productDescription" data-target="1" data-type="short" data-write="true"><?=$stickerImage->descriptions[1]["short"]?></textarea>
                <h4>Beschreibung</h4>
                <textarea class="data-input" data-fun="productDescription" data-target="1" data-type= "long" data-write="true"><?=$stickerImage->descriptions[1]["long"]?></textarea>
            </div>
        </section>
        <section class="innerDefCont">
            <div>
                <span>Wandtattoo</span>
                <span class="right">
                    <label class="switch">
                        <input type="checkbox" id="wandtattoo" <?=$stickerImage->data["is_walldecal"] == 1 ? "checked" : ""?> data-variable="true">
                        <span class="slider round" id="wandtattooClick" data-binding="true"></span>
                    </label>
                </span>
            </div>
            <button id="transferWandtattoo" data-binding="true">Wandtattoo übertragen</button>
            <div class="loaderOrSymbol">
                <a target="_blank" href="<?=$stickerImage->getShopProducts("wandtattoo", "link")?>" id="productStatus">
                    <div>
                        <div class="lds-ring productLoader" id="productLoader2"><div></div><div></div><div></div><div></div></div>
                        <span><?php if($stickerImage->isInShop("wandtattoo")):?>✓ <?php else:?>x <?php endif;?></span>
                    </div>        
                    <p>Wandtattoo ist im Shop</p>
                </a>
            </div>
            <div>
                <h4>Kurzbeschreibung</h4>
                <textarea class="data-input" data-fun="productDescription" data-target="2" data-type="short" data-write="true"><?=$stickerImage->descriptions[2]["short"]?></textarea>
                <h4>Beschreibung</h4>
                <textarea class="data-input" data-fun="productDescription" data-target="2" data-type="long" data-write="true"><?=$stickerImage->descriptions[2]["long"]?></textarea>
            </div>
        </section>
        <section class="innerDefCont">
            <div>
                <span>Textil</span>
                <span class="right">
                    <label class="switch">
                        <input type="checkbox" id="textil" <?=$stickerImage->data["is_shirtcollection"] == 1 ? "checked" : ""?> data-variable="true">
                        <span class="slider round" id="textilClick" data-binding="true"></span>
                    </label>
                </span>
            </div>
            <button id="transferTextil" data-binding="true">Textil übertragen</button>
            <div class="loaderOrSymbol">
                <a target="_blank" href="<?=$stickerImage->getShopProducts("textil", "link")?>" id="productStatus">
                    <div>
                        <div class="lds-ring productLoader" id="productLoader3"><div></div><div></div><div></div><div></div></div>
                        <span><?php if($stickerImage->isInShop("textil")):?>✓ <?php else:?>x <?php endif;?></span>
                    </div>        
                    <p>Textil ist im Shop</p>
                </a>
            </div>
            <div>
                <object id="svgContainer" data="<?=$stickerImage->getSVGIfExists()?>" type="image/svg+xml"></object>
                <button id="makeColorable" data-binding="true">Einfärbbar machen</button>
                <button id="makeBlack" data-binding="true">Schwarz</button>
                <button id="makeRed" data-binding="true">Rot</button>
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
        </section>
    </div>
    <div class="defCont align-center">
        <?=$stickerImage->getSizeTable()?>
        <div id="previewSizeText"></div>
    </div>
    <div class="defCont">
        <h2>Weitere Infos</h2>
        <div class="revised">
            <span>Wurde der Artikel neu überarbeitet?</span>
            <span class="right">
                <label class="switch">
                    <input type="checkbox" id="revised" <?=$stickerImage->data["is_revised"] == 1 ? "checked" : ""?> data-variable="true">
                    <span class="slider round" id="revisedClick" data-binding="true"></span>
                </label>
            </span>
        </div>
        <p>Speicherort:</p>
        <input class="data-input" data-fun="speicherort" data-write="true" value="<?=$stickerImage->data["directory_name"]?>">
        <p>Zusätzliche Infos und Notizen:</p>
        <textarea class="data-input" data-fun="additionalInfo" data-write="true"><?=$stickerImage->data["additional_info"]?></textarea>
        <div class="lds-ring productLoader" id="productLoader5"><div></div><div></div><div></div><div></div></div>
        <button data-id="4" class="newButton marginTop30" data-fun="transferAll" data-binding="true">Alles aktualisieren/ generieren</button>
    </div>
<?php else:

$query = "SELECT id, `name`, directory_name, IF(is_plotted = 1, '✓', 'X') AS is_plotted, IF(is_short_time = 1, '✓', 'X') AS is_short_time, IF(is_long_time = 1, '✓', 'X') AS is_long_time, IF(is_multipart = 1, '✓', 'X') AS is_multipart, IF(is_walldecal = 1, '✓', 'X') AS is_walldecal, IF(is_shirtcollection = 1, '✓', 'X') AS is_shirtcollection, IF(is_revised = 1, '✓', '') AS is_revised, IF(is_marked = 1, '★', '') AS is_marked FROM `module_sticker_sticker_data`";
$data = DBAccess::selectQuery($query);

$column_names = array(
    0 => array("COLUMN_NAME" => "id", "ALT" => "Nummer"),
    1 => array("COLUMN_NAME" => "name", "ALT" => "Name"),
    2 => array("COLUMN_NAME" => "directory_name", "ALT" => "Verzeichnis"),
    3 => array("COLUMN_NAME" => "is_plotted", "ALT" => "geplottet"),
    4 => array("COLUMN_NAME" => "is_short_time", "ALT" => "Werbeaufkleber"),
    5 => array("COLUMN_NAME" => "is_long_time", "ALT" => "Hochleistungsfolie"),
    6 => array("COLUMN_NAME" => "is_multipart", "ALT" => "mehrteilig"),
    7 => array("COLUMN_NAME" => "is_walldecal", "ALT" => "Wandtattoo"),
    8 => array("COLUMN_NAME" => "is_shirtcollection", "ALT" => "Textil"),
    9 => array("COLUMN_NAME" => "is_revised", "ALT" => "Überarbeitet"),
    10 => array("COLUMN_NAME" => "is_marked", "ALT" => "Gemerkt"),
);

//Protocoll::prettyPrint($column_names);


$linker = new Link();
$linker->addBaseLink("sticker-overview");
$linker->setIterator("id", $data, "id");

$t = new Table();
$t->createByData($data, $column_names);
$t->addLink($linker);
echo $t->getTable();

endif; ?>