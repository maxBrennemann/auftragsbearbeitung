<?php

/*
  * Anforderungen der Table Klasse:
  * static Funktionen, um schnell eine Tabelle aus der Datenbank zu generieren, angelehnt an die alte FormGenerator Class
  * initialisierbare Klasse, um Tabellen bearbeitbar zu machen, damit sind verknüpfte Aktionen, wie löschen, hinzufügen und bearbeiten gemeint
*/

class Table {

	private $type = "";
	private $identifier = "";
	private $data;
	private $editable = false;
	private $limit;
	private $link = null;
	public $columnNames;
	
	/* action button variables */
	private $buttonEdit = false;
	private $buttonDelete = false;
	private $buttonUpdate = false;

	private $callback = null;
	private $keys = null;

    function __construct($type = 0, $limit = 10, $editable = false) {
		if (is_numeric($limit) && $limit > 0)
			$this->limit = $limit;

		if (!is_numeric($type)) {
			$cnames = self::getColumnNames($type);
			if ($cnames != null) {
				$this->columnNames = $cnames;
			}
			$this->data = DBAccess::selectQuery("SELECT * FROM `$type` LIMIT " . $this->limit);
		}

		$this->type = $type;
		$this->editable = $editable;
    }

    public function createByDB($type) {
		$this->columnNames = self::getColumnNames($type);
		$this->data = DBAccess::selectQuery("SELECT * FROM `$type` LIMIT " . $this->limit);
    }

    public function createByData($data, $columnNames) {
        $this->type = 0;
        $this->data = $data;
        $this->columnNames = $columnNames;
	}
	
	public function addLink($link) {
		if (is_string($link))
			$this->link = $link;
	}

	/* every index of the keys array is interpreted as a key for the data array */
	public function createKeys() {
		$this->keys = [];
		for ($i = 0; $i < sizeof($this->data); $i++) {
			$key = bin2hex(random_bytes(6));
			while (in_array($key, $this->keys)) {
				$key = bin2hex(random_bytes(6));
			}
			array_push($this->keys, $key);
		}
	}

	public function addUpdateFunction($callback) {
		$this->callback = $callback;
	}

	public function addActionButton($button, $identifier = null) {
		switch($button) {
			case "update":
				$this->buttonUpdate = !$this->buttonUpdate;
				$array = [];
				if ($this->keys == null)
					$this->createKeys();
				
				for ($i = 0; $i < sizeof($this->data); $i++) {
					$btn = $this->addUpdateButton($this->keys[$i]);
					$array[$i] = $btn;
				}
				$this->addColumn("Aktionen", $array);
			break;
			case "edit":
				$this->buttonUpdate = !$this->buttonEdit;
			break;
			case "delete":
				$this->buttonUpdate = !$this->buttonDelete;
				if ($this->keys == null)
					$this->createKeys();
				
				for ($i = 0; $i < sizeof($this->data); $i++) {
					$btn = $this->addDeleteButton($this->keys[$i]);
					$array[$i] = $btn;
				}
				$this->addColumn("Aktionen", $array);

				if ($identifier != null) {
					$this->setIdentifier($identifier);
				}
			break;
		}
	}

	/* action buttons */
	private function addUpdateButton($key) {
        $button = "<button class='actionButton' onclick=\"updateIsDone('$key')\" title='Als erledigt markieren.'>&#x2714;</button>";
		return $button;
    }

    private function addEditButton($key) {
        $button = "<button class='actionButton' onclick=\"editRow($key)\" = 'Bearbeiten' disabled>&#x270E;</button>";
		return $button;
    }

    private function addDeleteButton($key) {
		$button = "<button class='actionButton' onclick=\"deleteRow('$key')\" title='Löschen'>&#x1F5D1;</button>";
		return $button;

    }

    public function addColumn($rowName, $data) {
		if (sizeof($data) == sizeof($this->data)) {
			for ($i = 0; $i < sizeof($data); $i++) {
				$this->data[$i][$rowName] = $data[$i];
			}

			$rowName = [
				"COLUMN_NAME" => $rowName
			];
			array_push($this->columnNames, $rowName);
		} else {
			throw new Exception("Array sizes do not match");
		}
	}
	
	public function addRow($row) {
		if (sizeof($row) == sizeof($this->data[0])) {
			array_push($this->data, $row);
		} else {
			throw new Exception("Array sizes do not match");
		}
	}

	public function setIdentifier($val) {
		if (is_string($val)) {
			$this->identifier = $val;
		} else {
			throw new Exception("wrong data type, String required");
		}
	}

	public static function updateValue($table, $action, $key) {
		if (!is_string($table) || !is_string($action) || !is_string($key))
			return "data cannot be processed";

		if (isset($_SESSION[$table])) {
			$actionObject = unserialize($_SESSION[$table]);
			//$actionObject->update($action, $key);

			if ($actionObject->callback != null)
				$actionObject->callback();

			$number = array_search($key, $actionObject->keys);
			
			$setTo = $_POST['setTo'];
			$actionObject->data[$number]["Hausnummer"] = $setTo;

			if ($action == "delete") {
				$number = array_search($key, $actionObject->keys);
				$row = $actionObject->data[$number]["Kundennummer"];
				DBAccess::deleteQuery("DELETE FROM $actionObject->type WHERE $actionObject->identifier = $row");
				echo "DELETE FROM $actionObject->type WHERE $actionObject->identifier = $row";
			}

			echo $number;
		} else {
			return "no data found";
		}
	}

	/*
	 * erstellt die Tabelle
	 * wenn $this->data null ist, wird eine Nachricht zurückgegeben
	*/
    public function getTable() {
		if ($this->data == null)
			return "<p>Keine Einträge vorhanden</p>";

        $html = "";

        if ($this->editable) {
			$html = "<table class='allowAddingContent' data-type={$this->type} data-send-to={$this->sendTo}>";
		} else {
			$html = "<table data-type={$this->type}>";
		}
        
		$html .= self::html_createTableHeader($this->columnNames);

		/* for each row of the result */
		for ($i = 0; $i < sizeof($this->data); $i++) {
			$row = $this->data[$i];
			$html .= self::html_createRow2($row, $this->columnNames, $this->link);
		}
		
		return $html;
    }

    /* static functions */
    public static function createTable() {

	}
	
	/*
	 * erstellt eine Zeile
	 * 
	 * @param Array		$row		Zeilendaten
	 * @param Array		$rowNames	Zeilennamen
	 * @param string	$link		Link, kann auch null sein, dann wird kein Link gesetzt
	 * 
	 * @return	Gibt eine Tabellenzeile in HTML zurück
	 */
	private static function html_createRow2($row, $rowNames, $link) {
		$html = "<tr>";
		
		for ($i = 0; $i < sizeof($row); $i++) {
			$column = $rowNames[$i]["COLUMN_NAME"];
			$data = $row[$column];
			
			if ($link == null)
				$html .= "<td>" . $data . "</td>";
			else
				$html .= "<td class=\"linkTable\"><a href=\"$link\">" . $data . "</a></td>";
		}

		$html .= "</tr>";
		return $html;
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