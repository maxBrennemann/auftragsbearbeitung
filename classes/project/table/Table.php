<?php

namespace project\table;

class Table
{

    private $headers = [];
    private $rows = [];

    private int $rowCount = 0;
    private int $start = 0;
    private int $page = 1;
    private int $pageSize = 10;

    private $sort = [];

    private $search = [];

    private $filter = [];

    private $total = 0;

    private $extraOptions = [];

    public function __construct()
    {
    }

    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    public function setRows($rows)
    {
        $this->rows = $rows;
    }

    public function generateTable()
    {
        $html = "<table>";

        $html .= $this->generateTableHeader();
        $html .= $this->generateTableBody();
        $html .= $this->generateTableFooter();

        $html .= "</table>";

        return $html;
    }

    private function generateTableHeader(): string
    {
        $html = <<<html
            <thead>
                <tr>
        html;

        foreach ($this->headers as $header) {
            $html .= "<th>{$header}</th>";
        }

        $html .= <<<html
                </tr>
            </thead>
        html;

        return $html;
    }

    private function generateTableBody(): string
    {
        $html = "<tbody>";

        foreach ($this->rows as $row) {
            $html .= "<tr>";

            foreach ($row as $cell) {
                $html .= "<td>{$cell}</td>";
            }

            $html .= "</tr>";
        }

        $html .= "</tbody>";

        return $html;
    }

    private function generateTableFooter(): string
    {
        $html = "<tfoot>";

        $html .= "<tr>";

        $html .= "<td colspan='" . count($this->headers) . "'>";

        $html .= $this->generatePagination();

        $html .= "</td>";

        $html .= "</tr>";

        $html .= "</tfoot>";

        return $html;
    }

    private function generatePagination(): string
    {
        return "";
    }

}
