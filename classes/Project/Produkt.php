<?php

namespace Classes\Project;

use Classes\Link;
use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class Produkt
{
    private $price = null;
    private int $produktnummer;
    private string $bezeichnung;
    private string $beschreibung;

    public function __construct(int $produktnummer)
    {
        $data = DBAccess::selectAllByCondition("produkt", "Nummer", $produktnummer);
        if (!empty($data)) {
            $data = $data[0];
            $this->price = $data['Preis'];
            $this->produktnummer = (int) $data['Nummer'];
            $this->bezeichnung = (string) $data['Bezeichnung'];
            $this->beschreibung = (string) $data['Beschreibung'];
        }
    }

    public function bekommePreis(): float
    {
        return 0;
    }

    public function getBezeichnung(): string
    {
        return $this->bezeichnung;
    }

    public function getBeschreibung(): string
    {
        return $this->beschreibung;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function getPriceWithTax(): string
    {
        return number_format((float) $this->price / 100, 2, ",", ".");
    }

    public function getProductId(): int
    {
        return $this->produktnummer;
    }

    public function getProduktLink(): string
    {
        return Link::getPageLink("produkt") . "?id=" . $this->produktnummer;
    }

    /**
     * @return array<int, Image>
     */
    public function getImages(): array
    {
        $query = "SELECT DISTINCT id FROM dateien LEFT JOIN dateien_produkte ON dateien_produkte.id_datei = dateien.id WHERE dateien_produkte.id_produkt = $this->produktnummer";
        $data = DBAccess::selectQuery($query);

        $images = array();

        foreach ($data as $d) {
            array_push($images, new Image($d['id']));
        }

        /* checks if array is empty, if so, add default image */
        if (sizeof($images) == 0) {
            array_push($images, Image::setDefault());
        }

        return $images;
    }

    /**
     * @param array<string, string> $arr
     * @return string
     */
    public function fillToArray(array $arr): string
    {
        return "";
    }

    /**
     * comment to this solution: https://stackoverflow.com/a/38467483, modified
     * @return array<int, array<string, string>>
     */
    public function getAttributeTable(): array
    {
        $query = "SELECT product_combination.id, GROUP_CONCAT(attribute.value SEPARATOR ', ') AS `Werte` FROM attribute, product_combination JOIN product_attribute_combination ON product_attribute_combination.id_produkt_attribute = product_combination.id WHERE attribute.id = product_attribute_combination.attribute_id GROUP BY product_combination.id;";
        $data = DBAccess::selectQuery($query);
        return $data;
    }

    public static function createProduct(): int
    {
        $title = (string) Tools::get("title");
        $brand = (string) Tools::get("brand");
        $source = (string) Tools::get("source");
        $categoryId = (int) Tools::get("category");
        $price = (float) Tools::get("price");
        $purchasePrice = (float) Tools::get("purchasePrice");
        $description = (string) Tools::get("description");
        //$attributes = Tools::get("attributes");

        $newProductId = DBAccess::insertQuery("INSERT INTO produkt (Bezeichnung, Marke, Beschreibung, Einkaufspreis, Preis, einkaufs_id, id_category) VALUES (:title, :brand, :description, :purchasePrice, :price, :source, :idCategory)", [
            "title" => $title,
            "brand" => $brand,
            "description" => $description,
            "purchasePrice" => $purchasePrice,
            "price" => $price,
            "source" => $source,
            "idCategory" => $categoryId,
        ]);

        JSONResponseHandler::sendResponse(["id" => $newProductId]);
        return $newProductId;
    }

    public static function addCombinations(): void
    {
        $productId = (int) Tools::get("id");
        $combinations = Tools::get("combinations");
        $combinations = json_decode($combinations);

        $data = [];
        foreach ($combinations as $c) {
            $id_product_attribute = DBAccess::insertQuery("INSERT INTO product_combination (id_produkt) VALUES ($productId)");

            foreach ($c as $value) {
                array_push($data, [$id_product_attribute, $value]);
            }
        }

        DBAccess::insertMultiple("INSERT INTO product_attribute_combination (id_produkt_attribute, attribute_id) VALUES ", $data);
    }

    public static function searchInProducts($searchQuery)
    {
        $products = DBAccess::selectQuery("SELECT Nummer, Bezeichnung, Beschreibung FROM produkt");
        $mostSimilarProducts = array();

        foreach ($products as $product) {
            self::calculateSimilarity($mostSimilarProducts, $searchQuery, $product['Bezeichnung'], $product['Nummer']);
            self::calculateSimilarity($mostSimilarProducts, $searchQuery, $product['Beschreibung'], $product['Nummer']);
        }

        self::sortByPercentage($mostSimilarProducts);
        $mostSimilarProducts = self::filterByPercentage($mostSimilarProducts);

        return array_slice($mostSimilarProducts, 0, 10);
    }

    private static function sortByPercentage(&$mostSimilarProducts): void
    {
        usort($mostSimilarProducts, fn($a, $b) => $b[1] <=> $a[1]);
    }

    private static function filterByPercentage($mostSimilarProducts)
    {
        $filteredArray = array();
        foreach ($mostSimilarProducts as $product) {
            if (end($filteredArray)[0] == $product[0]) {
                if ($product[1] > end($filteredArray)[1]) {
                    $filteredArray[sizeof($filteredArray) - 1] = $product;
                }
            } else {
                array_push($filteredArray, $product);
            }
        }

        return $filteredArray;
    }

    private static function calculateSimilarity(&$mostSimilarProducts, $searchQuery, $text, $nummer): void
    {
        similar_text($searchQuery, $text, $percentage);
        array_push($mostSimilarProducts, array($nummer, $percentage));
    }

    public static function addSource(): void
    {
        $name = Tools::get("name");
        $desc = Tools::get("desc");

        if ($name != "" && $desc != "") {
            $id = DBAccess::insertQuery("INSERT INTO einkauf (name, description) VALUES (:name, :desc)", [
                "name" => $name,
                "desc" => $desc
            ]);

            JSONResponseHandler::sendResponse(["id" => $id]);
            return;
        }

        JSONResponseHandler::throwError(400, "Name und Beschreibung müssen ausgefüllt sein");
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function getSources(): array
    {
        return DBAccess::selectQuery("SELECT name, id FROM einkauf");
    }

    public static function getHTMLShortSummary(int $productnumber): void
    {
        $product = new Produkt($productnumber);
        $html = "<div><h3>{$product->bezeichnung}</h3><span>Anzahl <input value=\"1\" id=\"{$productnumber}_getAmount\"></span><button onclick=\"chooseProduct($productnumber)\">Auswählen</button></div>";
        echo $html;
    }

    /**
     * Returns a list of Product objects
     * @return Produkt[]
     */
    public static function getAllProducts(?int $categoryId = null): array
    {
        $products = [];

        if ($categoryId == null) {
            $sql = "SELECT Nummer FROM produkt";
        } else {
            $sql = "SELECT Nummer FROM produkt WHERE id_category = $categoryId";
        }

        $ids = DBAccess::selectQuery($sql);

        foreach ($ids as $id) {
            $products[] = new Produkt($id["Nummer"]);
        }

        return $products;
    }

    public static function getFiles(int $idProduct): string
    {
        $files = DBAccess::selectQuery("SELECT DISTINCT dateiname AS Datei, originalname, `date` AS Datum, typ as Typ 
			FROM dateien 
			LEFT JOIN dateien_produkte ON dateien_produkte.id_datei = dateien.id 
			WHERE dateien_produkte.id_produkt = $idProduct");

        for ($i = 0; $i < sizeof($files); $i++) {
            $link = Link::getResourcesShortLink($files[$i]['Datei'], "upload");

            $html = "<a target=\"_blank\" rel=\"noopener noreferrer\" href=\"$link\"><img class=\"img_prev_i\" src=\"$link\" width=\"40px\"><p class=\"img_prev\">{$files[$i]['originalname']}</p></a>";

            $files[$i]['Datei'] = $html;
        }

        $column_names = array(
            0 => array("COLUMN_NAME" => "Datei"),
            1 => array("COLUMN_NAME" => "Typ"),
            2 => array("COLUMN_NAME" => "Datum")
        );

        $t = new Table();
        $t->createByData($files, $column_names);
        $t->setType("dateien");
        $t->addActionButton("delete", $identifier = "id");

        return $t->getTable();
    }

    public static function update(): void
    {
        $productId = (int) Tools::get("id");
        $type = (string) Tools::get("type");
        $content = (string) Tools::get("content");

        $query = "";

        switch ($type) {
            case "productTitle":
                $query = "UPDATE produkt SET Bezeichnung = :content WHERE Nummer = :id";
                break;
            case "productDescription":
                $query = "UPDATE produkt SET Beschreibung = :content WHERE Nummer = :id";
                break;
            case "productPrice":
                $query = "UPDATE produkt SET Preis = :content WHERE Nummer = :id";
                break;
        }

        if ($query != "") {
            DBAccess::updateQuery($query, [
                "content" => $content,
                "id" => $productId
            ]);

            JSONResponseHandler::sendResponse(["message" => "success"]);
        } else {
            JSONResponseHandler::throwError(400, "Type not found");
        }
    }

    public static function addFiles(): void
    {
        $uploadHandler = new UploadHandler();
        $files = $uploadHandler->uploadMultiple();
        $productId = (int) Tools::get("id");

        $query = "INSERT INTO dateien_produkte (id_datei, id_produkt) VALUES ";
        $values = [];
        foreach ($files as $file) {
            $values[] = [(int) $file["id"], $productId];
        }

        DBAccess::insertMultiple($query, $values);
    }
}
