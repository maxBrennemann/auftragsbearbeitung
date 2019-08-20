<?php

require_once('DBAccess.php');
error_reporting(E_ALL);

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * Klasse generiert Tabellen für Formulare
 *
 * @access public
 * @author firstname and lastname of author, <author@example.org>
 */
class FormGenerator {
	private $column_names;
    
	function __construct($type) {
		self::$column_names = DBAccess::selectQuery("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = N'${type}'");
		var_dump($column_names);
	}

	public function createTable($showData, $amountOfData = 5) {
		$html_table = "<table><tr>";
		$data = DBAccess::selectQuery("SELECT * FROM Table ORDER BY ID DESC LIMIT ${amountOfData}");
		$empty_row = "<tr>";
		for ($i = 0; $i < sizeof(self::$column_names); $i++) {
			$html_table = $html_table . "<th>${column_names[$i]}</th>";
			if ($showData == null) {
				$empty_row = $empty_row . "<td></td>";
			}
		}
		$empty_row = $empty_row . "</tr>";
		$html_table = $html_table . "</tr>";
		if ($showData == null) {
			$html_table = $html_table . $empty_row;
		}
		for ($i = 0; $i < sizeof($data); $i++) {
			$html_table = $html_table . "<tr>";
			for ($n = 0; $n < sizeof(self::$column_names); $n++) {
				$html_table = $html_table . "<td>${data[$i][self::$column_names[$n]}</td>";
			}
			$html_table = $html_table . "</tr>";
		}
		$html_table = "</table>";
		return $html_table;
	}

	public function insertData() {
		$input_string = "INSERT INTO ${self::$type} (";
		$columns = "";
		$values = "VALUES (";
		for ($i = 0; i < sizeof(self::$column_names)); i++) {
			$columns = $columns . self::$column_names[$i];
			$values = $values . $data[$i];
			if (i < sizeof(self::$column_names) - 1) {
				$columns = $columns . ", ";
				$values = $values . ", ";
			}
		}
		$input_string = $input_string . $columns . ") " . $values . ")";

		DBAccess::insertQuery($input_string);
	}

}

?>