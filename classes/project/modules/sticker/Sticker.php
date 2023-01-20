<?php

require_once('classes/project/modules/sticker/PrestashopConnection.php');
require_once('classes/project/modules/sticker/StickerChangelog.php');

/**
 * stellt allgemeine Stickerfunktionen zur Verfügung, ist die Elternklasse von
 * Aufkleber, Wandtattoo und Textil
 */
class Sticker extends PrestashopConnection {

    protected $idSticker;
    protected $idProduct;

    function __construct() {

    }

    public function getName() {

    }

    public function getId() {

    }

    public function getCreationDate() {

    }
    
    public function getBasePrice() {

    }

    public function getDescription() {

    }

    public function getTags() {

    }

    public function getImages() {

    }

    public function getActiveStatus() {

    }

    public function setName() {

    }

    public static function setDescription() {
        $id = (int) $_POST["id"];
        $type = (String) $_POST["type"];
        $target = (int) $_POST["target"];
        $content = (String) $_POST["content"];

        $query = "REPLACE INTO module_sticker_texts (id_sticker, `type`, `target`, content) VALUES (:id, :type, :target, :content);";
        DBAccess::updateQuery($query, [
            "id" => $id,
            "type" => $type,
            "target" => $target,
            "content" => $content,
        ]);

        StickerChangelog::log($id, $target, 0, "module_sticker_texts", "content", $content);
        echo "success";
    }

    public function save() {

    }

    public function update() {

    }

    public function delete() {
        $this->deleteXML("products", $this->idProduct);
    }

    /**
     * switches the product active status
     */
    public function toggleActiveStatus() {
        $xml = $this->getXML("product/$this->idProduct");
        $resource_product = $xml->children()->children();
        
        $active = (int) $resource_product->active;
        if ($active == 0) {
            $active = 1;
        } else {
            $active = 0;
        }

        $opt = array(
            'resource' => 'products',
            'putXml' => $xml->asXML(),
            'id' => $this->idProduct,
        );
        $this->editXML($opt);

        /* TODO: implement toggle type and access stickershopdbcontroller */
        /* TODO: fo: implement status via db */
    }
}

?>
