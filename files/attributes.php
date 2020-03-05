<?php
    require_once("classes/DBAccess.php");

    $getAttributes = "SELECT attribute_group FROM attribute_group";
    $getAttributes = DBAccess::selectQuery($getAttributes);

    $attributeGroups = DBAccess::selectQuery("SELECT * FROM attribute_group");
    $html = "";

    foreach ($attributeGroups as $group) {
        $attributeGroupId = $group['id'];
        $attributes = DBAccess::selectQuery("SELECT id, value FROM attribute WHERE attribute_group_id = $attributeGroupId");
        $html .= "<div class='defCont'><p><span data-id=\"{$attributeGroupId}\"><b>{$group['attribute_group']}</b></span><br>{$group['descr']}</p><div id=\"attributes{$attributeGroupId}\">";

        foreach ($attributes as $a) {
            $html .= "<span>{$a['value']}</span><br>";
        }

        $html .= "</div></div>";
    }

    $html .= "<button onclick=\"showAddAttribute()\">Weiteres Attribut hinzufügen</button>";

    echo $html;
?>
<div id="showDiv" style="display: none;">
    <input id="newVal">
    <select id="selectAttribute">
        <?php foreach ($attributeGroups as $group): ?>
            <option value="<?=$group['id']?>"><?=$group['attribute_group']?></option>
        <?php endforeach; ?>
	</select>
    <button onclick="addNewAttributeValue()">Hinzufügen</button>
    <br>
</div>
<button onclick="showAddAttributeValue()">Weiteren Attributwert hinzufügen</button>

<div id="showDivAddAttribute" style="display: none;">
    <span>Attributname: <input id="newName"></span><br>
    <span>Beschreibung: <input id="descr"></span><br>
    <button onclick="addNewAttribute()">Hinzufügen</button>
</div>
