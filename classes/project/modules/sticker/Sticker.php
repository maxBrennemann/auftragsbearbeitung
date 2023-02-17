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

    protected $name;

    function __construct($idSticker) {
        $this->idSticker = $idSticker;
        
        // TODO: implement db queries and get name
    }

    public function getName() {
        return $this->name;
    }

    public function getId() {
        return $this->idSticker;
    }

    public function getCreationDate() {

    }

    public function getIdCategory() {

    }
    
    public function getBasePrice() {

    }

    public function getDescription(int $target): String {
        return $this->getDescr($target, "long");
    }

    public function getDescriptionShort(int $target): String {
        return $this->getDescr($target, "short");
    }

    public function getDescr(int $target, String $type): String {
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

    public function create($descriptionLong, $descriptionShort) {
        try {
            $xml = $this->getXML('products?schema=blank');
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
            $resource_product->{'description'}->language[0] = $descriptionLong;
            $resource_product->{'description_short'}->language[0] = $descriptionShort;
            $resource_product->{'state'} = 1;

            /*
             * Unset fields that may not be updated, without this, a 400 bad request happens
             * https://stackoverflow.com/questions/36883467/how-can-i-update-product-categories-using-prestashop-web-service
             */
            unset($resource_product->manufacturer_name);
            unset($resource_product->quantity);

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

    /**
     * connects a list of products with the current product
     */
    public function connectAccessoires($connectTo, $xml = null) {
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

    public function getPurchasingPrices() {
        
    }

}

?>
