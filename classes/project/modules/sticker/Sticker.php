<?php

require_once('classes/project/modules/sticker/PrestashopConnection.php');
require_once('classes/project/modules/sticker/StickerChangelog.php');
require_once('classes/project/modules/sticker/StickerCombination.php');
require_once('classes/project/modules/sticker/StickerImage.php');
require_once('classes/project/modules/sticker/StickerTagManager.php');

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

    function __construct(int $idSticker) {
        $this->idSticker = $idSticker;
        $this->stickerData = DBAccess::selectQuery("SELECT * FROM module_sticker_sticker_data WHERE id = :idSticker LIMIT 1;", ["idSticker" => $idSticker]);
        if ($this->stickerData == null) {
            throw new Exception("Sticker does not exist.");
        }
        $this->stickerData = $this->stickerData[0];
        $this->additionalData = json_decode($this->stickerData["additional_data"], true);
        $this->idProduct = $this->getIdProduct();

        $this->imageData = new StickerImage2($idSticker);
    }

    private function getInstance() {
        if ($this instanceof Aufkleber) {
            return "aufkleber";
        } else if ($this instanceof Wandtattoo) {
            return "wandtattoo";
        } else if ($this instanceof Textil) {
            return "textil";
        } else {
            return "sticker";
        }
    }

    public function getIdProduct() {
        $instance = $this->getInstance();
        $id = (int) $this->additionalData["products"][$instance]["id"];

        if ($id == 0) {
            $id = -1;
        }

        return $id;
    }

    public function getName(): String {
        return $this->stickerData["name"];
    }

    public function getId(): int {
        return $this->idSticker;
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

    protected function isInShop() {}

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
            if (isset($prod[$type])) {
                return $prod[$type]["title"];
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
        $target = $this->getInstance();
        return $this->getDescr($target, "long");
    }

    public function getDescriptionShort(): String {
        $target = $this->getInstance();
        return $this->getDescr($target, "short");
    }

    protected function getDescr(int $target, String $type): String {
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

    public function getImages() {
        //
    }

    public function getActiveStatus() {

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

        StickerChangelog::log($id, $target, 0, "module_sticker_texts", "content", $content);
        echo "success";
    }

    /**
     * If a product for that sticker id already exists, the function calls the update function,
     * otherwise the create function is triggered
     */
    public function save() {
        $productId = $this->getIdProduct();

        if ($productId == -1) {
            $this->create("", "");
        } else {
            $this->save();
        }
    }

    /*
     * TODO: unterscheide zwischen update und create via exists in shop
     */
    public function update() {
        try {
            $xml = $this->getXML('products?schema=blank');
            $this->manipulateProductXML($xml);

            $opt = array(
                'resource' => 'products',
                'putXML' => $xml->asXML(),
            );
            $this->editXML($opt);
        } catch (PrestaShopWebserviceException $e) {
            echo $e->getMessage();
        }
    }

    public function create($descriptionLong, $descriptionShort) {
        try {
            $xml = $this->getXML('products?schema=blank');
            $this->manipulateProductXML($xml);

            $opt = array(
                'resource' => 'products',
                'postXml' => $xml->asXML(),
            );
            $this->addXML($opt);
            $this->idProduct = $this->xml->product->id;
        } catch(PrestaShopWebserviceException $e) {
            echo $e->getMessage();
        }
    }

    protected function manipulateProductXML(&$xml) {
        $resource_product = $xml->children()->children();

        /* unset unused paramters */
        unset($resource_product->id);
        unset($resource_product->position_in_category);
        unset($resource_product->manufacturer_name);
        unset($resource_product->id_default_combination);
        unset($resource_product->associations);
        
        /* set necessary parameters */
        $resource_product->{'id_shop'} = 1;
        $resource_product->{'minimal_quantity'} = 1;
        $resource_product->{'available_for_order'} = 1;
        $resource_product->{'show_price'} = 1;
        $resource_product->{'id_category_default'} = $this->getIdCategory();
        $resource_product->{'id_tax_rules_group'} = 8; /* Steuergruppennummer für DE 19% */
        $resource_product->{'price'} = $this->getBasePrice(); /* an Steuer anpassen */
        $resource_product->{'active'} = 1;
        $resource_product->{'reference'} = $this->idSticker;
        $resource_product->{'visibility'} = 'both';
        $resource_product->{'name'}->language[0] = $this->getName();
        $resource_product->{'description'}->language[0] = $this->getDescription();
        $resource_product->{'description_short'}->language[0] = $this->getDescriptionShort();
        $resource_product->{'state'} = 1;

        /*
         * Unset fields that may not be updated, without this, a 400 bad request happens
         * https://stackoverflow.com/questions/36883467/how-can-i-update-product-categories-using-prestashop-web-service
         */
        unset($resource_product->manufacturer_name);
        unset($resource_product->quantity);

        return $xml;
    }

    public function createCombinations() {

    }

    public function uploadImages($imageURLs) {
        /* https://www.prestashop.com/forums/topic/407476-how-to-add-image-during-programmatic-product-import/ */
        $images = array();
        foreach ($imageURLs as $i) {
            array_push($images, urlencode($i));
        }

        $ch = curl_init($this->url);
        # Setup request to send json via POST.
        $payload = json_encode(array("images"=> $images, "id" => $this->idProduct));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        # Return response instead of printing.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        # Send request.
        $result = curl_exec($ch);
        curl_close($ch);

        /* TODO: image shop ids abspeichern */
        $imageIds = json_decode($result);
        foreach ($imageIds as $imageId) {
            //$key = array_search($imageId["url"], $imageURLs);
        }
    }

    public function setCategory() {

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

    private function getAccessoires() {
        $query = "SELECT id_product FROM module_sticker_accessoires WHERE id_sticker = :idSticker";
        $result = DBAccess::selectQuery($query, ["idSticker" => $this->idSticker]);
        return array_map(fn ($data): int => $data["id_product"], $result);
    }

    /**
     * connects a list of products with the current product
     */
    public function connectAccessoires($xml = null) {
        if ($xml == null) {
            $xml = $this->getXML("products/$this->idProduct");
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
            'id' => $this->idProduct,
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

}

?>
