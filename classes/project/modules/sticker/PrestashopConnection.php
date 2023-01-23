<?php

class PrestashopConnection {

    private $url = SHOPURL . "/auftragsbearbeitung/JSONresponder.php";
    private $prestaKey = SHOPKEY;
    private $prestaUrl =  SHOPURL;

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

    protected function deleteXML($resource, $id, $debug = false) {
        try {
            $this->webService = new PrestaShopWebservice($this->prestaUrl, $this->prestaKey, $debug);

            $this->webService->delete([
                'resource' => $resource,
                'id' => $id,
            ]);
        } catch (PrestaShopWebserviceException $e) {
            echo 'Error:' . $e->getMessage();
        }
    }
}

?>