<?php

require_once('vendor\prestashop\prestashop-webservice-lib\PSWebServiceLibrary.php');

class StickerShopDBController {

    private $result = "";
    private $url = "https://klebefux.de/auftragsbearbeitung/JSONresponder.php";
    private $prestaKey = "GUG1XJLZ2F5WHMY3Q3FZLA1WTPI4SVHD";
    private $prestaUrl = "https://klebefux.de/";

    function __construct() {
        $webService = new PrestaShopWebservice($this->prestaUrl, $this->prestaKey);
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

}

?>