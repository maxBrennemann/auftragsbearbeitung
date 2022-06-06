<?php 

require_once('classes/project/Table.php');
require_once('classes/front/CategoryTree.php');

$categoryitems = CategoryTree::getOneLayerArray();

$cacheOn = "";
$cacheOff = "checked";

$cacheStatus = CacheManager::getCacheStatus();
if ($cacheStatus == "on") {
    $cacheOn = "checked";
    $cacheOff = "";
}

?>
<section>
    <h2>Auftragstypen festlegen</h2>
    <?php echo (new Table("auftragstyp"))->getTable(); ?>
</section>
<section>
    <h2>Einkaufsmöglichkeiten festlegen</h2>
    <?php echo (new Table("einkauf"))->getTable(); ?>
</section>
<section>
    <h2>Mitarbeiter festlegen</h2>
    <?php echo (new Table("mitarbeiter"))->getTable(); ?>
</section>
<section>
    <h2>Cache</h2>
	<input onchange="toggleCache('on')" type="radio" name="cacheswitch" value="on" <?=$cacheOn?>> Cache aktivieren<br>
	<input onchange="toggleCache('off')" type="radio" name="cacheswitch" value="off" <?=$cacheOff?>> Cache deaktivieren<br>
    <button id="deleteCache">Cache löschen</button>
</section>
<section>
    <h2>Persönliche Einstellungen</h2>
    <div class="defCont" id="farbe">
        <h4>Farbtöne festlegen</h4>
        <select>
            <option value="1">Tabellenfarbe</option>
            <option value="2">Äußere Rahmen</option>
            <option value="3">Innere Rahmen</option>
        </select>
        <script>var cp = new Colorpicker(document.getElementById("farbe"));</script>
        <button onclick="setCustomColor();">Diese Farbe übernehmen</button>
        <button onclick="setCustomColor(0)">Auf Standard zurücksetzen</button>
    </div>
</section>
<section>
    <h2>Kategorien festlegen</h2>
    <?=CategoryTree::getHTMLRepresentation()?>
    <select name="categories" id="select-category">
        <?php foreach ($categoryitems as $c): ?>
        <option value="<?=$c->id?>"><?=$c->title?></option>
        <?php endforeach; ?>
    </select>
</section>
<script>
    function setCustomColor(value) {
        let color = value == 0 ? "" : cp.color;
        let type = document.querySelector("select")
        type = type.options[type.selectedIndex].value;

        /* ajax parameter */
        let params = {
            getReason: "setCustomColor",
            type: type,
            color: color
        };

        var add = new AjaxCall(params, "POST", window.location.href);
        add.makeAjaxCall(function (response) {
            if (response == "ok") {
                location.reload();
            }
        });
    }

    function toggleCache(status) {
        /* ajax parameter */
        let params = {
            getReason: "toggleCache",
            status: status
        };

        var toggle = new AjaxCall(params, "POST", window.location.href);
        toggle.makeAjaxCall(function (response) {
            console.log(response);
            if (response == "ok") {
                infoSaveSuccessfull("success");
            }
        });
    }
</script>
