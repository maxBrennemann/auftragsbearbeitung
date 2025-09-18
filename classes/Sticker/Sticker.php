<?php

namespace Classes\Sticker;

use Classes\Link;
use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use SimpleXMLElement;

/**
 * stellt allgemeine Stickerfunktionen zur Verfügung, ist die Elternklasse von
 * Aufkleber, Wandtattoo, AufkleberWandtatoo und Textil
 */
class Sticker extends PrestashopConnection
{
    protected int $idSticker;
    protected int $idProduct;

    protected string $name;

    /** @var array<string, mixed> */
    protected array $stickerData;

    /** @var array<mixed, mixed> */
    protected array $additionalData;

    protected StickerImage $imageData;

    protected string $instanceType = "sticker";

    public function __construct(int $idSticker)
    {
        parent::__construct();

        $this->idSticker = $idSticker;
        $this->stickerData = DBAccess::selectQuery("SELECT * FROM module_sticker_sticker_data WHERE id = :idSticker LIMIT 1;", ["idSticker" => $idSticker]);

        if ($this->stickerData == null) {
            throw new \Exception("Sticker does not exist.");
        }
        $this->stickerData = $this->stickerData[0];

        if ($this->stickerData["additional_data"] == null) {
            $this->additionalData = [];
        } else {
            $this->additionalData = json_decode($this->stickerData["additional_data"], true);
        }

        $this->imageData = new StickerImage($idSticker);
        $this->instanceType = "sticker";
    }

    public function getIdProduct(): int
    {
        if ($this->idProduct != null) {
            return $this->idProduct;
        }

        if (isset($this->additionalData["products"][$this->instanceType])) {
            $this->idProduct = (int) $this->additionalData["products"][$this->instanceType]["id"];
        } else {
            $this->idProduct = 0;
        }

        return $this->idProduct;
    }

    public function getName(): String
    {
        return $this->stickerData["name"];
    }

    public function getId(): int
    {
        return $this->idSticker;
    }

    public function getType(): string
    {
        return $this->instanceType;
    }

    public function getDirectory(): string
    {
        return $this->stickerData["directory_name"];
    }

    public function getIsMarked(): bool
    {
        return $this->stickerData["is_marked"];
    }

    public function getIsRevised(): bool
    {
        return $this->stickerData["is_revised"];
    }

    public function getAdditionalInfo(): string
    {
        return $this->stickerData["additional_info"];
    }

    public function isInShop(): bool
    {
        return false;
    }

    protected function checkIsInShop(string $type): bool
    {
        if ($this->additionalData != null) {
            if (isset($this->additionalData["products"]) && isset($this->additionalData["products"][$type])) {
                return true;
            }
        }

        return false;
    }

    public function getShopLink(): string
    {
        throw new \LogicException("Generic Sticker does not have a specific URL.");
    }

    protected function getShopLinkHelper(string $type): string
    {
        if ($this->additionalData != null) {
            if (isset($this->additionalData["products"]) && isset($this->additionalData["products"][$type])) {
                return $this->additionalData["products"][$type]["link"] ?? "";
            }
        }

        return "#";
    }

    public function getAltTitle(string $type = ""): string
    {
        if ($type == "") {
            return "";
        }

        if (isset($this->additionalData["products"])) {
            $prod = $this->additionalData["products"];
            if (isset($prod[$type]) && isset($prod[$type]["altTitle"])) {
                $altTitle = $prod[$type]["altTitle"];
                if ($altTitle == null) {
                    return "";
                }
                return $altTitle;
            }
        }
        return "";
    }

    public function getCreationDate(): string
    {
        return $this->stickerData["creation_date"];
    }

    public function getIdCategory(): int
    {
        throw new \LogicException("Generic Sticker does not have a category id.");
    }

    public function getBasePrice(): string
    {
        throw new \LogicException("Generic Sticker does not have a base price.");
    }

    public function getDescription(): string
    {
        return $this->getDescr($this->instanceType, "long");
    }

    public function getDescriptionShort(): string
    {
        return $this->getDescr($this->instanceType, "short");
    }

