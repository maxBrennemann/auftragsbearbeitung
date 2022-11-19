<?php

//require_once('.res\PrestashopCreateProduct.php');
require_once('vendor/autoload.php');

/**
 * musste https://www.prestashop.com/forums/topic/912956-webservice-count-parameter-must-be-an-array-or-an-object-that-implements-countable/#comment-3296957
 * zu classes/webservice/WebserviceRequest.php hinzufügen, da es hier einene countable error gab
 * 
 * https://stackoverflow.com/questions/69987125/getting-401-unauthorized-when-accessing-the-prestashop-api-webservice
 * und
 * https://wordcodepress.com/prestashop-1-7-webservice-api-401-unauthorized/
 * .htaccess file wird immer wieder mal neu generiert, dann kann es dazu kommen, dass ein 401 unauthorized Fehler kommt
 */
class StickerShopDBController {

    private $result = "";
    private $url = "https://klebefux.de/auftragsbearbeitung/JSONresponder.php";
    private $prestaKey = "GUG1XJLZ2F5WHMY3Q3FZLA1WTPI4SVHD";
    private $prestaUrl = "https://klebefux.de";

    private $tags = array();
    private $images = array();

    private $id_sticker;
    private $id_product = 0;
    private $title;
    private $description;
    private $description_short;
    private $price;

    function __construct($id_sticker, $title, $description, $description_short, $basePrice) {
        $this->id_sticker = $id_sticker;
        $this->title = $title;
        $this->description = $description;
        $this->description_short = $description_short;
        $this->price = $basePrice;
    }

    public function addImages($imageURLs) {
        $this->images = $imageURLs;
    }

    /**
     * @param tags is an array with strings
     */
    public function addTags($tags) {
        array_merge($this->tags, $tags);
    }

    public function select($query) {
        $this->result = $this->getCURLResponse($query);
        return $this;
    }

    public function getResult(){
        return $this->result;
    }

    private function getCURLResponse($query) {
        $ch = curl_init($this->url);
        # Setup request to send json via POST.
        $payload = json_encode(array("query"=> $query));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        # Return response instead of printing.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        # Send request.
        $result = curl_exec($ch);
        curl_close($ch);
        # Print response.
        return $result;
    }

    private function getXML($resource, $debug = false) {
        $this->webService = new PrestaShopWebservice($this->prestaUrl, $this->prestaKey, $debug);
        $this->xml = $this->webService->get(array('resource' => $resource));

        return $this->xml;
    }

    private function updateXML() {

    }

    private function addXML($options) {
        $this->xml = $this->webService->add($options);
    }

    /* https://www.prestashop.com/forums/topic/640693-how-to-add-a-product-through-the-webservice-with-custom-feature-values/#comment-2663527 */
    public function addSticker() {
        try {

            /* https://docs.prestashop-project.org/1-6-documentation/english-documentation/developer-guide/developer-tutorials/using-the-prestashop-web-service/web-service-reference */
            $xml = $this->getXML('product_options/5', true);
            return;

            $xml = $this->getXML('products?schema=blank');
            $resource_product = $xml->children()->children();

            unset($resource_product->id);
            unset($resource_product->position_in_category);
            unset($resource_product->manufacturer_name);
            unset($resource_product->id_default_combination);
            unset($resource_product->associations);

            $resource_product->id_shop = 1;
            $resource_product->minimal_quantity = 1;
            $resource_product->available_for_order = 1;
            $resource_product->show_price = 1;
            $resource_product->id_category_default = 2;
            $resource_product->id_tax_rules_group = 8; /* Steuergruppennummer für DE 19% */
            $resource_product->price = $this->price; /* an Steuer anpassen */
            $resource_product->active = 1;
            $resource_product->reference = $this->id_sticker;
            $resource_product->visibility = 'both';
            $resource_product->name->language[0] = $this->title;
            $resource_product->description->language[0] = $this->description;
            $resource_product->description_short->language[0] = $this->description_short;
            $resource_product->state = 1;

            $resource_product->addChild('associations'); 
            unset($resource_product->associations->tags);

            /*
             * Unset fields that may not be updated, without this, a 400 bad request happens
             * https://stackoverflow.com/questions/36883467/how-can-i-update-product-categories-using-prestashop-web-service
             */
            unset($resource_product->manufacturer_name);
            unset($resource_product->quantity);

            $tags = $resource_product->associations->addChild('tags'); 
            /* loop over all tags */
            for ($i = 0; $i < sizeof($this->tags); $i++) {
                $id = $this->getTagId($this->tags[$i]);
                $tag = $tags->addChild("tag");
                $tag->addChild("id", $id);
            }

           
            /* varianten */
           
            $opt = array(
                'resource' => 'products',
                'postXml' => $this->xml->asXML()
            );
            $this->addXML($opt);
            $this->id_product = $this->xml->product->id;

            /* images */
            $this->uploadImage($this->id_product, $this->images);

            /* set category here, Start is 2, maybe list all categories */
            $this->setCategoryDefault($this->id_product);
            
        } catch (PrestaShopWebserviceException $e) {
            echo $e->getMessage();
        }
    }

