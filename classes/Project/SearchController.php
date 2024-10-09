<?php

use Classes\DBAccess;

class SearchController
{

    private string $query = "";
    private string $searchQuery = "";

    private array $fields = [];
    private array $data = [];
    private array $response = [];

    public function __construct(string $query, array $fields, string $searchQuery)
    {
        $this->query = $query;
        $this->fields = $fields;
        $this->searchQuery = $searchQuery;
    }

    public function search(): array
    {
        $this->data = DBAccess::selectQuery($this->query);

        foreach ($this->data as $row) {
            $this->response[] = [
                "row" => $row,
                "match" => 0,
            ];
        }

        return [];
    }

    private function evaluateMatches(): array {
        foreach ($this->response as $calculatedResponse) {
            $row = $calculatedResponse["row"];

            foreach ($this->fields as $field) {
                $searchIn = $row[$field];
                
                if (!isset($searchIn)) {
                    continue;
                }

                if ($searchIn == $this->searchQuery) {
                    $calculatedResponse["match"] += 300;
                    continue;
                }

                $substCount = substr_count($searchIn, $this->searchQuery);
                $calculatedResponse["match"] += $substCount * 15;

                if ($substCount >= 3) {
                    continue;
                }

                $parts = explode($searchIn, " ");
                foreach ($parts as $part) {
                    $ld = levenshtein($this->searchQuery, $part);

                    switch ($ld) {
                        case 1:
                            $calculatedResponse["match"] += 10;
                            break;
                        case 2:
                            $calculatedResponse["match"] += 5;
                            break;
                        case 3:
                            $calculatedResponse["match"] += 3;
                            break;
                    }
                }
            }
        }


        return [];
    }
}
