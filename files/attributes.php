<?php
    require_once("classes/DBAccess.php");

    $getAttributes = "SELECT value, attribute_group.attribute_group FROM attribute LEFT JOIN attribute_group ON attribute_group.id = attribute.attribute_group_id GROUP BY attribute_group.attribute_group";


?>