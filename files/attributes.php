<?php
require_once("classes/DBAccess.php");

$getAttributes = "SELECT attribute_group FROM attribute_group";
$getAttributes = DBAccess::selectQuery($getAttributes);

$attributeGroups = DBAccess::selectQuery("SELECT * FROM attribute_group");

?>
<div class="attributesContainer">
<?php foreach ($attributeGroups as $group) : ?>
<div class="defCont singleAttribute">
    <h2 data-id="<?=$group['id']?>"><?=$group["attribute_group"]?></h2>
    <p><i><?=$group["descr"]?></i></p>
    <ul id="attributeValues_<?=$group["id"]?>">
    <?php 
    $attributes = DBAccess::selectQuery("SELECT id, value FROM attribute WHERE attribute_group_id = {$group['id']}");
    foreach ($attributes as $a) :?>
        <li><?=$a["value"]?></li>
    <?php endforeach; ?>
    </ul>
</div>
<?php endforeach; ?>
</div>
<div class="defCont addAttributeValue">
    <h3>Eigenschaftswert hinzufügen</h3>
    <input id="newVal">
    <select id="selectAttribute">
        <?php foreach ($attributeGroups as $group): ?>
            <option value="<?=$group['id']?>"><?=$group['attribute_group']?></option>
        <?php endforeach; ?>
    </select>
    <button onclick="addNewAttributeValue()">Hinzufügen</button>
</div>
<div class="defCont addAttribute">
    <h3>Neue Eigenschaft hinzufügen</h3>
    <span>Eigenschaftsname: <input id="newName"></span><br>
    <span>Beschreibung: <input id="descr"></span><br>
    <button onclick="addNewAttribute()">Hinzufügen</button>
</div>
<style>
    .attributesContainer {
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        gap: 10px;
        margin: auto;
    }

    .addAttribute {

    }
</style>