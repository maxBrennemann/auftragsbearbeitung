<?php

/**
 * Klasse generiert Tabellen für Formulare
 *
 * @access public
 * @author Max Brennemann, maxgoogelt@gmail.com
 */
class FormGenerator {
	
	protected $type;
	protected $isOrderedBy;
	protected $whereCondition;
	protected $tableData;
	protected $dataTypes = null;

	function __construct($type, $isOrderedBy, $whereCondition) {
		$this->type = $type;
		$this->isOrderedBy = $isOrderedBy;
		$this->whereCondition = $whereCondition;
	}

	public function setData($data) {
		$this->tableData = $data;
	}

	private static function getColumnNames($type) {
		return DBAccess::selectColumnNames($type);
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
	private static function generateTable($type, $editable, $showData, $sendTo, $amountOfData, $isRowLink, $data = null, $column_names = null, $forceData = false, $retUrl = null, $addClass = "") {
		if ($column_names == null) {
			$column_names = self::getColumnNames($type);
		}

		if($editable) {
			$html_table = "<table class='allowAddingContent' data-type=$type data-send-to=$sendTo>";
		} else {
			$html_table = "<table data-type=$type>";
		}

		$html_table .= self::createTableHeader($column_names);
		
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
			return DBAccess::selectQuery("SELECT * FROM $type $whereConditionStatement $isOrderedByStatement;");
		} else {
			return DBAccess::selectQuery("SELECT * FROM $type $whereConditionStatement $isOrderedByStatement LIMIT $amountOfData;");
		}
	}

	/*
	* creates an empty table row the size of the input $number
	*/
	private static function createEmptyRow($number, $addClass, $type) {
		$tableInfo = DBAccess::selectQuery("DESCRIBE $type");
		$empty_row = "<tr>";

		for ($i = 0; $i < $number; $i++) {
			$dataType = $tableInfo[$i]['Type'];
			if(strpos($dataType, "int") === 0) {
				$dataType = "number";
			} else if(strpos($dataType, "varchar") === 0) {
				$dataType = (int) str_replace("varchar(", "", $dataType);
			}
			if ($addClass == "") {
				$empty_row = $empty_row . "<td class='addingContentColumn' contenteditable='true' data-datatype='$dataType'></td>";
			} else {
				$empty_row = $empty_row . "<td class='addingContentColumn $addClass' contenteditable='true' data-datatype='$dataType'></td>";
			}
		}

		return $empty_row . "</tr>";
	}

	private static function createRow($showColumnData, $retUrl, $columnName, $n, $isRowLink, $type) {
		$html = "";

		/*
		 * Exception for Kunde table, Anrede will be replaced for value 0 by "Herr" and for value 1 by "Frau";
		*/
		if ($columnName == "Anrede") {
			if ($showColumnData == "0") {
				$showColumnData = "Herr";
			} else {
				$showColumnData = "Frau";
			}
		}

		if ($n == 0 && $isRowLink) {
			$url = $_SERVER['REQUEST_URI'];
			$url = strtok($url, '?');
			if ($retUrl != null) {
				$html = $html . "<td><a href='$retUrl?showDetails=$type&id=$showColumnData'>$showColumnData</a></td>";
			} else {
				$html = $html . "<td><a href='$url?showDetails=$type&id=$showColumnData'>$showColumnData</a></td>";
			}
		} else {
			$html = $html . "<td>$showColumnData</td>";
		}

		return $html;
	}

	/*
	* creates the <th> row of the table containing the column names
	*/
	private static function createTableHeader($column_names) {
		$table_header = "<tr>";

		for ($i = 0; $i < sizeof($column_names); $i++) {
			$showColumnName = $column_names[$i]["COLUMN_NAME"];
			$table_header .= "<th class='tableHead'>$showColumnName <span class=\"cursortable\" onclick=\"sortTable(this, $i, true)\">&#x25B2;</span><span class=\"cursortable\" onclick=\"sortTable(this, $i, false)\">&#x25BC;</span></th>";
		}

		return $table_header . "</tr>";
	}

	public static function insertData($type, $data) {
		$column_names = DBAccess::selectColumnNames($type);

		$input_string = "INSERT INTO $type (";
		$columns = "";
		$values = "VALUES (";
		for ($i = 0; $i < sizeof($column_names); $i++) {
			$columns = $columns . $column_names[$i]["COLUMN_NAME"];
			$values = $values . "'" . $data[$i] . "'";
			if ($i < sizeof($column_names) - 1) {
				$columns = $columns . ", ";
				$values = $values . ", ";
			}
		}
		$input_string = $input_string . $columns . ") " . $values . ")";
		DBAccess::insertQuery($input_string);
	}

	public function setIsOrderedBy($isOrderedBy) {
		$this->isOrderedBy = $isOrderedBy;
	}

	public function setWhereCondition($whereCondition) {
		$this->whereCondition = $whereCondition;
	}

	/*
	* creates table orderd by the specified settings:
	*		- isOrderedBy
	*		- whereCondition
	*/
	public function createSpecializedTable($editable, $showData, $sendTo, $amountOfData, $isRowLink, $column_names = null) {
		if(strcmp($this->isOrderedBy, "") == 0 || $this->isOrderedBy == null) {
			return "";
		} else {
			$data = self::executeSQLQuery($this->type, $amountOfData, $this->isOrderedBy, $this->whereCondition);
			return self::generateTable($this->type, $editable, $showData, $sendTo, $amountOfData, $isRowLink, $data, $column_names);
		}
	}

	/*
	* creates a table by the passed data and the $column_names
	* type is not needed, because the column names are passed in an array, the table is not editable;
	* Last parameter of generateTable is true, so the use of the passed data is forced (no null pointer or something else);
	*/
	public function createTableByData($data, $column_names) {
		return self::generateTable($this->type, false, true, "", -1, false, $data, $column_names, true);
	}

	public function createTableByDataRowLink($data, $column_names, $type, $retUrl) {
		return self::generateTable($type, false, true, "", -1, true, $data, $column_names, true, $retUrl);
	}

	public static function createEmptyTable($column_names, $addClass) {
		return self::generateTable("", true, false, "", 0, false, null, $column_names, false, null, $addClass);
	}

}

?>