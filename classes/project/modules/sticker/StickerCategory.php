<?php

require_once('classes/project/modules/sticker/PrestashopConnection.php');

class StickerCategory extends PrestashopConnection {

    public static function getChildCategoriesNested($startCategory) {
        $stickerCategory = new StickerCategory();
        $xml = $stickerCategory->getXML("categories/$startCategory");
        $categories = $xml->children()->children();
        $categoriesSimplified = [
            "id" => (int) $categories->id,
            "name" => (String) $categories->name->language[0],
        ];
        $categoriesSimplified["children"] = [];

        // warum es hier so kompliziert aufgerufen wird, weiß ich nicht, aber es funktioniert
        foreach ($categories->associations->categories->category as $category) {  
            $categoriesSimplified["children"][] = self::getChildCategoriesNested((int) $category->id);
        }

        return $categoriesSimplified;
    }

}

?>