    private function setCategoryDefault($id) {
        try {
            $webService = new PrestaShopWebservice($this->prestaUrl, $this->prestaKey, false);
            $xml = $webService->get(array('resource' => 'products/' . $id));

            $product = $xml->children()->children();
            unset($product->associations->categories);

            /*
             * Unset fields that may not be updated, without this, a 400 bad request happens
             * https://stackoverflow.com/questions/36883467/how-can-i-update-product-categories-using-prestashop-web-service
             */
            unset($product->manufacturer_name);
            unset($product->quantity);

            $categories = $product->associations->addChild('categories'); 
            $category = $categories->addChild("category");
            $category->addChild("id", 2);

            $opt = array('resource' => 'products');
            $opt['putXml'] = $xml->asXML();
            $opt['id'] = $id;
            $xml = $webService->edit($opt);
        } catch (PrestaShopWebserviceException $e) {
            echo $e->getMessage();
        }
    }

    public function addTagToProduct() {

    }

    /* https://stackoverflow.com/questions/35975677/prestashop-webservice-add-products-tags-and-attachment-document */
    private function getTagId($tag){
        /* check if tag exists */
        $webService = new PrestaShopWebservice($this->prestaUrl, $this->prestaKey, false);
        $xml = $webService->get(array('resource' => '/api/tags?filter[name]='.$tag.'&limit=1'));

        $resources = $xml->children()->children();
        if (!empty($resources)) {
            $attributes = $resources->tag->attributes();
            return $attributes['id'];
        }
    
        /* add a new tag */
        $webService = new PrestaShopWebservice($this->prestaUrl, $this->prestaKey, false);
        $xml = $webService->get(array('resource' => '/api/tags?schema=synopsis'));
        $resources = $xml->children()->children();
    
        unset($resources->id);
        $resources->name = $tag;
        $resources->id_lang = "de";
    
        $opt = array(
            'resource' => 'tags',
            'postXml' => $xml->asXML()
        );
        $this->addXML($opt);
        $id = $this->xml->product->id;
        return $id;
    }

    private function getAttributeId($attributeGroup, $attribute){
        /* check if tag exists */
        $webService = new PrestaShopWebservice($this->prestaUrl, $this->prestaKey, false);
        $xml = $webService->get(array('resource' => '/api/product_options?filter[name]='.$attribute.'&limit=1'));

        $resources = $xml->children()->children();
        if (!empty($resources)) {
            $attributes = $resources->tag->attributes();
            return $attributes['id'];
        }
    
        /* add a new tag */
        $webService = new PrestaShopWebservice($this->prestaUrl, $this->prestaKey, false);
        $xml = $webService->get(array('resource' => '/api/product_options?schema=synopsis'));
        $resources = $xml->children()->children();
    
        unset($resources->id);
        $resources->name = $attribute;
        $resources->id_lang = "de";
    
        $opt = array(
            'resource' => 'product_options',
            'postXml' => $xml->asXML()
        );
        $this->addXML($opt);
        $id = $this->xml->product->id;
        return $id;
    }

    private function uploadImage($id, $imageURLs) {
        /* https://www.prestashop.com/forums/topic/407476-how-to-add-image-during-programmatic-product-import/ */

        $images = array();
        foreach ($imageURLs as $i) {
            array_push($images, urlencode($i));
        }

        $ch = curl_init($this->url);
        # Setup request to send json via POST.
        $payload = json_encode(array("images"=> $images, "id" => $id));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        # Return response instead of printing.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        # Send request.
        $result = curl_exec($ch);
        curl_close($ch);
        echo $result;
        # Print response.
        return $result;
    }

    /* https://docs.prestashop-project.org/1-6-documentation/english-documentation/developer-guide/developer-tutorials/using-the-prestashop-web-service/web-service-tutorial/chapter-9-image-management */
    private function uploadImageToPrestashop($productId) {
        $url = "https://klebefux.de/api/images/products/$productId";
        /**
         * Uncomment the following line in order to update an existing image
         */
        //$url = 'http://myprestashop.com/api/images/products/1/2?ps_method=PUT';
        
        $image_path = 'C:\\Users\\pc\\Documents\\3.png';
        $image_path = 'C:/Users/pc/Documents/3.png';
        $image_path = 'C:\\test.jpg';

        $cImage = new CurlFile($image_path, 'image/jpeg', "image");

        $ch = curl_init();
        //curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_USERPWD, $this->prestaKey.':');
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('image' => $cImage)); //'@'.$image_path.";type=image/jpeg"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    public function updateSticker() {
        
    }

    public function addCombination() {
        $xml = $this->getXML('product_options/5', true);
        // hier sind die Breiten gespeichert, evtl hardcoding verwenden?, dann noch farben benötigt, diese werden dann in die combinations geschrieben

        /*
        wir brauchen:
        id_product,
        quantity,
        unit_price_impact
        product_option_values
            product_option_value
                id
        later: images
        */
    }

}

?>