<?php

require_once('classes/project/modules/sticker/PrestashopConnection.php');
require_once('classes/project/modules/sticker/StickerExport.php');
require_once('classes/project/modules/sticker/StickerChangelog.php');
require_once('classes/project/modules/sticker/Sticker.php');

class StickerTagManager extends PrestashopConnection implements StickerExport {

    private $idProduct;
    private $tags;

    function __construct($idProduct) {
        $query = "SELECT * FROM module_sticker_tags t JOIN module_sticker_sticker_tag st ON st.id_tag = t.id WHERE st.id_sticker = $idProduct";
        $this->tags = DBAccess::selectQuery($query);
    }

    public function get() {
        $tagsContent = [];
        foreach ($this->tags as $t) {
            $tagsContent[] = $t["content"];
        }

        return $tagsContent;
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
        $query = "SELECT id FROM module_sticker_tags WHERE content = :content";
        $result = DBAccess::selectQuery($query, ["content" => $content]);

        if ($result != null) {
            $id = $result["id"];
        } else {
            $query = "INSERT INTO module_sticker_tags (id_tag_shop, content) VALUES (:id_tag_shop, :content)";
            $idTagShop = $this->getTagIdFromShop($content);
            $id = DBAccess::insertQuery($query, ["id_tag_shop" => $idTagShop, "content" => $content]);
        }

        $query = "INSERT INTO module_sticker_sticker_tag (id_tag, id_sticker) VALUES (:id_tag, :id_sticker)";
        DBAccess::insertQuery($query, ["id_tag" => $id, "id_sticker" => $this->idProduct]);
        
        /* write to changelog */
        /* TODO: testen, ob es funktioniert, wenn sticker data nicht direkt mit der anderen tabelle zusammenhängt */
        StickerChangelog::log($this->idProduct, "", $id, "module_sticker_tags", "content", $content);
    }

    /* https://stackoverflow.com/questions/35975677/prestashop-webservice-add-products-tags-and-attachment-document */
    private function getTagIdFromShop($tag) {
        /* check if tag exists */
        $xml = $this->getXML("tags?filter[name]=$tag&limit=1");

        $resources = $xml->children()->children();
        if (!empty($resources)) {
            $attributes = $resources->tag->attributes();
            return $attributes['id'];
        }
    
        /* add a new tag */
        $xml = $this->getXML("tags?schema=synopsis");
        $resources = $xml->children()->children();
    
        unset($resources->id);
        $resources->{'name'} = $tag;
        $resources->{'id_lang'} = "de";
    
        $opt = array(
            'resource' => 'tags',
            'postXml' => $xml->asXML()
        );

        $this->addXML($opt);
        $id = $this->xml->product->id;
        return $id;
    }

    /**
     * iterates over all three product categories and adds tags to the specific product if
     * they don't exist
     */
    public function saveChanges(Sticker $products) {
        foreach ($products as $product) {
            $productId = $product->getId();
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
    }
}

?>