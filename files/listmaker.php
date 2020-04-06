<?php
    require_once('classes/project/Liste.php');

    $showLists = "";

    if (!isset($_GET['new']) && !isset($_GET['lid'])) {
        $showLists = Liste::getAllListPrevs();
    }

    if (isset($_GET['new'])) :
?>
<div class="defCont left">
    <h3>Neue Liste:</h3>
    <input id="newListName">
    <button id="createNewList" onclick="createNewList();">Neue Liste erstellen</button>
    <br>
    <button>Liste speichern</button>
</div>
<div class="defCont left">
    <h3>Neuen Unterpunkt:</h3>
    <input id="newListenpunktName" disabled>
    <input type="radio" name="listenpunktType" value="option1" id="listenpunktOption1" checked>
    <label for="option1">(1)</label>
    <input type="radio" name="listenpunktType" value="option2" id="listenpunktOption2">
    <label for="option2">(2)</label>
    <input type="radio" name="listenpunktType" value="option3">
    <label for="option3">(3)</label>
    <div class="innerDefCont">
        <h5>Auswahlknöpfe (1)</h5>
        <input type="radio" name="show" value="1" checked>
        <label for="1">Option 1</label>
        <input type="radio" name="show" value="2">
        <label for="2">Option 2</label>
        <input type="radio" name="show" value="3">
        <label for="3">Option 3</label>
    </div>
    <div class="innerDefCont">
        <h5>Kästchen abhaken (2)</h5>
        <input type="checkbox" name="check1" checked>
        <label for="check1">Ausgewählt</label>
        <input type="checkbox" name="check2">
        <label for="check2">Nicht ausgewählt</label>
    </div>
    <div class="innerDefCont">
        <h5>Textfeld (3)</h5>
        <label for="textfeld">Text eingeben:</label>
        <input type="text" name="textfeld">
    </div>
    <button id="createNewListenpunkt" onclick="createNewListenpunkt();" disabled>Neuen Listenpunkt erstellen</button>
</div>
<div class="defCont left">
    <h3>Neue Auswahlmöglichkeit:</h3>
    <input id="newAuswahlName" disabled>
    <button id="createNewListenauswahl" onclick="createNewAuswahl();" disabled>Neue Auswahl hinzufügen</button>
</div>
<div class="defCont" id="listpreview">
    <h3>Vorschau:</h3>
</div>
<script>
    window.onbeforeunload = function(){
        return 'Are you sure you want to leave?';
    };
</script>
<?php elseif (isset($_GET['lid'])) :
    $lid = $_GET['lid'];
    $list = Liste::readList($lid);
?>
    <?=$list->toHTML()?>
<?php else : ?>
    <h3>Alle Listen</h3>
    <a href="<?=Link::getPageLink("listmaker");?>?new">Neue Liste erstellen</a>
    <?=$showLists?>
<?php endif; ?>