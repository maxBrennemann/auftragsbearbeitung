<?php

namespace Src\Classes\Project;

use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class SearchController
{

    /** @var array<string, mixed> */
    private array $searches = [];

    /** @var array<int, array{data: mixed, type:string, score:int}> */
    private array $results = [];
    private int $limit = 15;

    /**
     * @param string $table
     * @param array{array<mixed, mixed>, string} $parsedQuery
     * @return void
     */
    private function add(string $table, array $parsedQuery): void
    {
        $this->searches[$table] = $parsedQuery;
    }

    private function searchData(): void
    {
        foreach ($this->searches as $searchName => $parsedQuery) {
            $query = $parsedQuery[1];
            //$filters = $parsedQuery[0];
            $config = SearchUtils::CONFIG[$searchName];

            $search = new Search($query, $config, $searchName);
            $this->results[$searchName] = $search->search();
        }
    }

    /**
     * @return array<int, array{data: mixed, type:string, score:int}>
     */
    private function getResults(): array
    {
        $scored = [];
        foreach ($this->results as $result) {
            $scored = array_merge($scored, $result);
        }

        $scored = array_filter($scored, fn ($v) => $v["score"] > 0);
        usort($scored, fn ($a, $b) => $b["score"] <=> $a["score"]);

        $scored = array_slice($scored, 0, $this->limit);
        return $scored;
    }

    /**
     * @param string $query
     * @param int $limit
     * @return array<int, array{data: mixed, type:string, score:int}>
     */
    public static function search(string $query, int $limit = 15): array
    {
        $parsedQuery = SearchUtils::parseSearchInput($query);
        $searchController = new SearchController();
        $searchController->limit = $limit;

        $type = $parsedQuery[0]["type"] ?? "all";
        $config = isset(SearchUtils::CONFIG[$type]) ? true : false;

        if ($type == "all" || $config == false) {
            foreach (array_keys(SearchUtils::CONFIG) as $key) {
                $searchController->add($key, $parsedQuery);
            }
        } else {
            $searchController->add($type, $parsedQuery);
        }

        $searchController->searchData();
        $results = $searchController->getResults();

        return $results;
    }

    public static function ajaxSearch(): void
    {
        $query = Tools::get("query");
        $limit = (int) Tools::get("limit");
        if ($limit <= 0) {
            $limit = 15;
        }

        if ($query == "") {
            JSONResponseHandler::throwError(400, "Query cannot be empty");
        }

        $results = self::search($query, $limit);
        JSONResponseHandler::sendResponse($results);
    }

    public static function searchAll(): void
    {
        $query = Tools::get("query");
        $results = self::search($query, 20);

        $html = "";
        foreach ($results as $result) {
            switch ($result["type"]) {
                case "kunde":
                    break;
                case "produkt":
                    break;
                case "auftrag":
                    break;
                case "wiki_articles":
                    break;
            }
        }

        JSONResponseHandler::sendResponse([
            "html" => $html,
        ]);
    }
}
