<?php

/**
 * https://gist.github.com/is-just-me/4dadabf7e5514dcc25cf7de51eac9d21#file-test_functions-php
 * 
 * made a class out of it
 */
class PrestashopCreateProduct {

    private $webService;
    private $config;

    function __construct($config) {
        $this->config = $config;
    }

    public function add_combination($data){
        try {
            $xml = $this->webService->get(array('url' => $this->config["ps_shop"].'api/combinations?schema=blank'));
            
            $combination = $xml->children()->children();
            $combination->associations->product_option_values->product_option_values[0]->id = $data["option_id"];
            $combination->reference = $data["code"];
            $combination->id_product = $data["id_product"];
            $combination->price = $data["price"]; //Prix TTC
            $combination->show_price = 1;
            $combination->quantity = $data["quantity"]; //Prix TTC
            $combination->minimal_quantity = 1;
            //$product_option_value->id                                                     = 1;    
            
            
            $opt = array('resource' => 'combinations');
            $opt['postXml']                                                                 = $xml->asXML();
            sleep(1);
            $xml = $this->webService->add($opt); 
            $combination = $xml->combination;
        } catch (PrestaShopWebserviceException $e){
            return;
        }
        //insert stock
        return $combination;
    }
    public function make_product_options($data){
        try {
            $xml = $this->webService->get(array('url' => $this->config["ps_shop"].'api/product_option_values?schema=blank'));
            
            $product_option_value = $xml->children()->children();
            
            //$product_option_value->id                       = 1;    
            $product_option_value->id_attribute_group = $data["id_attribute_group"];
            
            $product_option_value->name->language[0][0] = $data["name"];
            $product_option_value->name->language[0][0]['id'] = 1;
            
            
            $opt = array('resource' => 'product_option_values');
            $opt['postXml'] = $xml->asXML();
            sleep(1);
            $xml = $this->webService->add($opt); 
            $product_option_value = $xml->product_option_value;
        } catch (PrestaShopWebserviceException $e){
            return 0;
        }
        //insert stock
        return (int) $product_option_value->id;
    }

    public function make_father_product($data){
        try {
            $xml = $this->webService->get(array('url' => $this->config["ps_shop"].'api/products?schema=blank'));
            $product = $xml->children()->children();
            
            $product->price = $data["price"]; //Prix TTC
            $product->wholesale_price                                             =$data["price"]; //Prix d'achat
            $product->active = '1';
            $product->on_sale = 1; //on ne veux pas de bandeau promo
            $product->show_price = 1;
            $product->available_for_order = 1;
            
            $product->name->language[0][0] = $data["name"];
            $product->name->language[0][0]['id'] = 1;
            
            $product->description->language[0][0] = $data["description"];
            $product->description->language[0][0]['id'] = 1;
            
            $product->description_short->language[0][0] = $data["description"];
            $product->description_short->language[0][0]['id'] = 1;
            $product->reference = $data["code"];
            
            $product->associations->categories->addChild('category')->addChild('id', $data["category_id"]);
            $product->id_category_default = $data["category_id"];
            
            //$product->associations->stock_availables->stock_available->quantity = 1222;
            
            $opt = array('resource' => 'products');
            $opt['postXml'] = $xml->asXML();
            sleep(1);
            $xml = $this->webService->add($opt); 
            
            $product = $xml->product;
        } catch (PrestaShopWebserviceException $e){
            return;
        }
        return (int) $product->id;
    }

    public function make_product($data){
        try {
            $xml = $this->webService->get(array('url' => $this->config["ps_shop"].'api/products?schema=blank'));
            $product = $xml->children()->children();
            
            $product->price = $data["price"]; //Prix TTC
            $product->wholesale_price                                             =$data["price"]; //Prix d'achat
            $product->active = '1';
            $product->on_sale = 1; //on ne veux pas de bandeau promo
            $product->show_price = 1;
            $product->available_for_order = 1;
            
            $product->name->language[0][0] = $data["name"];
            $product->name->language[0][0]['id'] = 1;
            
            $product->description->language[0][0] = $data["description"];
            $product->description->language[0][0]['id'] = 1;
            
            $product->description_short->language[0][0] = $data["description"];
            $product->description_short->language[0][0]['id'] = 1;
            $product->reference = $data["code"];
            
            $product->associations->categories->addChild('category')->addChild('id', $data["category_id"]);
            $product->id_category_default = $data["category_id"];
            
            //$product->associations->stock_availables->stock_available->quantity = 1222;
            
            $opt = array('resource' => 'products');
            $opt['postXml'] = $xml->asXML();
            sleep(1);
            $xml = $this->webService->add($opt); 
            
            $product = $xml->product;
        } catch (PrestaShopWebserviceException $e){
            return;
        }
        //insert stock
        $this->set_product_quantity($data["quantity"], $product->id, $product->associations->stock_availables->stock_available->id, $product->associations->stock_availables->stock_available->id_product_attribute);
        return $product->id;
    }
    /**
    * Actualizar stock usando WS
    */
    public function set_product_quantity($quantity, $ProductId, $StokId, $AttributeId){
        try {
            $opt                             = array();
            $opt['resource']                 = "stock_availables";
            $opt['filter']                   = array('id_product' => $ProductId, "id_product_attribute" => $AttributeId);
            $xml                             = $this->webService->get($opt);
            $resources                       = $xml->children()->children()[0];
            $StokId                          = (int) $resources['id'][0];
            
            $xml                             = $this->webService->get(array('url' => $this->config["ps_shop"].'api/stock_availables?schema=blank'));
            $resources                       = $xml -> children() -> children();
            $resources->id                   = $StokId;
            $resources->id_product           = $ProductId;
            $resources->quantity             = $quantity;
            $resources->id_shop              = 1;
            $resources->out_of_stock         =0;
            $resources->depends_on_stock     = 0;
            $resources->id_product_attribute =$AttributeId;
            
            $opt                             = array('resource' => 'stock_availables');
            $opt['putXml']                   = $xml->asXML();
            $opt['id']                       = $StokId;
            $xml                             = $this->webService->edit($opt);
        } catch (PrestaShopWebserviceException $ex) {

        }

    }
}
