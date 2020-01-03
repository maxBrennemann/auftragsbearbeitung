<?php

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
}

?>