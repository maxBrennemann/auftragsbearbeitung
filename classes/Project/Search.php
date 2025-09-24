<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;

class Search
{
    private string $query = "";
    //private array $filters = [];
    private array $config = [];
    private string $table = "";

    public function __construct(string $query, array $config, string $table)
    {
        $this->query = $query;
        //$this->filters = $filters;
        $this->config = $config;
        $this->table = $table;
    }

    /**
     * @return array
     */
    public function search(): array
    {
        $sqlResults = $this->runSQLSearch();
        $broadResults = $this->fetchBroadSet();

        $scored = $this->mergeAndScore($sqlResults, $broadResults, $this->query, $this->table);
        return $scored;
    }

    /**
     * @return array<int, array<string>>
     */
    private function fetchBroadSet(): array
    {
        $columns = array_filter($this->config["columns"], fn ($value) =>
        isset($value["fuzzy"])
            && $value["fuzzy"] == true);
        $columnNames = array_keys($columns);

        $id = $this->config["id"];
        $query = "SELECT $id, " . implode(",", $columnNames) . " FROM " . $this->table;

        return DBAccess::selectQuery($query);
    }

    private function runSQLSearch(): array
    {
        $SQLQuery = $this->buildSearchQuery($this->table, $this->query);
        $data = $this->runSearchQuery($SQLQuery[0], $SQLQuery[1]);
        return $data;
    }

    private function runSearchQuery(string $query, $params): array
    {
        $data = DBAccess::selectQuery($query, $params);
        return $data;
    }

    private function buildSearchQuery($table, string $searchTerm): array
    {
        $columns = SearchUtils::CONFIG[$table]["columns"] ?? [];
        $conditions = [];
        $params = [];

        foreach ($columns as $column => $info) {
            switch ($info["type"]) {
                case "text":
                    if (!empty($info["fuzzy"])) {
                        $conditions[] = "$column LIKE ?";
                        $params[] = "%" . $searchTerm . "%";
                    } else {
                        $conditions[] = "$column = ?";
                        $params[] = $searchTerm;
                    }
                    break;
                case "number":
                    if (is_numeric($searchTerm)) {
                        $conditions[] = "$column = ?";
                        $params[] = $searchTerm;
                    }
                    break;
                case "phone":
                    $normalized = SearchUtils::normalizePhone($searchTerm);
                    if ($normalized == 0) {
                        break;
                    }
                    $conditions[] = "REPLACE(REPLACE(REPLACE($column, ' ', ''), '-', ''), '+', '') = ?";
                    $params[] = $normalized;
                    break;
                case "date":
                    if (strtotime($searchTerm)) {
                        $conditions[] = "DATE($column) = ?";
                        $params[] = date('Y-m-d', strtotime($searchTerm));
                    }
                    break;
            }
        }

        if (empty($conditions)) {
            return [
                null,
                [],
            ];
        }

        $query = "SELECT * FROM $table WHERE " . implode(" OR ", $conditions);
        return [$query, $params];
    }

    /**
     * @param array<int, array<string>> $sqlResults
     * @param array<int, array<string>> $broadResults
     * @param string $query
     * @param string $table
     * @return array<int, array{data: mixed, type:string, score:int}>
     */
    private function mergeAndScore(array $sqlResults, array $broadResults, string $query, string $table): array
    {
        $scored = [];
        $key = $this->config["id"];

        foreach ($sqlResults as $row) {
            $id = $row[$key];
            $scored[$id] = [
                "data" => $row,
                "type" => $table,
                "score" => 30,
            ];
        }

        foreach ($broadResults as $row) {
            $id = $row[$key];
            $score = 0;
            foreach ($row as $colName => $colVal) {
                if ($key == $colName || $colVal == null) {
                    continue;
                }

                $value = strtolower($colVal);
                $distance = levenshtein(strtolower($query), $value);
                $score += max(0, 7 - $distance);

                if (str_contains($value, strtolower($query))) {
                    $score += 5;
                }
            }

            if (!isset($scored[$id])) {
                $scored[$id] = [
                    "data" => $row,
                    "type" => $table,
                    "score" => -7,
                ];
            }

            $scored[$id]["score"] += $score;
        }

        $scored = array_filter($scored, fn ($v) => $v["score"] > 0);
        usort($scored, fn ($a, $b) => $b["score"] <=> $a["score"]);
        return $scored;
    }
}
