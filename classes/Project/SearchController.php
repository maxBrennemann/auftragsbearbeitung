<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class SearchController
{

    private array $searches = [];
    private string $searchQuery = "";
    private array $response = [];

    public function __construct(string $query, array $fields, string $searchQuery)
    {
        $this->searchQuery = $searchQuery;
        $this->searches[] = new Search2($query, $fields, $searchQuery);
    }

    public function add(string $query, array $fields)
    {
        $this->searches[] = new Search2($query, $fields, $this->searchQuery);
    }

    public function search($results = 15): array
    {
        foreach ($this->searches as $search) {
            $this->response[] = $search->search();
        }

        return $this->evaluate($results);
    }

    /**
     * TODO: it must be decided when to cut the results
     * 
     * @return array
     */
    private function evaluate($results): array
    {
        $sortedResults = [];

        foreach ($this->response as &$r) {
            usort($r, function ($a, $b) {
                if ($a["match"] == $b["match"]) {
                    return 0;
                }
                return ($a["match"] > $b["match"]) ? -1 : 1;
            });
            $r = array_filter($r, fn($el) => $el["match"] > 0);
            $r = array_slice($r, 0, $results);
            $sortedResults[] = $r;
        }

        return $sortedResults;
    }

    public static function initSearch($type, $query, $results = 10) {
        $sql = "";
        $fields = [];

        switch ($type) {
            case "customer":
                $sql = "SELECT * FROM kunde";
                $fields = ["Firmenname", "Vorname"];
                break;
            case "order":
                $sql = "SELECT Auftragsnummer, Kundennummer, Auftragsbezeichnung, Auftragsbeschreibung FROM auftrag;";
                $fields = ["Auftragsbezeichnung", "Auftragsbeschreibung"];
                break;
            default:
                throw new \Exception("unsupported search type");
        } 

        $sc = new SearchController($sql, $fields, $query);
        return $sc->search($results);
    }

    public static function init()
    {
        $type = Tools::get("type");
        $query = Tools::get("query");
        $results = Tools::get("results");

        if ($query == null) {
            JSONResponseHandler::throwError(400, "no query parameter found");
        }

        try {
            $results = self::initSearch($type, $query, $results);
        } catch (\Exception $e) {
            JSONResponseHandler::throwError(400, $e->getMessage());
        }

        JSONResponseHandler::sendResponse($results);
    }
}
