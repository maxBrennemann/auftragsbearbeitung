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

	private $additionalParams = array();

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

	public function addParam($key, $value) {
		$this->additionalParams[$key] = $value;
	}

	public function deleteRow($row) {
		DBAccess::deleteQuery("DELETE FROM $this->type WHERE $this->identifier = $row");
	}

	public function editRow($row, $column, $data) {
		$additionalData = ",";
		foreach ($this->additionalParams as $key => $value) {
			$additionalData .= $key . " = '" . $value . "',";
		}
		$additionalData = rtrim($additionalData, ',');
		DBAccess::updateQuery("UPDATE $this->type SET $column = $data $additionalData WHERE $this->identifier = $row");
	}

	private function addDeleteButton($row) {
		$button = "<button class='actionButton' onclick=\"deleteRow($row)\" title='Löschen'>&#x1F5D1;</button>";
		return $button;
	}

	private function addEditButton() {
		$button = "<button class='actionButton' onclick=\"editRow()\" title='Bearbeiten' disabled>&#x270E;</button>";
		return $button;
	}

	private function updateIsDone($row) {
		$button = "<button class='actionButton' onclick=\"updateIsDone($row)\" title='Als erledigt markieren.'>&#x2714;</button>";
		return $button;
	}

	public function create($data, $columnNames, $default = "Schrittnummer") {
		if ($this->isRowDeletable) {
			$editcolumn = array("COLUMN_NAME" => "Bearbeitung");
			array_push($columnNames, $editcolumn);

			for ($i = 0; $i < sizeof($data); $i++) {
				$btn =  $this->addDeleteButton($i);
				$data[$i]["Bearbeitung"] = $btn;
			}
		}

		if ($this->isRowEditable) {
			$editcolumn = array("COLUMN_NAME" => "Bearbeitung");
			
			$hasBearbeitung = false;
			for ($i = 0; $i < sizeof($columnNames); $i++) {
				if ($columnNames[$i]["COLUMN_NAME"] == "Bearbeitung") {
					$hasBearbeitung = true;
				}
			}

			if (!$hasBearbeitung) {
				array_push($columnNames, $editcolumn);
			}

			for ($i = 0; $i < sizeof($data); $i++) {
				$btn =  $this->addEditButton($i);
				$data[$i]["Bearbeitung"] .= $btn;
			}
		}

		/* adds three buttons, needs a rework */
		if ($this->isRowDone) {
			$editcolumn = array("COLUMN_NAME" => "Aktionen");
			array_push($columnNames, $editcolumn);

			for ($i = 0; $i < sizeof($data); $i++) {
				$row = $data[$i][$default];

				$update =  $this->updateIsDone($row);
				$edit = $this->addEditButton($row);
				$delete = $this->addDeleteButton($row);

				$data[$i]["Aktionen"] = $update . $edit . $delete;
			}
		}

		return $this->createTableByData($data, $columnNames);
	}

}

?>