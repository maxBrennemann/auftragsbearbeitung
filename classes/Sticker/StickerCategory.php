<?php

namespace Classes\Sticker;

use MaxBrennemann\PhpUtilities\DBAccess;

class StickerCategory extends PrestashopConnection
{

    /**
     * sets the categories of a sticker by deleting all old categories and inserting the new ones
     *
     * @param int $stickerId
     * @param string $categories
     */
    public static function setCategories(int $stickerId, string $categories): void
    {
        $categories = json_decode($categories, true);
        $categories = array_unique($categories);

        $query = "DELETE FROM module_sticker_categories WHERE stickerId = :stickerId";
        DBAccess::deleteQuery($query, ["stickerId" => $stickerId]);

        $query = "INSERT INTO module_sticker_categories (stickerId, categoryId) VALUES (:stickerId, :categoryId)";
        foreach ($categories as $category) {
            DBAccess::insertQuery($query, ["stickerId" => $stickerId, "categoryId" => $category]);
        }
    }

    /**
     * gets the categories of a sticker
     *
     * @param int $stickerId
     * @return array<mixed, mixed>
     */
    public static function getCategoriesForSticker(int $stickerId): array
    {
        $query = "SELECT categoryId FROM module_sticker_categories WHERE stickerId = :stickerId";
        $categories = DBAccess::selectQuery($query, ["stickerId" => $stickerId]);

        $categories = array_column($categories, "categoryId");
        $categories = array_map(function ($category) {
            return (int) $category;
        }, $categories);

        return $categories;
    }

    /**
     * sends a request to chatGPT and returns the response,
     * gets a suggestion for categories for a sticker
     *
     * @param string $articleName
     * @param int $category
     */
    public static function getCategoriesSuggestion(string $articleName, int $category = 13): string
    {
        return "";
    }

    /**
     * @param int $startCategory
     * @return array<mixed, mixed>
     */
    public static function getCategories(int $startCategory): array
    {
        $cachedCategories = self::getCachedCategoryTree($startCategory);

        if ($cachedCategories !== false) {
            return $cachedCategories;
        }

        $categories = self::getCategoryTreeFromShop($startCategory);
        self::cacheCategoryTree($categories, $startCategory);

        return $categories;
    }

    /**
     * gets the category tree from the shop
     *
     * @param int $startCategory
     *
     * @return array<string, mixed>
     */
    private static function getCategoryTreeFromShop(int $startCategory): array
    {
        $stickerCategory = new StickerCategory();

        $xml = $stickerCategory->getXML("categories/$startCategory");
        $categories = $xml->children()->children();

        $categoriesSimplified = [
            "id" => (int) $categories->id,
            "name" => (string) $categories->name->language[0],
        ];
        $categoriesSimplified["children"] = [];

        /* warum es hier so kompliziert aufgerufen wird, weiß ich nicht, aber es funktioniert */
        foreach ($categories->associations->categories->category as $category) {
            $categoriesSimplified["children"][] = self::getCategoryTreeFromShop((int) $category->id);
        }

        return $categoriesSimplified;
    }

    /**
     * checks if the category tree is cached and if its last update is not older than 2 weeks
     *
     * @param int $startCategory
     *
     * @return array<mixed, mixed>|false
     */
    private static function getCachedCategoryTree(int $startCategory): array|false
    {
        if (!file_exists('cache/modules/sticker/categories')) {
            mkdir('cache/modules/sticker/categories', 0777, true);
        }

        $filename = 'cache/modules/sticker/categories/' . $startCategory . '.json';

        if (file_exists($filename) === false) {
            return false;
        }

        if (time() - filemtime($filename) > 2 * 7 * 24 * 60 * 60) {
            return false;
        }

        $cachedCategories = file_get_contents($filename);
        if ($cachedCategories === false) {
            return false;
        }

        return json_decode($cachedCategories, true);
    }

    /**
     * caches the category tree in a json file
     *
     * @param array<string, mixed> $categories
     * @param int $startCategory
     */
    private static function cacheCategoryTree(array $categories, int $startCategory): void
    {
        $filename = 'cache/modules/sticker/categories/' . $startCategory . '.json';

        if (is_writable($filename) === false) {
            return;
        }

        file_put_contents($filename, json_encode($categories));
    }
}
