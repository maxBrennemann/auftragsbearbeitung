<?php

namespace Classes\Project;

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

    public function search(): array
    {
        foreach ($this->searches as $search) {
            $this->response[] = $search->search();
        }

        return $this->evaluate();
    }

    private function evaluate(): array
    {
        $sortedResults = [];

        foreach ($this->response as &$r) {
            usort($r, function ($a, $b) {
                if ($a["match"] == $b["match"]) {
                    return 0;
                }
                return ($a["match"] > $b["match"]) ? -1 : 1;
            });
            $r = array_slice($r, 0, 5);
            $sortedResults[] = $r;
        }

        return $sortedResults;
    }

    public static function init()
    {
        $sc = new SearchController("SELECT * FROM kunde", ["Firmenname", "Vorname"], "schule");
        var_dump($sc->search());

        //var_dump($sc->response);
    }
}
