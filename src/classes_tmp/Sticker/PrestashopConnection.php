<?php

namespace Src\Classes\Sticker;

use Src\Classes\Protocol;
use SimpleXMLElement;

/**
 * musste https://www.prestashop.com/forums/topic/912956-webservice-count-parameter-must-be-an-array-or-an-object-that-implements-countable/#comment-3296957
 * zu classes/webservice/WebserviceRequest.php hinzufügen, da es hier einene countable error gab
 *
 * https://stackoverflow.com/questions/69987125/getting-401-unauthorized-when-accessing-the-prestashop-api-webservice
 * und
 * https://wordcodepress.com/prestashop-1-7-webservice-api-401-unauthorized/
 * .htaccess file wird immer wieder mal neu generiert, dann kann es dazu kommen, dass ein 401 unauthorized Fehler kommt
 *
 * https://docs.prestashop-project.org/1-6-documentation/english-documentation/developer-guide/developer-tutorials/using-the-prestashop-web-service/web-service-tutorial
 */
class PrestashopConnection
{

    protected string $url;
    private string $prestaKey;
    private string $prestaUrl;

    protected \PrestaShopWebservice $webService;
    protected SimpleXMLElement $xml;

    public function __construct()
    {
        $this->url = $_ENV["SHOPURL"] . "/auftragsbearbeitung/JSONresponder.php";
        $this->prestaKey = $_ENV["SHOPKEY"];
        $this->prestaUrl = $_ENV["SHOPURL"];
    }

    public function getXML(string $resource, bool $debug = false): SimpleXMLElement
    {
        $debugText = $debug ? "true" : "false";
        Protocol::write("PrestashopConnection::getXML($resource, debug = $debugText)");

        if ($resource == "" || $this->prestaUrl == "" || $this->prestaKey == "") {
            throw new \Exception("Malconfigured resource in PrestashopConnection::getXML()");
        }

        $this->webService = new \PrestaShopWebservice($this->prestaUrl, $this->prestaKey, $debug);
        $this->xml = $this->webService->get([
            'resource' => $resource
        ]);

        return $this->xml;
    }

    /**
     * @param array<string, mixed> $options
     * @throws \Exception
     * @return void
     */
    protected function addXML(array $options): void
    {
        Protocol::write("PrestashopConnection::addXML()");

        if ($this->prestaUrl == "" || $this->prestaKey == "") {
            throw new \Exception("Malconfigured resource in PrestashopConnection::getXML()");
        }

        $this->xml = $this->webService->add($options);
    }

    /**
     * @param array<string, mixed>  $options
     * @throws \Exception
     * @return void
     */
    protected function editXML(array $options): void
    {
        Protocol::write("PrestashopConnection::editXML()");

        if ($this->prestaUrl == "" || $this->prestaKey == "") {
            throw new \Exception("Malconfigured resource in PrestashopConnection::getXML()");
        }

        $this->xml = $this->webService->edit($options);
    }

    /**
     * TODO: change from string return type to bool
     * @param string $resource
     * @param int $id
     * @param bool $debug
     * @throws \Exception
     * @return string
     */
    protected function deleteXML(string $resource, int $id, bool $debug = false): string
    {
        $debugText = $debug ? "true" : "false";
        Protocol::write("PrestashopConnection::getXML($resource, debug = $debugText)");

        if ($this->prestaUrl == "" || $this->prestaKey == "") {
            throw new \Exception("Malconfigured resource in PrestashopConnection::getXML()");
        }

        try {
            $this->webService = new \PrestaShopWebservice($this->prestaUrl, $this->prestaKey, $debug);

            $this->webService->delete([
                "resource" => $resource,
                "id" => $id,
            ]);
        } catch (\PrestaShopWebserviceException $e) {
            return "Error: " . $e->getMessage();
        }

        return "";
    }
}
