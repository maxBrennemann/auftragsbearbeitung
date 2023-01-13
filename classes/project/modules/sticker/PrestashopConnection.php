<?php

class PrestashopConnection {

    private $url = "https://klebefux.de/auftragsbearbeitung/JSONresponder.php";
    private $prestaKey = "GUG1XJLZ2F5WHMY3Q3FZLA1WTPI4SVHD";
    private $prestaUrl = "https://klebefux.de";

    protected $webService;
    protected $xml;

    function __construct() {
        
    }

    protected function getXML($resource, $debug = false) {
        $this->webService = new PrestaShopWebservice($this->prestaUrl, $this->prestaKey, $debug);
        $this->xml = $this->webService->get(array('resource' => $resource));

        return $this->xml;
    }

    protected function addXML($options) {
        $this->xml = $this->webService->add($options);
    }

    protected function editXML($options) {
        $this->xml = $this->webService->edit($options);
    }
}

?>