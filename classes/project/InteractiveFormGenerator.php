<?php

require_once('classes/DBAccess.php');
error_reporting(E_ALL);

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * Klasse generiert Tabellen für Formulare, erbt von FormGenerator
 *
 * @access public
 * @author Max Brennemann, maxgoogelt@gmail.com
 */
class InteractiveFormGenerator extends FormGenerator {

	private $isRowDeletable = false;
	private $isRowEditable = false;
	private $isRowDone = false;

	private $identifier = "";

	function __construct($type) {
		parent::__construct($type, "", "");
	}

	public function setIsOrderedBy($isOrderedBy) {
		if (is_string($val)) {
			$this->isOrderedBy = $isOrderedBy;
		} else {
			throw new Exception("wrong data type, String required");
		}
	}

	public function setWhereCondition($whereCondition) {
		if (is_string($val)) {
			$this->whereCondition = $whereCondition;
		} else {
			throw new Exception("wrong data type, String required");
		}
	}

	public function setRowEditable($val) {
		if (is_bool($val)) {
			$this->isRowEditable = $val;
		} else {
			throw new Exception("wrong data type, boolean required");
		}
	}

	public function setRowDone($val) {
		if (is_bool($val)) {
			$this->isRowDone = $val;
		} else {
			throw new Exception("wrong data type, boolean required");
		}
	}

	public function setRowDeletable($val) {
		if (is_bool($val)) {
			$this->isRowDeletable = $val;
		} else {
			throw new Exception("wrong data type, boolean required");
		}
	}

	public function setIdentifier($val) {
		if (is_string($val)) {
			$this->identifier = $val;
		} else {
			throw new Exception("wrong data type, String required");
		}
	}

	public function deleteRow($row) {
		DBAccess::deleteQuery("DELETE FROM $this->type WHERE $this->identifier = $row");
	}

	public function editRow($row, $column, $data) {
		DBAccess::updateQuery("UPDATE $this->type SET $column = $data WHERE $this->identifier = $row");
	}

	private function addDeleteButton($row) {
		$button = "<button onclick=\"deleteRow('$this->type', $row)\">🗑</button>";
		return $button;
	}

	private function addEditButton() {
	
	}

	private function updateIsDone($row) {
		$button = "<button onclick=\"updateIsDone($row)\" title='Als erledigt markieren.'>✔</button>";
		return $button;
	}

	public function create($data, $columnNames) {
		if ($this->isRowDeletable) {
			$editcolumn = array("COLUMN_NAME" => "Bearbeitung");
			array_push($columnNames, $editcolumn);

			for ($i = 0; $i < sizeof($data); $i++) {
				$btn =  $this->addDeleteButton($i);
				$data[$i]["Bearbeitung"] = $btn;
			}
		}

		if ($this->isRowDone) {
			$editcolumn = array("COLUMN_NAME" => "Erledigt");
			array_push($columnNames, $editcolumn);

			for ($i = 0; $i < sizeof($data); $i++) {
				$btn =  $this->updateIsDone($data[$i]["Schrittnummer"]);
				$data[$i]["Erledigt"] = $btn;
			}
		}

		return $this->createTableByData($data, $columnNames);
	}

}

?>