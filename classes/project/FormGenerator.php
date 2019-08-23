<?php

require_once('classes/DBAccess.php');
error_reporting(E_ALL);

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * Klasse generiert Tabellen für Formulare
 *
 * @access public
 * @author Max Brennemann, maxgoogelt@gmail.com
 */
class FormGenerator {
	
	private $type;
	private $isOrderedBy;
	private $whereCondition;

	function __construct($type, $isOrderedBy, $whereCondition) {
		$this->type = $type;
		$this->isOrderedBy = $orderBy;
		$this->whereCondition = $whereCondition;
	}

	private static function getColumnNames($type) {
		return DBAccess::selectQuery("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = N'${type}'");
	}

	/*
	* static function is used to cover the private static function generateTable(), because some parameters are not needed for this function
	*/
	public static function createTable($type, $editable, $showData, $sendTo, $amountOfData = 5, $isRowLink = false) {
		return self::generateTable($type, $editable, $showData, $sendTo, $amountOfData, $isRowLink);
	}

	/*
	* $type is the table from which the data is extracted
	* $editable is responsible for editing the table
	* $sendTo is the page the edited information will be send to
	* $amountOfData is the number of rows of the table which will be shown, default is 5
	* $isRowLink creates a link for every first element of a row, the link is composed of the current page link and the getParameters showDetails and id, $isRowLink only works, if the page
	* can show details for a specific value and if the first column represents a unique number
	*/
	private static function generateTable($type, $editable, $showData, $sendTo, $amountOfData, $isRowLink, $data = null) {
		$column_names = self::getColumnNames($type);

		if($editable) {
			$html_table = "<table border='1' class='allowAddingContent' data-type=${type} data-send-to=${sendTo}>";
		} else {
			$html_table = "<table border='1'>";
		}

		$html_table .= self::createTableHeader($column_names);
		
		if ($showData) {
			if ($data == null) {
				$data = self::executeSQLQuery($type, $amountOfData);
			}

			for ($i = 0; $i < sizeof($data); $i++) {
				$html_table = $html_table . "<tr>";
				for ($n = 0; $n < sizeof($column_names); $n++) {
					$showColumnData = $data[$i][$column_names[$n]["COLUMN_NAME"]];
					if ($n == 0 && $isRowLink) {
						$html_table = $html_table . "<td><a href='{$_SERVER['REQUEST_URI']}?showDetails={$type}&id={$showColumnData}'>{$showColumnData}</a></td>";
					} else {
						$html_table = $html_table . "<td>{$showColumnData}</td>";
					}
					
				}
				$html_table = $html_table . "</tr>";
			}
		}
		
		if ($editable) {
			$html_table = $html_table . self::createEmptyRow(sizeof($column_names));
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
		
		return DBAccess::selectQuery("SELECT * FROM ${type} {$whereConditionStatement} {$isOrderedByStatement} LIMIT ${amountOfData}");
	}

	/*
	* creates an empty table row the size of the input $number
	*/
	private static function createEmptyRow($number) {
		$empty_row = "<tr>";

		for ($i = 0; $i < $number; $i++) {
			$empty_row = $empty_row . "<td class='addingContentColumn' contenteditable='true'></td>";
		}

		return $empty_row . "</tr>";
	}

	/*
	* creates the <th> row of the table containing the column names
	*/
	private static function createTableHeader($column_names) {
		$table_header = "<tr>";

		for ($i = 0; $i < sizeof($column_names); $i++) {
			$showColumnName = $column_names[$i]["COLUMN_NAME"];
			$table_header .= "<th class='tableHead'>${showColumnName}</th>";
		}

		return $table_header . "</tr>";
	}

	public static function insertData($type, $data) {
		$column_names = DBAccess::selectQuery("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = N'${type}'");

		$input_string = "INSERT INTO ${type} (";
		$columns = "";
		$values = "VALUES (";
		for ($i = 0; $i < sizeof($column_names); $i++) {
			$columns = $columns . $column_names[$i]["COLUMN_NAME"];
			$values = $values . $data[$i];
			if ($i < sizeof($column_names) - 1) {
				$columns = $columns . ", ";
				$values = $values . ", ";
			}
		}
		$input_string = $input_string . $columns . ") " . $values . ")";
		DBAccess::insertQuery($input_string);
	}

	public function setIsOrderedBy($isOrderedBy) {
		$this->isOrderedBy = $orderBy;
	}

	public function setWhereCondition($whereCondition) {
		$this->whereCondition = $whereCondition;
	}

	/*
	* creates table orderd by the specified settings:
	*		- isOrderedBy
	*		- whereCondition
	*/
	public function createSpecializedTable($editable, $showData, $sendTo, $amountOfData, $isRowLink) {
		if(strcmp($this->isOrderedBy, "") == 0 || $this->isOrderedBy == null) {
			return "";
		} else {
			$data = self::executeSQLQuery($this->type, $amountOfData, $this->isOrderedBy, $this->whereCondition);
			return generateTable($this->type, $editable, $showData, $sendTo, $amountOfData, $isRowLink, $data);
		}
	}

}

?>