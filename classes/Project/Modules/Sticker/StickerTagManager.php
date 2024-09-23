<?php

namespace Classes\Project\Modules\Sticker;

class StickerTagManager extends PrestashopConnection
{

    private $idSticker;
    private $idProductReference;
    private $tags;
    private $title;

    function __construct(int $idSticker, String $title = "")
    {
        $query = "SELECT * FROM module_sticker_tags t JOIN module_sticker_sticker_tag st ON st.id_tag = t.id WHERE st.id_sticker = $idSticker";
        $this->tags = DBAccess::selectQuery($query);

        $this->idSticker = $idSticker;

        if ($title == "") {
            $sticker = new Sticker($idSticker);
            $title = $sticker->getName();
        }
        $this->title = $title;
    }

    public function setProductId($idProductReference)
    {
        $this->idProductReference = $idProductReference;
    }

    public function get()
    {
        $tagsContent = [];

        foreach ($this->tags as $t) {
            $tagsContent[] = $t["content"];
        }

        return $tagsContent;
    }

    public function getTagsHTML()
    {
        $queries = explode(" ", $this->title);
        $suggestionTags = [];

        foreach ($queries as $query) {
            $tags = $this->getSynonyms($query);
            $suggestions = array_slice($tags, 0, 3);
            array_push($suggestionTags, ...$suggestions);
        }

        insertTemplate('classes/project/modules/sticker/views/showTagsView.php', [
            "tags" => $this->tags,
            "suggestionTags" => $suggestionTags,
        ]);
    }

    public function getTagIds()
    {
        $tagIds = [];
        
        foreach ($this->tags as $t) {
            $id_tag_shop = $t["id_tag_shop"];

            if ($id_tag_shop == "" || $id_tag_shop == 0) {
                $id_tag_shop = $this->getTagIdFromShop($t["content"]);
                $query = "UPDATE module_sticker_tags SET id_tag_shop = :id_tag_shop WHERE id = :id";
                DBAccess::updateQuery($query, ["id_tag_shop" => $id_tag_shop, "id" => $t["id"]]);
            }
            $tagIds[] = $id_tag_shop;
        }

        return $tagIds;
    }

    public function remove(int $id)
    {
    }

    /**
     * adds a tag to a product,
     * this function does not sync tags with the shop
     */
    public function add(String $content)
    {
        if (strlen($content) > 32) {
            return;
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
        DBAccess::insertQuery($query, ["id_tag" => $id, "id_sticker" => $this->idSticker]);

        /* write to changelog */
        /* TODO: testen, ob es funktioniert, wenn sticker data nicht direkt mit der anderen tabelle zusammenhängt */
        StickerChangelog::log($this->idSticker, 0, $id, "module_sticker_tags", "content", $content);

        return $id;
    }

    /* https://stackoverflow.com/questions/35975677/prestashop-webservice-add-products-tags-and-attachment-document */
    private function getTagIdFromShop($tag): int
    {
        /* check if tag exists */
        $tagEncoded = str_replace(" ", "+", $tag);

        try {
            $xml = $this->getXML("tags?filter[name]=$tagEncoded&limit=1");
            $resources = $xml->children()->children();

            if (!empty($resources)) {
                return (int) $resources->tag->attributes()->id;
            }
        } catch (PrestaShopWebserviceException $e) {
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
        } catch (PrestaShopWebserviceException $e) {
            echo $e->getMessage();
        }

        return -1;
    }

    /**
     * iterates over all three product categories and adds tags to the specific product if
     * they don't exist
     */
    public function saveChanges(Sticker $products)
    {
        foreach ($products as $product) {
            $productId = $product->getId();
            $this->saveTags();
            //$this->saveTags($productId);
        }
    }

    public function saveTagsXml(&$xml)
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

    public function saveTags()
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
        } catch (PrestaShopWebserviceException $e) {
            echo $e;
        }
    }

    public function getSynonyms($query)
    {
        if (!file_exists('cache/modules/sticker/tags')) {
            mkdir('cache/modules/sticker/tags', 0777, true);
        }

        @$cachedSynonyms = file_get_contents('cache/modules/sticker/tags/' . $query . '.json');
        if ($cachedSynonyms === false) {
            $ch = curl_init("https://www.openthesaurus.de/synonyme/search?q=$query&format=application/json");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($result, true);
            $synonyms = [];

            if ($result != null && $result["synsets"] != null) {
                foreach ($result["synsets"] as $set) {
                    foreach ($set["terms"] as $term) {
                        /* chatgpt reported the typo */
                        if (!in_array($term["term"], $synonyms) && strlen($term["term"]) <= 32) {
                            array_push($synonyms, $term["term"]);
                        }
                    }
                }
            }

            file_put_contents('cache/modules/sticker/tags/' . $query . '.json', json_encode($synonyms));
            return $synonyms;
        } else {
            $cachedSynonyms = json_decode($cachedSynonyms);
            return $cachedSynonyms;
        }
    }

    /**
     * gets called when an ajax request is fired,
     * loads more synonyms
     */
    public static function loadMoreSynonyms()
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

        if ($tag == null || $tag == "") {
            echo -1;
            return;
        }

        $stickerTagManager = new StickerTagManager($id);

        JSONResponseHandler::sendResponse($stickerTagManager->add($tag));
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
    public static function getTagId(String $tagContent): int
    {
        $query = "SELECT id FROM module_sticker_tags WHERE content = :content LIMIT 1;";
        $result = DBAccess::selectQuery($query, ["content" => $tagContent]);

        if ($result != null) {
            return $result[0]["id"];
        }
        return -1;
    }

    public static function addTagGroup(String $title): int
    {
        $query = "INSERT INTO module_sticker_sticker_tag_group (title) VALUES (:title)";
        $tagGroupId = DBAccess::insertQuery($query, ["title" => $title]);
        return $tagGroupId;
    }

    public static function addTagToTagGroup(String $tagContent, int $tagGroup)
    {
        // get tag Id
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
    public static function crawlAllTags()
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
                } catch (PrestaShopWebserviceException $e) {
                    Protocol::write($e->getMessage());
                }

                DBAccess::insertMultiple("INSERT INTO module_sticker_tags (id_tag_shop, `content`) VALUES ", $data);
            }
        } catch (PrestaShopWebserviceException $e) {
            Protocol::write($e->getMessage());
        }
    }

    public static function countTagOccurences()
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

    public static function getTagSuggestions()
    {
        $id = (int) Tools::get("id");
        $name = Tools::get("name");

        $stickerTagManager = new StickerTagManager($id, $name);
        $stickerTagManager->getTagsHTML();
    }
}
