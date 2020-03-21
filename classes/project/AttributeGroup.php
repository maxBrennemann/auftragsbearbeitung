<?php

require_once('classes/Files.php');

class AttributeGroup {

    private $attributeGroup = null;
    private $description = null;

    function __construct($id) {
        if (is_numeric($id)) {
            $data = DBAccess::selecQuery("SELECT attribute_group, descr FROM attribute_group WHERE id = $id");
            if (!empty($data)) {
                $data = $data[0];
                $this->attributeGroup = $data['attribute_group'];
                $this->description = $data['descr'];
            }
        } else {
            throw new Exception("Keine gültige Zahl übergeben.");
        }
    }
    
    public static function getProductToAttributeMatcher() {
        $fileContent = Files::get_file_contents("attributes.html");

        $attributeGroups = DBAccess::selectQuery("SELECT id, attribute_group FROM attribute_group");
        $anzahl = sizeof($attributeGroups);
        $attr = "";

        foreach ($attributeGroups as $agroup) {
            $attr .= "<option value=\"{$agroup['id']}\">{$agroup['attribute_group']}</option>";
        }

        $replacements = array(
            "ANZAHLATT" => $anzahl,
            "ATTR" => $attr
        );

        $fileContent = str_replace("ANZAHLATT", $replacements["ANZAHLATT"], $fileContent);
        $fileContent = str_replace("ATTR", $replacements["ATTR"], $fileContent);

        echo $fileContent;
    }

    public static function getAttributes($attributeGroupId) {
        $attributes = DBAccess::selectQuery("SELECT id, value FROM attribute WHERE attribute_group_id = $attributeGroupId");
        $size = sizeof($attributes);
        $html = "";

        foreach ($attributes as $a) {
            $html .= "<span class=\"selectSim\" onclick=\"addAttributeToProduct($attributeGroupId, {$a['id']}, '{$a['value']}');\">{$a['value']} ⊕</span><br>";
        }

        echo $html . "</select>";
    }
}

?>