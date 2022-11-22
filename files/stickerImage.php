<?php
    require_once('classes/project/StickerImage.php');
    require_once('classes/project/StickerShopDBController.php');

    $id = 0;
    $stickerImage = null;
    if (isset($_GET['id'])) {
        $id = (int) $_GET['id'];
        $stickerImage = new StickerImage($id);

        $images = $stickerImage->getImages();
        $mainImage = $images[0];
        $getDownloadResources = $stickerImage->getFiles();
    }

    if ($id != 0):
?>
    <div class="defCont cont1">
        <div>
            <h2>Motiv <span id="name"><?=$stickerImage->getName();?></span><button class="actionButton" data-binding="true" id="editName">✎</button></h2>
            <p>Artikelnummer: <span id="motivId" data-variable="true"><?=$id?></span></p>
            <div class="imageBigContainer">
                <img src="<?=$mainImage["link"]?>" alt="<?=$mainImage["alt"]?>" title="<?=$mainImage["alt"]?>" class="imageBig" data-image-id="<?=$mainImage["id"]?>">
                <div class="imageTypes">
                    <label>Aufkleberbild: <input type="checkbox" id="aufkleberbild" <?=$mainImage["is_aufkleber"] == 1 ? "checked" : ""?> onchange="changeImageParameters(event)"></label>
                    <label>Wandtattoobild: <input type="checkbox" id="wandtattoobild" <?=$mainImage["is_wandtattoo"] == 1 ? "checked" : ""?> onchange="changeImageParameters(event)"></label>
                    <label>Textilbild: <input type="checkbox" id="textilbild" <?=$mainImage["is_textil"] == 1 ? "checked" : ""?> onchange="changeImageParameters(event)"></label>
                    <button>Bild löschen</button>
                </div>
            </div>
            <div class="imageContainer">
                <?php foreach ($images as $image): ?>
                <img src="<?=$image["link"]?>" class="imagePrev" alt="<?=$image["alt"]?>" title="<?=$image["alt"]?>" data-image-id="<?=$image["id"]?>" onclick="changeImage(event)" data-is-aufkleber="<?=$image["is_aufkleber"]?>" data-is-wandtattoo="<?=$image["is_wandtattoo"]?>" data-is-textil="<?=$image["is_textil"]?>">
                <?php endforeach; ?>
            </div>
        </div>
        <div>
            <p>Download <?=$getDownloadResources?></p>
            <p>Erstellt am 29.12.2000<button class="actionButton" data-binding="true" id="editDate">✎</button><p>
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
                        <input type="checkbox" id="aufkleberPlott" <?=$stickerImage->data["is_plotted"] == 1 ? "checked" : ""?> data-variable="true">
                        <span class="slider round" id="aufkleberPlottClick" data-binding="true"></span>
                    </label>
                </span>
            </div>
            <div>
                <span>kurzfristiger Aufkleber</span>
                <span class="right">
                    <label class="switch">
                        <input type="checkbox" id="aufkleberKurz" <?=$stickerImage->data["is_short_time"] == 1 ? "checked" : ""?> data-variable="true">
                        <span class="slider round" data-binding="true" data-fun="toggleCheckbox"></span>
                    </label>
                </span>
            </div>
            <div>
                <span>langfristiger Aufkleber</span>
                <span class="right">
                    <label class="switch">
                        <input type="checkbox" id="aufkleberLang" <?=$stickerImage->data["is_long_time"] == 1 ? "checked" : ""?> data-variable="true">
                        <span class="slider round" data-binding="true" data-fun="toggleCheckbox"></span>
                    </label>
                </span>
            </div>
            <div>
                <span>mehrteilig</span>
                <span class="right">
                    <label class="switch">
                        <input type="checkbox" id="aufkleberMehrteilig" <?=$stickerImage->data["is_multipart"] == 1 ? "checked" : ""?> data-variable="true">
                        <span class="slider round" data-binding="true" data-fun="toggleCheckbox"></span>
                    </label>
                </span>
            </div>
            <button id="transferAufkleber" data-binding="true" <?=$stickerImage->data["is_plotted"] == 1 ? "" : "disabled"?>>Aufkleber übertragen</button>
            <button id="saveAufkleber" data-binding="true">Speichern</button>
            <div class="loaderOrSymbol">
                <div>
                    <div class="lds-ring" id="productLoader"><div></div><div></div><div></div><div></div></div>
                    <span id="productStatus"><?php if($stickerImage->isInShop()):?>✓ <?php else:?>x <?php endif;?></span>
                </div>        
                <p>Aufkleber ist im Shop</p>
            </div>
        </section>
        <section class="innerDefCont">
            <p>Wandtatto <input class="right" type="checkbox"></p>
            <button>Aufkleber übertragen</button>
            <p>Wandtatto ist im Shop</p>
        </section>
        <section class="innerDefCont">
            <p>Textil <input class="right" type="checkbox"></p>
            <button>Aufkleber übertragen</button>
            <p>Textil ist im Shop</p>
        </section>
    </div>
    <div class="defCont align-center">
        <?=$stickerImage->getSizeTable()?>
    </div>
    <div class="defCont">
        <h4>Kurzbeschreibung</h4>
        <textarea></textarea>
        <h4>Beschreibung</h4>
        <textarea></textarea>
    </div>
<?php else:

$query = "SELECT id, name, IF(is_plotted = 1, '✓', 'X') AS is_plotted, IF(is_short_time = 1, '✓', 'X') AS is_short_time, IF(is_long_time = 1, '✓', 'X') AS is_long_time, IF(is_multipart = 1, '✓', 'X') AS is_multipart, IF(is_walldecal = 1, '✓', 'X') AS is_walldecal, IF(is_shirtcollection = 1, '✓', 'X') AS is_shirtcollection FROM `module_sticker_sticker_data`";
$column_names = array(
    0 => array("COLUMN_NAME" => "id", "ALT" => "Nummer"),
    1 => array("COLUMN_NAME" => "name", "ALT" => "Name"),
    3 => array("COLUMN_NAME" => "is_plotted", "ALT" => "geplottet"),
    4 => array("COLUMN_NAME" => "is_short_time", "ALT" => "Werbeaufkleber"),
    5 => array("COLUMN_NAME" => "is_long_time", "ALT" => "Hochleistungsfolie"),
    6 => array("COLUMN_NAME" => "is_multipart", "ALT" => "mehrteilig"),
    7 => array("COLUMN_NAME" => "is_walldecal", "ALT" => "Wandtatto"),
    8 => array("COLUMN_NAME" => "is_shirtcollection", "ALT" => "Textil"),
);

$data = DBAccess::selectQuery($query);

$linker = new Link();
$linker->addBaseLink("sticker-overview");
$linker->setIterator("id", $data, "id");

$t = new Table();
$t->createByData($data, $column_names);
$t->addLink($linker);
echo $t->getTable();

endif; ?>
<!--
    Kategornienamen
        Motivname
    Nummer
        Aufkleber (plott)
    Kurzfrist
    Langfrist
    Wandtatto
    mehrteilig
    Shirtkollektion
    Texte im Shop
    T-Shirtmotiv
    Aufkleber (Druck)
    Schild
    Breite 30cm
    Breite 60cm
    Breite 90cm
    Breite 120cm
    Sondermaße
    erstellt
    T-Shirtmotiv Aufpreis
    Werbung alt
    Insta 3-Quartal 2021

    Möglichkeite, weitere Daten hinzuzufügen
-->
