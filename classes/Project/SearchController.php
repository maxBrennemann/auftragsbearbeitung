<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class SearchController
{

    private array $searches = [];
    private array $results = [];
    private int $limit = 15;

    private function add(string $table, array $parsedQuery)
    {
        $this->searches[$table] = $parsedQuery;
    }

    private function searchData()
    {
        foreach ($this->searches as $searchName => $parsedQuery) {
            $query = $parsedQuery[1];
            $filters = $parsedQuery[0];
            $config = SearchUtils::CONFIG[$searchName];

            $search = new Search($query, $filters, $config, $searchName);
            $this->results[$searchName] = $search->search();
        }
    }

    private function getResults()
    {
        $scored = [];
        foreach ($this->results as $result) {
            $scored = array_merge($scored, $result);
        }

        $scored = array_filter($scored, fn($v) => $v["score"] > 0);
        usort($scored, fn($a, $b) => $b["score"] <=> $a["score"]);

        $scored = array_slice($scored, 0, $this->limit);
        return $scored;
    }

    public static function search($query, $limit = 15)
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

    public static function ajaxSearch()
    {
        $query = Tools::get("query");
        $limit = (int) Tools::get("limit");
        if ($limit <= 0) {
            $limit = 15;
        }

        if ($query == "") {
            JSONResponseHandler::throwError(400, "Query cannot be empty");
            return;
        }

        $results = self::search($query, $limit);
        JSONResponseHandler::sendResponse($results);
    }
}
