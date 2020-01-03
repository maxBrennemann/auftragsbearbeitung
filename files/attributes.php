<?php
    require_once("classes/DBAccess.php");

    $getAttributes = "SELECT value, attribute_group.attribute_group FROM attribute LEFT JOIN attribute_group ON attribute_group.id = attribute.attribute_group_id GROUP BY attribute_group.attribute_group";
    $getAttributes = DBAccess::selectQuery($getAttributes);

    $attributeGroups = DBAccess::selectQuery("SELECT * FROM attribute_group");
    $html = "";

    foreach ($attributeGroups as $group) {
        $attributeGroupId = $group['id'];
        $attributes = DBAccess::selectQuery("SELECT id, value FROM attribute WHERE attribute_group_id = $attributeGroupId");
        $html .= "<div class='defCont'><p><b>{$group['attribute_group']}</b><br>{$group['descr']}</p>";

        foreach ($attributes as $a) {
            $html .= "<span>{$a['value']}</span><br>";
        }

        $html .= "</div>";
    }

    echo $html;
?>