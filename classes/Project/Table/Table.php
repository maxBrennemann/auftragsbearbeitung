<?php

namespace Classes\Project\Table;

class Table extends \Classes\Project\Models\Model
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

    private $model;

    public function __construct()
    {
    }

    /**
     * Sets the headers of the table,
     * if extra options are set, they will be merged with the extra options array
     * 
     * @param array $headers
     * 
     * @return bool
     */
    public function setHeaders(array $headers): bool
    {
        if (empty($headers)) {
            return false;
        }

        $this->headers = array_keys($headers);

        foreach ($headers as $key => $header) {
            if ($this->extraOptions[$key]) {
                $this->extraOptions[$key] = array_merge($this->extraOptions[$key], $header);
            }
        }

        return true;
    }

    /**
     * Sets the rows of the table
     * 
     * @param array $rows
     * 
     * @return bool
     */
    public function setRows($rows): bool
    {
        if (empty($rows)) {
            return false;
        }

        $this->rows = $rows;

        return true;
    }

    public function setModel($model)
    {
        $this->model = $model;
    }

    public function addActionButton($icon, $action)
    {
        $this->extraOptions[] = [
            'icon' => $icon,
            'action' => $action
        ];
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

    /**
     * 
     */
    private function generateTableHeader(): string
    {
        $html = "
            <thead>
                <tr>";

        foreach ($this->headers as $header) {
            $html .= "<th>{$header}</th>";
        }

        $html .= "</tr>
            </thead>";

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

        if ($this->model != null) {
            $html .= $this->generatePagination();
        }

        $html .= "</td>";

        $html .= "</tr>";

        $html .= "</tfoot>";

        return $html;
    }

    private function generatePagination(): string
    {
        return "";
    }

    public static function create($model)
    {
        $table = new Table();
        $table->setModel($model);
        return $table;
    }
}
