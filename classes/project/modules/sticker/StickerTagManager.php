<?php

require_once('classes/project/modules/sticker/PrestashopConnection.php');
require_once('classes/project/modules/sticker/StickerExport.php');
require_once('classes/project/modules/sticker/StickerChangelog.php');
require_once('classes/project/StickerImage.php');
require_once('classes/project/modules/sticker/Sticker.php');

class StickerTagManager extends PrestashopConnection implements StickerExport {

    private $idSticker;
    private $tags;
    private $title;

    function __construct(int $idSticker, String $title = "") {
        $query = "SELECT * FROM module_sticker_tags t JOIN module_sticker_sticker_tag st ON st.id_tag = t.id WHERE st.id_sticker = $idSticker";
        $this->tags = DBAccess::selectQuery($query);
        $this->idSticker = $idSticker;

        if ($title == "") {
            $sticker = new StickerImage($idSticker);
            $title = $sticker->getName();
        }
        $this->title = $title;
    }

    public function get() {
        $tagsContent = [];
        foreach ($this->tags as $t) {
            $tagsContent[] = $t["content"];
        }

        return $tagsContent;
    }

    public function getTagsHTML() {
        $tagsHTML = "<dl class=\"tagList\">";

        foreach ($this->tags as $tag) {
            $id = $tag["id"];
            $content = $tag["content"];
            $tagsHTML .= "<dt>$content<span class=\"remove\" data-tag=\"$id\">x</span></dt>";
        }

        foreach (explode(" ", $this->title) as $query) {
            $tags = array_slice($this->getSynonyms($query), 0, 3);
            foreach ($tags as $tag) {
                $tagsHTML .= "<dt class=\"suggestionTag\">$tag<span class=\"remove\">x</span></dt>";
            }
        }

        return $tagsHTML . "</dl>";
    }

    public function getTagIds() {
        $tagIds = [];
        foreach ($this->tags as $t) {
            $id_tag_shop = $t["id_tag_shop"];

            if ($id_tag_shop == "" || $id_tag_shop == 0) {
                $id_tag_shop = $this->getTagIdFromShop($t["content"]);
                $query = "UPDATE TABLE module_sticker_tags SET id_tag_shop = :id_tag_shop WHERE id = :id";
                DBAccess::updateQuery($query, ["id_tag_shop" => $id_tag_shop, "id" => $t["id"]]);
            }
            $tagIds[] = $id_tag_shop;
        }

        return $tagIds;
    }

    public function remove(int $id) {

    }

    /**
     * adds a tag to a product,
     * this function does not sync tags with the shop
     */
    public function add(String $content) {
        if (strlen($content) > 32) {
            return;
        }

        $query = "SELECT id FROM module_sticker_tags WHERE content = :content";
        $result = DBAccess::selectQuery($query, ["content" => $content]);

        if ($result != null) {
            $id = $result["id"];
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
        StickerChangelog::log($this->idSticker, "", $id, "module_sticker_tags", "content", $content);

        return $id;
    }

    /* https://stackoverflow.com/questions/35975677/prestashop-webservice-add-products-tags-and-attachment-document */
    private function getTagIdFromShop($tag): int {
        /* check if tag exists */
        $tagEncoded = str_replace(" ", "+", $tag);
        $xml = $this->getXML("tags?filter[name]=$tagEncoded&limit=1");

        $resources = $xml->children()->children();
        if (!empty($resources)) {
            return (int) $resources->tag->attributes()->id;
        }
    
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
        $id = $this->xml->product->id;
        return (int) $id;
    }

    /**
     * iterates over all three product categories and adds tags to the specific product if
     * they don't exist
     */
    public function saveChanges(Sticker $products) {
        foreach ($products as $product) {
            $productId = $product->getId();
            $this->saveTags($productId);
        }
    }

    public function saveTags($productId) {
        $xml = $this->getXML("product/$productId");
        $tags = $xml->{'assiciations'};

        $tagIds = [];
        /* read all existing tags from shop for this product */
        foreach ($tags->tag as $tag) {
            $tagIds = $tag->{'id'};
        }

        /* insert new tag if it does not exist */
        foreach ($this->getTagIds() as $id) {
            if (!in_array($id, $tagIds)) {
                $tag = $tags->addChild("tag");
                $tag->addChild("id", $id);
            }
        }

        $opt = array(
            'resource' => 'products',
            'putXml' => $xml->asXML(),
            'id' => $productId,
        );
        $this->editXML($opt);
    }

    public function getSynonyms($query) {
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
                        if (!in_array($term["term"], $synonyms) && strlen($term["term"] <= 32)) {
                            array_push($synonyms, $term["term"]);
                        }
                    }
                }
            }

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
    public static function loadMoreSynonyms() {
        
    }

    /**
     * gets called when an ajax request is fired
     */
    public static function addTag() {
        $id = (int) getParameter("id", "POST");
        $tag = getParameter("tag", "POST");

        if ($tag == null || $tag == "") {
            echo -1;
            return;
        }

        $stickerTagManager = new StickerTagManager($id);
        echo $stickerTagManager->add($tag);
    }

    /**
     * gets called when an ajax request is fired
     */
    public static function removeTag() {
        $id = getParameter("id", "POST");
        $tag = getParameter("tag", "POST");

        $tagId = "SELECT id FROM module_sticker_tags WHERE content = '$tag'";
        $tagId = DBAccess::selectQuery($tagId);
        if ($tagId != null) {
            $tagId = $tagId[0]["id"];

            $query = "DELETE FROM module_sticker_sticker_tag WHERE id_tag = $tagId AND id_sticker = $id";
            DBAccess::deleteQuery($query);
        } else {
            echo "not found";
        }
    }

}

?>