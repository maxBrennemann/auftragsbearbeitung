<?php

class Table {

    private $type = "";
    private $data;
    private $columnNames;

    function __construct() {

    }

    public function createByDB($type) {
        $this->columnNames = self::getColumnNames($type);
    }

    public function createByData($data, $columnNames) {
        $this->type = 0;
        $this->data = $data;
        $this->columnNames = $columnNames;
    }

    public function addColumn() {

    }

    public function getTable() {
        $html = "";

        if ($this->editable) {
			$html = "<table class='allowAddingContent' data-type={$this->type} data-send-to={$this->sendTo}>";
		} else {
			$html = "<table data-type={$this->type}>";
        }
        
        $html .= self::html_createTableHeader($this->column_names);
    }

    /* static functions */
    public static function createTable() {

    }

    /* returns the column names of a specific sql table */
    private static function getColumnNames($type) {
		return DBAccess::selectColumnNames($type);
	}

    private static function generateTable($type, $editable, $showData, $sendTo, $amountOfData, $isRowLink, $data = null, $column_names = null, $forceData = false, $retUrl = null, $addClass = "") {
		if ($column_names == null && strcmp($type, "")) {
			$column_names = self::getColumnNames($type);
		}

		if ($editable) {
			$html_table = "<table class='allowAddingContent' data-type=${type} data-send-to=${sendTo}>";
		} else {
			$html_table = "<table data-type=${type}>";
		}

		$html_table .= self::html_createTableHeader($column_names);
		
		if ($showData) {
			if ($data == null && $forceData == false) {
				$data = self::executeSQLQuery($type, $amountOfData);
			}

			for ($i = 0; $i < sizeof($data); $i++) {
				$html_table = $html_table . "<tr>";
				for ($n = 0; $n < sizeof($column_names); $n++) {
					$showColumnData = $data[$i][$column_names[$n]["COLUMN_NAME"]];

					$addToTable = self::createRow($showColumnData, $retUrl, $column_names[$n]["COLUMN_NAME"], $n, $isRowLink, $type);
					$html_table .= $addToTable;
				}
				$html_table = $html_table . "</tr>";
			}
		}
		
		if ($editable) {
			$html_table = $html_table . self::createEmptyRow(sizeof($column_names), $addClass, $type);
		}
		return $html_table . "</table>";
    }
    
    /*
	* executes the SQL Query composed of the type of the table, the amount of data to be extracted and the where condition as well as the order condition
	*/
	private static function executeSQLQuery($type, $amountOfData, $orderBy = "", $whereCondition = "") {
		$isOrderedByStatement = "";
		$whereConditionStatement = "";
		if (strcmp($orderBy, "") != 0) {
			$isOrderedByStatement = "ORDER BY {$orderBy}";
		}
		if (strcmp($whereCondition, "") != 0) {
			$whereConditionStatement = "WHERE {$whereCondition}";
		}
		
		if ($amountOfData == -1) {
			return DBAccess::selectQuery("SELECT * FROM ${type} {$whereConditionStatement} {$isOrderedByStatement}");
		} else {
			return DBAccess::selectQuery("SELECT * FROM ${type} {$whereConditionStatement} {$isOrderedByStatement} LIMIT ${amountOfData}");
		}
	}

    /* html generator functions */
    private static function html_createTableHeader($column_names) {
		$table_header = "<tr>";

		for ($i = 0; $i < sizeof($column_names); $i++) {
			$showColumnName = $column_names[$i]["COLUMN_NAME"];
			$table_header .= "<th class='tableHead'>${showColumnName} <span class=\"cursortable\" onclick=\"sortTable(this, $i, true)\">&#x25B2;</span><span class=\"cursortable\" onclick=\"sortTable(this, $i, false)\">&#x25BC;</span></th>";
		}

		return $table_header . "</tr>";
    }
    
    private static function html_createRow($showColumnData, $retUrl, $columnName, $n, $isRowLink, $type) {
		$html = "";

		if ($n == 0 && $isRowLink) {
			$url = $_SERVER['REQUEST_URI'];
			$url = strtok($url, '?');
			if ($retUrl != null) {
				$html = $html . "<td><a href='{$retUrl}?showDetails={$type}&id={$showColumnData}'>{$showColumnData}</a></td>";
			} else {
				$html = $html . "<td><a href='{$url}?showDetails={$type}&id={$showColumnData}'>{$showColumnData}</a></td>";
			}
		} else {
			$html = $html . "<td>{$showColumnData}</td>";
		}

		return $html;
	}

}

?>