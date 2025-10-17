<?php

namespace Src\Classes\Sticker;

use Src\Classes\Protocol;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class StickerTagManager extends PrestashopConnection
{
    private int $idSticker;
    private int $idProductReference;

    /** @var array<int, mixed> */
    private array $tags;

    public function __construct(int $idSticker, string $title = "")
    {
        $query = "SELECT * FROM module_sticker_tags t 
            JOIN module_sticker_sticker_tag st 
                ON st.id_tag = t.id 
            WHERE st.id_sticker = $idSticker";
        $this->tags = DBAccess::selectQuery($query);

        $this->idSticker = $idSticker;

        if ($title == "") {
            $sticker = new Sticker($idSticker);
            $title = $sticker->getName();
        }
    }

    public function setProductId(int $idProductReference): void
    {
        $this->idProductReference = $idProductReference;
    }

    /**
     * @return string[]
     */
    public function get(): array
    {
        $tagsContent = [];

        foreach ($this->tags as $t) {
            $tagsContent[] = $t["content"];
        }

        return $tagsContent;
    }

    public static function getTagsHTML(): void
    {
        $id = (int) Tools::get("id");
        $title = Tools::get("title");
        $queries = explode(" ", $title);
        $suggestionTags = [];

        foreach ($queries as $query) {
            if ($query == "") {
                continue;
            }
            $tags = self::getSynonyms($query);
            $suggestions = array_slice($tags, 0, 3);
            array_push($suggestionTags, ...$suggestions);
        }

        $stickerTagManager = new StickerTagManager($id, $title);
        $tagTemplate = \Src\Classes\Controller\TemplateController::getTemplate("sticker/showTags", [
            "tags" => $stickerTagManager->tags,
            "suggestionTags" => $suggestionTags,
        ]);

        JSONResponseHandler::sendResponse([
            "template" => $tagTemplate,
        ]);
    }

    /**
     * @return array<int, mixed>
     */
    public function getTagIds(): array
    {
        $tagIds = [];

        foreach ($this->tags as $t) {
            $id_tag_shop = $t["id_tag_shop"];

            if ($id_tag_shop == "" || $id_tag_shop == 0) {
                $id_tag_shop = $this->getTagIdFromShop($t["content"]);
                $query = "UPDATE module_sticker_tags SET id_tag_shop = :id_tag_shop WHERE id = :id";
                DBAccess::updateQuery($query, [
                    "id_tag_shop" => $id_tag_shop,
                    "id" => $t["id"]
                ]);
            }
            $tagIds[] = $id_tag_shop;
        }

        return $tagIds;
    }

    public function remove(int $id): void
    {
    }

    /**
     * adds a tag to a product,
     * this function does not sync tags with the shop
     */
    public function add(string $content): ?int
    {
        if (strlen($content) > 32) {
            return null;
        }

        $result = self::getTagId($content);

        if ($result != -1) {
            $id = $result;
        } else {
            $query = "INSERT INTO module_sticker_tags (id_tag_shop, content) VALUES (:id_tag_shop, :content);";

            $id_tag_shop = $this->getTagIdFromShop($content);
            $parameters = [
                "id_tag_shop" => $id_tag_shop,
                "content" => $content,
            ];

            $id = DBAccess::insertQuery($query, $parameters);
        }

        $query = "INSERT INTO module_sticker_sticker_tag (id_tag, id_sticker) VALUES (:id_tag, :id_sticker)";
        DBAccess::insertQuery($query, [
            "id_tag" => $id,
            "id_sticker" => $this->idSticker
        ]);

        /* write to changelog */
        /* TODO: testen, ob es funktioniert, wenn sticker data nicht direkt mit der anderen tabelle zusammenhängt */
        StickerChangelog::log($this->idSticker, 0, $id, "module_sticker_tags", "content", $content);

        return $id;
    }

    /* https://stackoverflow.com/questions/35975677/prestashop-webservice-add-products-tags-and-attachment-document */
    private function getTagIdFromShop(string $tag): int
    {
        /* check if tag exists */
        $tagEncoded = str_replace(" ", "+", $tag);

        try {
            $xml = $this->getXML("tags?filter[name]=$tagEncoded&limit=1");
            $resources = $xml->children()->children();

            if (!empty($resources)) {
                return (int) $resources->tag->attributes()->id;
            }
        } catch (\PrestaShopWebserviceException $e) {
            echo $e->getMessage();
        }

        try {
            /* add a new tag */
            $xml = $this->getXML("tags?schema=synopsis");
            $resources = $xml->children()->children();

            unset($resources->id);
            $resources->{'name'} = $tag;
            /* language_id for de is 1 */
            $resources->{'id_lang'} = 1;

            $opt = array(
                'resource' => 'tags',
                'postXml' => $xml->asXML()
            );

            $this->addXML($opt);
            $id = $this->xml->tag->id;
            return (int) $id;
        } catch (\PrestaShopWebserviceException $e) {
            echo $e->getMessage();
        }

        return -1;
    }

    public function saveTagsXml(mixed &$xml): void
    {
        $product_reference = $xml->children()->children();
        $associations = $product_reference->{'associations'};

        $tagIds = [];
        /* read all existing tags from shop for this product */
        if ($associations->{'tags'} != null) {
            $tags = $associations->{'tags'};
            foreach ($tags as $tag) {
                /*
                 * I have basically no idea why it is this nested for tags, couldn't figure it out,
                 * thats why I left all this xml code in here
                 */
                if ($tag->{'tag'}) {
                    $tagIds[] = (int) $tag->tag->{'id'};
                }
            }
        }

        unset($product_reference->associations->tags);
        $tags = $product_reference->associations->addChild("tags");
        $tagsAll = array_merge($tagIds, $this->getTagIds());

        // add each "tag" node with its "id" child node to the "tags" node
        foreach ($tagsAll as $id) {
            $tagNode = $tags->addChild('tag');
            $tagNode->addChild('id', (string) $id);
        }
    }

    public function saveTags(): void
    {
        $xml = $this->getXML("products/$this->idProductReference");
        $product_reference = $xml->children()->children();

        unset($product_reference->manufacturer_name);
        unset($product_reference->quantity);

        $associations = $product_reference->{'associations'};

        $tagIds = [];
        /* read all existing tags from shop for this product */
        if ($associations->{'tags'} != null) {
            $tags = $associations->{'tags'};
            foreach ($tags as $tag) {
                /*
                 * I have basically no idea why it is this nested for tags, couldn't figure it out,
                 * thats why I left all this xml code in here
                 */
                if ($tag->{'tag'}) {
                    $tagIds[] = (int) $tag->tag->{'id'};
                }
            }
        }

        unset($product_reference->associations->tags);
        $tags = $product_reference->associations->addChild("tags");
        $tagsAll = array_merge($tagIds, $this->getTagIds());

        // add each "tag" node with its "id" child node to the "tags" node
        foreach ($tagsAll as $id) {
            $tagNode = $tags->addChild('tag');
            $tagNode->addChild('id', (string) $id);
        }

        try {
            $opt = array(
                'resource' => 'products',
                'putXml' => $xml->asXML(),
                'id' => $this->idProductReference,
            );
            $this->editXML($opt);
        } catch (\PrestaShopWebserviceException $e) {
            echo $e;
        }
    }

    /**
     * @return array<int, string>
     */
    public static function getSynonyms(string $query): array
    {
        $cacheDir = "storage/cache/modules/sticker/tags";
        $sanitizedQuery = preg_replace('/[^a-zA-Z0-9_-]/', '_', $query);
        $cacheFile = "$cacheDir/$sanitizedQuery.json";

        if (!is_dir($cacheDir) && !mkdir($cacheDir, 0777, true)) {
            Protocol::write("Failed to create cache directory: $cacheDir", "", "ERROR");
            throw new \RuntimeException("Failed to create cache directory: $cacheDir");
        }

        if (file_exists($cacheFile)) {
            $cached = file_get_contents($cacheFile);
            if ($cached !== false) {
                $decoded = json_decode($cached, true);
                return is_array($decoded) ? $decoded : [];
            }
        }

        $client = new Client([
            "base_uri" => "https://www.openthesaurus.de/",
            "timeout" => 5.0,
        ]);

        try {
            $response = $client->request("GET", "synonyme/search", [
                "query" => [
                    "q" => $query,
                    "format" => "application/json",
                ],
                "headers" => [
                    "Accept" => "application/json",
                ]
            ]);

            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);
        } catch (RequestException $e) {
            Protocol::write("Guzzle error while fetching synonyms.", $e->getMessage(), "ERROR");
            return [];
        }

        $synonyms = [];
        if (!empty($data["synsets"])) {
            foreach ($data["synsets"] as $set) {
                foreach ($set["terms"] ?? [] as $term) {
                    $termText = $term["term"] ?? "";
                    if (strlen($termText) <= 32 && !in_array($termText, $synonyms, true)) {
                        $synonyms[] = $termText;
                    }
                }
            }
        }

        file_put_contents($cacheFile, json_encode($synonyms, JSON_UNESCAPED_UNICODE));
        return $synonyms;
    }

    /**
     * gets called when an ajax request is fired,
     * loads more synonyms
     */
    public static function loadMoreSynonyms(): void
    {
    }

    /**
     * gets called when an ajax request is fired
     *
     * @return void
     */
    public static function addTag(): void
    {
        $id = (int) Tools::get("id");
        $tag = Tools::get("tag");

        if ($tag === null || $tag === "") {
            echo -1;
            return;
        }

        $stickerTagManager = new StickerTagManager($id);

        JSONResponseHandler::sendResponse([
            $stickerTagManager->add($tag)
        ]);
    }

    /**
     * gets called when an ajax request is fired
     */
    public static function removeTag(): void
    {
        $id = Tools::get("id");
        $tag = Tools::get("tag");

        /* get tag id */
        $query = "SELECT id FROM module_sticker_tags WHERE content = :tag LIMIT 1;";
        $tagId = DBAccess::selectQuery($query, [
            "tag" => $tag,
        ]);

        if ($tagId == null) {
            JSONResponseHandler::returnNotFound();
        }

        $tagId = (int) $tagId[0]["id"];

        /* remove tag from sticker */
        $query = "DELETE FROM module_sticker_sticker_tag WHERE id_tag = :tagId AND id_sticker = :id;";
        DBAccess::deleteQuery($query, [
            "tagId" => $tagId,
            "id" => $id,
        ]);

        JSONResponseHandler::sendResponse(["status" => "success"]);
    }

    /**
     * returns the id of a tag by its content,
     * retruns -1 if not found
     */
    public static function getTagId(string $tagContent): int
    {
        $query = "SELECT id FROM module_sticker_tags WHERE content = :content LIMIT 1;";
        $result = DBAccess::selectQuery($query, ["content" => $tagContent]);

        if ($result != null) {
            return (int) $result[0]["id"];
        }
        return -1;
    }

    public static function addTagGroup(string $title): int
    {
        $query = "INSERT INTO module_sticker_sticker_tag_group (title) VALUES (:title)";
        $tagGroupId = DBAccess::insertQuery($query, ["title" => $title]);
        return $tagGroupId;
    }

    public static function addTagToTagGroup(string $tagContent, int $tagGroup): void
    {
        $tagId = self::getTagId($tagContent);
        $query = "INSERT INTO (module_sticker_sticker_tag_group_match) (idGroup, idTag) VALUES (:tagGroup, :tag)";
        DBAccess::insertQuery($query, [
            "tagGroup" => $tagGroup,
            "tag" => $tagId
        ]);
    }

    /**
     * Die Funktion geht alle Tags im Shop durch und speichert sie einzeln ab.
     * Wichtig: TagContent ist unique, wie im Shop.
     * Deshalb müsste es später vielleicht ein REPLACE INTO werden?
     * Oder alles löschen und dann neu crawlen?
     */
    public static function crawlAllTags(): void
    {
        $crawler = new PrestashopConnection();

        try {
            $xml = $crawler->getXML("tags");
            $tags = $xml->children()->children();

            foreach ($tags as $tag) {
                $id = (int) $tag->attributes()->{"id"};

                $innerCrawler = new PrestashopConnection();
                $data = [];

                try {
                    $xml = $innerCrawler->getXML("tags/$id");
                    $tag = $xml->children()->children();

                    $name = (string) $tag->{"name"};
                    $data[] = [
                        "int" => $id,
                        "string" => $name,
                    ];
                } catch (\PrestaShopWebserviceException $e) {
                    Protocol::write($e->getMessage());
                }

                DBAccess::insertMultiple("INSERT INTO module_sticker_tags (id_tag_shop, `content`) VALUES ", $data);
            }
        } catch (\PrestaShopWebserviceException $e) {
            Protocol::write($e->getMessage());
        }
    }

    /**
     * @return array<int, mixed>
     */
    public static function countTagOccurences(): array
    {
        $query = "SELECT COUNT(t.id_tag_shop) AS occurences, t.content 
            FROM module_sticker_tags t 
            LEFT JOIN module_sticker_sticker_tag c 
                ON t.id = c.id_tag 
            GROUP BY t.id_tag_shop 
            ORDER BY `occurences` 
            DESC;";
        $result = DBAccess::selectQuery($query);

        return $result;
    }

    public static function getTagOverview(): void
    {
        $query = "SELECT COUNT(id) AS tagCount, content FROM module_sticker_tags GROUP BY content ORDER BY tagCount;";
        $data = DBAccess::selectQuery($query);

        JSONResponseHandler::sendResponse($data);
    }
}
