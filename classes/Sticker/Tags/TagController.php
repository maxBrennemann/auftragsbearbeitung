<?php

namespace Classes\Sticker\Tags;

use Classes\Protocol;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class TagController
{
    /**
     * @param string $query
     * @throws \RuntimeException
     * @return array<int, string>
     */
    private static function getSynonyms(string $query): array
    {
        $cacheDir = "cache/modules/sticker/tags";
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

    public static function addTag(): void
    {

    }

    public static function removeTag(): void
    {

    }

    public static function addTagGroup(): void
    {

    }

    public static function addTagToGroup(): void
    {

    }

    public static function getTagSuggestions(): void
    {
        $id = (int) Tools::get("id");
        $title = Tools::get("title");

        $queries = explode(" ", $title);
        $suggestionTags = [];

        foreach ($queries as $query) {
            $tags = self::getSynonyms($query);
            $suggestions = array_slice($tags, 0, 3);
            $suggestionTags = [...$suggestionTags, ...$suggestions];
        }

        $tags = new TagRepository($id, $title);
        $tagTemplate = \Classes\Controller\TemplateController::getTemplate("sticker/showTags", [
            "tags" => $tags->get(),
            "suggestionTags" => $suggestionTags,
        ]);

        JSONResponseHandler::sendResponse([
            "template" => $tagTemplate,
        ]);
    }
}
