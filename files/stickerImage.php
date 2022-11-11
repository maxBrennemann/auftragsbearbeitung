<?php
    require_once('classes/project/StickerImage.php');
    require_once('classes/project/StickerShopDBController.php');

    $id = 0;
    $stickerImage = null;
    if (isset($_GET['id'])) {
        $id = (int) $_GET['id'];
        $stickerImage = new StickerImage($id);
    }

    if ($id != 0):
?>
    <div class="defCont cont1">
        <div>
            <h2>Motiv <span id="name"><?=$stickerImage->getName();?></span><button class="actionButton" data-binding="true" id="editName">✎</button></h2>
            <img src="https://klebefux.de/1175-large_default/aufkleber-sport-ist-mord.jpg">
        </div>
        <div>
            <p>Download <a href="#">Bild</a><a href="#">SVG/EPS</a><a href="#">Letterpot</a></p>
            <p>Erstellt am 29.12.2000<p>
        </div>
    </div>
    <div class="defCont cont2">
        <section class="innerDefCont">
            <div>
                <span>Aufkleber Plott</span>
                <span class="right">
                    <label class="switch">
                        <input type="checkbox" id="aufkleberPlott">
                        <span class="slider round" id="aufkleberPlottClick" data-binding="true"></span>
                    </label>
                </span>
            </div>
            <div>
                <span>kurzfristiger Aufkleber</span>
                <span class="right">
                    <label class="switch">
                        <input type="checkbox" id="aufkleberKurz">
                        <span class="slider round"></span>
                    </label>
                </span>
            </div>
            <div>
                <span>langfristiger Aufkleber</span>
                <span class="right">
                    <label class="switch">
                        <input type="checkbox" id="aufkleberLang">
                        <span class="slider round"></span>
                    </label>
                </span>
            </div>
            <div>
                <span>mehrteilig</span>
                <span class="right">
                    <label class="switch">
                        <input type="checkbox" id="aufkleberMehrteilig">
                        <span class="slider round"></span>
                    </label>
                </span>
            </div>
            <button id="aufkleberUebertragen" disabled>Aufkleber übertragen</button>
            <p><span><?php if($stickerImage->isInShop()):?>✓ <?php else:?>x <?php endif;?></span>Aufkleber ist im Shop</p>
        </section>
        <section class="innerDefCont">
            <p>Wandtatto <input class="right" type="checkbox"></p>
            <button>Aufkleber übertragen</button>
            <p>Aufkleber ist im Shop</p>
        </section>
        <section class="innerDefCont">
            <p>Textil <input class="right" type="checkbox"></p>
            <button>Aufkleber übertragen</button>
            <p>Aufkleber ist im Shop</p>
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
<?php endif; ?>
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
