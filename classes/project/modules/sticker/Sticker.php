<?php

require_once('classes/project/modules/sticker/PrestashopConnection.php');
require_once('classes/project/modules/sticker/StickerChangelog.php');
require_once('classes/project/modules/sticker/StickerCombination.php');
require_once('classes/project/modules/sticker/StickerImage.php');
require_once('classes/project/modules/sticker/StickerTagManager.php');
require_once('classes/project/modules/sticker/StickerUpload.php');

/**
 * stellt allgemeine Stickerfunktionen zur Verfügung, ist die Elternklasse von
 * Aufkleber, Wandtattoo und Textil
 */
class Sticker extends PrestashopConnection {

    protected $idSticker;
    protected $idProduct;

    protected $name;
    protected $stickerData;
    protected $additionalData;

    protected $imageData;

    protected $instanceType = "sticker";

    function __construct(int $idSticker) {
        $this->idSticker = $idSticker;
        $this->stickerData = DBAccess::selectQuery("SELECT * FROM module_sticker_sticker_data WHERE id = :idSticker LIMIT 1;", ["idSticker" => $idSticker]);

        if ($this->stickerData == null) {
            throw new Exception("Sticker does not exist.");
        }
        $this->stickerData = $this->stickerData[0];

        if ($this->stickerData["additional_data"] == null) {
            $this->additionalData = [];
        } else {
            $this->additionalData = json_decode($this->stickerData["additional_data"], true);
        }

        $this->imageData = new StickerImage($idSticker);
        $this->instanceType = "sticker";
    }

    public function getIdProduct() {
        if ($this->idProduct != null) {
            return $this->idProduct;
        }

        if (isset($this->additionalData["products"][$this->instanceType])) {
            $this->idProduct = (int) $this->additionalData["products"][$this->instanceType]["id"];
        } else {
            $this->idProduct = 0;
        }

        return $this->idProduct;
    }

    public function getName(): String {
        return $this->stickerData["name"];
    }

    public function getId(): int {
        return $this->idSticker;
    }

    public function getType() {
        return $this->instanceType;
    }

    public function getDirectory() {
        return $this->stickerData["directory_name"];
    }

    public function getIsMarked() {
        return $this->stickerData["is_marked"];
    }

    public function getIsRevised() {
        return $this->stickerData["is_revised"];
    }

    public function getAdditionalInfo() {
        return $this->stickerData["additional_info"];
    }

    public function isInShop() {
        return false;
    }

    protected function checkIsInShop($type): bool {
        if ($this->additionalData != null) {
            if (isset($this->additionalData["products"]) && isset($this->additionalData["products"][$type])) {
                return true;
            }
        }

        return false;
    }

    protected function getShopLink() {}

    protected function getShopLinkHelper($type) {
        if ($this->additionalData != null) {
            if (isset($this->additionalData["products"]) && isset($this->additionalData["products"][$type])) {
                return $this->additionalData["products"][$type]["link"];
            }
        }

        return "#";
    }
    
    public function getAltTitle($type = ""): String {
        if ($type == "")
            return "";

        if (isset($this->additionalData["products"])) {
            $prod = $this->additionalData["products"];
            if (isset($prod[$type]) && isset($prod[$type]["altTitle"])) {
                $altTitle = $prod[$type]["altTitle"];
                if ($altTitle == null) {
                    return "";
                }
                return $altTitle;
            }
        }
        return "";
    }

    public function getCreationDate() {
        return $this->stickerData["creation_date"];
    }

    public function getIdCategory() {

    }
    
    public function getBasePrice() {

    }

    public function getDescription(): String {
        return $this->getDescr($this->instanceType, "long");
    }

    public function getDescriptionShort(): String {
        return $this->getDescr($this->instanceType, "short");
    }

    protected function getDescr(String $target, String $type): String {
        $description = DBAccess::selectQuery("SELECT content, `type` FROM module_sticker_texts WHERE id_sticker = :id_sticker AND `target` = :target AND `type` = :type", [
            "id_sticker" => $this->idSticker,
            "target" => $target,
            "type" => $type,
        ]);

        if ($description != null) {
            $description = $description[0]["content"];
        } else {
            $description = "";
        }

        return $description;
    }

    public function getTags() {

    }

    public function getActiveStatus() {
        if (isset($this->additionalData["products"][$this->instanceType])) {
            $ref = $this->additionalData["products"][$this->instanceType];
            if (isset($ref["status"])) {
                return $ref["status"] == 1;
            }
        }
        return true;
    }

    public function setName(String $name) {
        $query = "UPDATE module_sticker_sticker_data SET `name` = :stickerName WHERE id = :idSticker";
        DBAccess::updateQuery($query, ["stickerName" => $name, "idSticker" => $this->getId()]);

        StickerChangelog::log($this->getId(), 0, $this->getId(), "module_sticker_sticker_data", "name", $name);

        return ["status" => "success"];
    }

