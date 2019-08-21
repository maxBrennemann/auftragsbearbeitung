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
 * @author firstname and lastname of author, <author@example.org>
 */
class FormGenerator {
	
	function __construct($type) {

	}

	private static function getColumnNames($type) {
		self::$column_names = DBAccess::selectQuery("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = N'${type}'");
	}

	public static function createTable($type, $editable, $showData, $sendTo, $amountOfData = 5) {
		if($editable) {
			$html_table = "<table border='1' class='allowAddingContent' data-type=${type} data-send-to=${sendTo}><tr>";
		} else {
			$html_table = "<table border='1'><tr>";
		}
		$empty_row = "<tr>";

		$column_names = DBAccess::selectQuery("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = N'${type}'");

		for ($i = 0; $i < sizeof($column_names); $i++) {
			$showColumnName = $column_names[$i]["COLUMN_NAME"];
			$html_table = $html_table . "<th class='tableHead'>${showColumnName}</th>";
			if ($editable == true) {
				$empty_row = $empty_row . "<td class='addingContentColumn' contenteditable='true'></td>";
			} else {
				$empty_row = $empty_row . "<td></td>";
			}
		}
		
		$html_table = $html_table . "</tr>";
		if ($showData == true) {
			//$data = DBAccess::selectQuery("SELECT * FROM ${type} ORDER BY `Kundennummer` DESC LIMIT ${amountOfData}");
			$data = DBAccess::selectQuery("SELECT * FROM ${type} LIMIT ${amountOfData}");
			for ($i = 0; $i < sizeof($data); $i++) {
				$html_table = $html_table . "<tr>";
				for ($n = 0; $n < sizeof($column_names); $n++) {
					$showColumnName = $data[$i][$column_names[$n]["COLUMN_NAME"]];
					$html_table = $html_table . "<td>${showColumnName}</td>";
				}
				$html_table = $html_table . "</tr>";
			}
		}
		$empty_row = $empty_row . "</tr>";
		if ($editable == true) {
			$html_table = $html_table . $empty_row;
		}
		$html_table = $html_table . "</table>";
		return $html_table;
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

}

?>