    protected function getDescr(string $target, string $type): string
    {
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

    public function getTags(): void {}

    public function getActiveStatus(): bool
    {
        if (isset($this->additionalData["products"][$this->instanceType])) {
            $ref = $this->additionalData["products"][$this->instanceType];
            if (isset($ref["status"])) {
                return $ref["status"] == 1;
            }
        }
        return true;
    }

    /**
     * @param string $name
     * @return array{status: string}
     */
    public function setName(string $name): array
    {
        $query = "UPDATE module_sticker_sticker_data SET `name` = :stickerName WHERE id = :idSticker";
        DBAccess::updateQuery($query, ["stickerName" => $name, "idSticker" => $this->getId()]);

        StickerChangelog::log($this->getId(), 0, $this->getId(), "module_sticker_sticker_data", "name", $name);

        return [
            "status" => "success"
        ];
    }

    public static function setDescription(): void
    {
        $id = (int) $_POST["id"];
        $type = (string) $_POST["type"];
        $target = (string) $_POST["target"];
        $content = (string) $_POST["content"];

        $query = "REPLACE INTO module_sticker_texts (id_sticker, `type`, `target`, content) VALUES (:id, :type, :target, :content);";
        DBAccess::updateQuery($query, [
            "id" => $id,
            "type" => $type,
            "target" => $target,
            "content" => $content,
        ]);

        //StickerChangelog::log($id, $target, 0, "module_sticker_texts", "content", $content);
        echo "success";
    }

    public function save(): ?string
    {
        throw new \LogicException("Generic Sticker does not have a save function.");
    }

    public function createCombinations(): void {}

    public function setCategory(): void {}

    public function delete(): void
    {
        $this->deleteXML("products", $this->getIdProduct());
    }

    /**
     * switches the product active status
     */
    public function toggleActiveStatus(): void
    {
        if ($this->getIdProduct() == 0) {
            return;
        }

        $xml = $this->getXML("products/" . $this->getIdProduct());
        $resource_product = $xml->children()->children();

        $active = (int) $resource_product->active;

        if ($active == 0) {
            $active = 1;
        } else {
            $active = 0;
        }

        $this->additionalData["products"][$this->instanceType]["status"] = $active;
        $this->saveAdditionalData();

        $resource_product->{"active"} = $active;
        unset($resource_product->manufacturer_name);
        unset($resource_product->quantity);

        $opt = array(
            'resource' => 'products',
            'putXml' => $xml->asXML(),
            'id' => $this->getIdProduct(),
        );
        $this->editXML($opt);

        /* TODO: implement toggle type and access stickershopdbcontroller */
        /* TODO: fo: implement status via db */
    }

    /**
     * @return int[]
     */
    private function getAccessoires(): array
    {
        $query = "SELECT id_product_reference FROM module_sticker_accessoires WHERE id_sticker = :idSticker AND `type` = :typeSticker";
        $result = DBAccess::selectQuery($query, [
            "idSticker" => $this->idSticker,
            "typeSticker" => $this->instanceType,
        ]);

        return array_map(fn($data): int => $data["id_product_reference"], $result);
    }

    /**
     * connects a list of products with the current product
     */
    public function connectAccessoires(): void
    {
        if ($this->getIdProduct() == 0) {
            return;
        }
        
        $xml = $this->getXML("products/" . $this->getIdProduct());

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
        $connectTo = $this->getAccessoires();
        foreach ($connectTo as $id) {
            if (!in_array($id, $existingAccessoires)) {
                $product = $accessoires->addChild("product");
                $product->addChild("id", (string) $id);
            }
        }

        $opt = array(
            'resource' => 'products',
            'putXml' => $xml->asXML(),
            'id' => $this->getIdProduct(),
        );
        $this->editXML($opt);
    }

    /**
     * @return array<int, array<int>>
     */
    public function getAttributes(): array
    {
        return [];
    }

    /**
     * @return array<int, mixed>
     */
    public function getPrices(): array
    {
        return [];
    }

    /**
     * @return array<int, mixed>
     */
    public function getPricesMatched(): array
    {
        return [];
    }

    /**
     * @return array<void>
     */
    public function getPurchasingPrices(): array
    {
        return [];
    }

    /**
     * @return float[]
     */
    public function getPurchasingPricesMatched(): array
    {
        return [];
    }

    /**
     * inserts a new sticker into the database and sets all its initial values
     * @param string $title the new sticker's name
     */
    public static function createNewSticker(string $title): void
    {
        /* insert sticker into database */
        $query = "INSERT INTO module_sticker_sticker_data (`name`) VALUES (:title)";
        $id = DBAccess::insertQuery($query, ["title" => $title]);
        $aufkleberWandtattoo = new AufkleberWandtattoo($id);
        $sizes = [
            20,
            50,
            100,
            150,
            200,
            250,
            300,
            400,
            500,
            600,
            700,
            800,
            900,
            1000,
            1100,
            1200
        ];
        foreach ($sizes as $size) {
            $price = $aufkleberWandtattoo->getPrice($size, 0, 1);
            $aufkleberWandtattoo->updatePrice($size, 0, $price);
        }

        /* sets exports defaults to true */
        $query = "INSERT INTO module_sticker_exports (idSticker, facebook, google, amazon, etsy, ebay, pinterest) VALUES ($id, -1, -1, -1, -1, -1, -1);";
        DBAccess::insertQuery($query);

        if ($id == 0 || !is_numeric($id)) {
            JSONResponseHandler::throwError(400, "Sticker could not be created.");
        } else {
            $link = Link::getPageLink("sticker") . "?id=" . $id;
            JSONResponseHandler::sendResponse(["status" => "success", "link" => $link]);
        }
    }

    private function saveAdditionalData(): void
    {
        $query = "UPDATE module_sticker_sticker_data SET `additional_data` = :additionalData WHERE id = :idSticker";
        DBAccess::updateQuery($query, [
            "additionalData" => json_encode($this->additionalData),
            "idSticker" => $this->getId()
        ]);
    }
}