    public static function setDescription() {
        $id = (int) $_POST["id"];
        $type = (String) $_POST["type"];
        $target = (String) $_POST["target"];
        $content = (String) $_POST["content"];

        $query = "REPLACE INTO module_sticker_texts (id_sticker, `type`, `target`, content) VALUES (:id, :type, :target, :content);";
        DBAccess::updateQuery($query, [
            "id" => $id,
            "type" => $type,
            "target" => $target,
            "content" => $content,
        ]);

        //StickerChangelog::log($id, $target, 0, "module_sticker_texts", "content", $content);
        echo "success";
    }

    public function save() {}

    public function createCombinations() {

    }

    public function setCategory() {

    }

    public function delete() {
        $this->deleteXML("products", $this->getIdProduct());
    }

    /**
     * switches the product active status
     */
    public function toggleActiveStatus() {
        $xml = $this->getXML("products/" . $this->getIdProduct());
        $resource_product = $xml->children()->children();
        
        $active = (int) $resource_product->active;
        
        if ($active == 0) {
            $active = 1;
        } else {
            $active = 0;
        }

        $this->additionalData["products"][$this->instanceType]["status"] = $active;
        $this->saveAdditionalData();

        $resource_product->{"active"} = $active;
        unset($resource_product->manufacturer_name);
        unset($resource_product->quantity);

        $opt = array(
            'resource' => 'products',
            'putXml' => $xml->asXML(),
            'id' => $this->getIdProduct(),
        );
        $this->editXML($opt);

        /* TODO: implement toggle type and access stickershopdbcontroller */
        /* TODO: fo: implement status via db */
    }

    private function getAccessoires() {
        $query = "SELECT id_product_reference FROM module_sticker_accessoires WHERE id_sticker = :idSticker AND `type` = :typeSticker";
        $result = DBAccess::selectQuery($query, [
            "idSticker" => $this->idSticker,
            "typeSticker" => $this->instanceType,
        ]);

        return array_map(fn ($data): int => $data["id_product_reference"], $result);
    }

    /**
     * connects a list of products with the current product
     */
    public function connectAccessoires($xml = null) {
        if ($xml == null) {
            $xml = $this->getXML("products/" . $this->getIdProduct());
        }

        $product_reference = $xml->children()->children();
        unset($product_reference->manufacturer_name);
        unset($product_reference->quantity);
        $accessoires = $product_reference->{'associations'}->accessories;

        $existingAccessoires = [];
        if ($accessoires != null) {
            foreach ($accessoires as $productConnected) {
                $existingAccessoires[] = $productConnected->{'id'};
            }
        }

        /* insert new tag if it does not exist */
        $connectTo = $this->getAccessoires();
        foreach ($connectTo as $id) {
            if (!in_array($id, $existingAccessoires)) {
                $product = $accessoires->addChild("product");
                $product->addChild("id", $id);
            }
        }

        $opt = array(
            'resource' => 'products',
            'putXml' => $xml->asXML(),
            'id' => $this->getIdProduct(),
        );
        $this->editXML($opt);
    }

    public function getAttributes() {

    }

    public function getPrices() {

    }

    public function getPricesMatched() {

    }

    public function getPurchasingPrices() {
        
    }

    public function getPurchasingPricesMatched() {
        
    }

    /**
     * inserts a new sticker into the database and sets all its initial values
     * @param string $title the new sticker's name
     */
    public static function createNewSticker(string $title) {
        /* insert sticker into database */
        $query = "INSERT INTO module_sticker_sticker_data (`name`) VALUES (:title)";
        $id = DBAccess::insertQuery($query, ["title" => $title]);

        require_once('classes/project/modules/sticker/AufkleberWandtattoo.php');
        $aufkleberWandtattoo = new AufkleberWandtattoo($id);
        $sizes = [100, 200, 300, 600, 900, 1200];
        foreach ($sizes as $size) {
            $price = $aufkleberWandtattoo->getPrice($size, 0, 1);
            $aufkleberWandtattoo->updatePrice($size, 0, $price);
        }

        /* sets exports defaults to true */
        $query = "INSERT INTO module_sticker_exports (idSticker, facebook, google, amazon, etsy, ebay, pinterest) VALUES ($id, -1, -1, -1, -1, -1, -1);";
        DBAccess::insertQuery($query);

        if ($id == 0 || !is_numeric($id)) {
            echo -1;
        } else {
            $link = Link::getPageLink("sticker") . "?id=" . $id;
            echo $link;
        }
    }

    private function saveAdditionalData() {
        $query = "UPDATE module_sticker_sticker_data SET `additional_data` = :additionalData WHERE id = :idSticker";
        DBAccess::updateQuery($query, [
            "additionalData" => json_encode($this->additionalData),
            "idSticker" => $this->getId()
        ]);
    }

}
