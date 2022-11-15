<?php

require_once('vendor\prestashop\prestashop-webservice-lib\PSWebServiceLibrary.php');

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
    private $prestaKey = "8NREC2HS6FY3ZEFWSJ11WE52F25Q9QSD";
    private $prestaUrl = "https://klebefux.de";

    function __construct() {
        
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

    /* https://www.prestashop.com/forums/topic/640693-how-to-add-a-product-through-the-webservice-with-custom-feature-values/#comment-2663527 */
    public function addSticker($title, $decription) {
        try {
            $webService = new PrestaShopWebservice($this->prestaUrl, $this->prestaKey, true);
            //$blankXML = $webService->get(['resource' => 'products']);
            //$xml = $webService->get(array('resource' => 'products?schema=blank'));
            //$xml = $webService->get(array('resource' => 'products/427'));
            //$xml = $webService->get(array('resource' => 'product_option_values?schema=blank'));
            $xml = $webService->get(array('resource' => 'combinations?schema=blank'));
            var_dump($xml);
            return;
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
            //$resource_product->quantity = 10;           // la cantidad hay que setearla por medio de un webservice particular
            $resource_product->id_category_default = 2;   // PRODUCTOS COMO CATEGORÍA RAIZ
            $resource_product->price = 12.23;
            $resource_product->active = 1;
            $resource_product->visibility = 'both';
            $resource_product->name->language[0] = $title;
            $resource_product->description->language[0] = $decription;
            $resource_product->state = 1;

            $opt = array('resource' => 'products');
            $opt['postXml'] = $xml->asXML();
            $xml = $webService->add($opt);
            $id = $xml->product->id;
        } catch (PrestaShopWebserviceException $e) {
            echo $e->getMessage();
        }
    }

    public function updateSticker() {
        
    }

}

?>