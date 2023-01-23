<?php

class PrestaCommunicater {

    protected $apiKey = SHOPKEY;
    private $shopUrl = SHOPURL;

    function __construct() {
        
    }

    protected function getXML($resource, $debug = false) {
        $this->webService = new PrestaShopWebservice($this->shopUrl, $this->apiKey, $debug);
        $this->xml = $this->webService->get(array('resource' => $resource));

        return $this->xml;
    }

    private function updateXML() {

    }

    private function addXML($options) {
        $this->xml = $this->webService->add($options);
    }

    private function editXML($options) {
        $this->xml = $this->webService->edit($options);
    }
}

?>