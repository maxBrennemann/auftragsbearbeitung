<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;

/**
 * Anforderungen der Table Klasse:
 * static Funktionen, um schnell eine Tabelle aus der Datenbank zu generieren,
 * angelehnt an die alte FormGenerator Class
 * initialisierbare Klasse, um Tabellen bearbeitbar zu machen,
 * damit sind verknüpfte Aktionen, wie löschen, hinzufügen und bearbeiten gemeint
 */
class Table
{
    private $type = "";
    private $identifier = "";
    private $data;
    private $editable = false;
    private $limit = 0;
    private $link = null;
    public $columnNames;

    /* TODO: schauen, ob diese parameter public oder private sein sollen, weil ich die nur eingefügt habe, weil sie nicht definiert waren */
    private $update;

    private $dataset = [
        0 => false
    ];

    /* action button variables */
    private $buttonEdit = false;
    private $buttonDelete = false;
    private $buttonUpdate = false;
    private $buttonCheck = false;

    /* speicifies whether a new line button is included or not */
    private $addNewLineButtonTrue = false;

    private $callback = null;
    private $keys = null;

    private $dataKey;

    /**
     * limit -1 hebt das Limit auf
     */
    public function __construct($type = 0, int $limit = 10, $editable = false)
    {
        $this->limit = $limit;

        if (!is_numeric($type)) {
            $cnames = self::getColumnNames($type);
            if ($cnames != null) {
                $this->columnNames = $cnames;
            }

            if ($this->limit == -1) {
                $this->data = DBAccess::selectQuery("SELECT * FROM `$type`");
            } else {
                $this->data = DBAccess::selectQuery("SELECT * FROM `$type` LIMIT " . $this->limit);
            }
        }

        $this->type = $type;
        $this->editable = $editable;

        $this->dataKey = "key_" . bin2hex(random_bytes(6));
    }

    public function getTableKey(): string
    {
        return $this->dataKey;
    }

    public function createByDB($type): void
    {
        $this->columnNames = self::getColumnNames($type);
        $this->data = DBAccess::selectQuery("SELECT * FROM `$type` LIMIT " . $this->limit);
    }

    public function createByData($data, $columnSpecs): void
    {
        $this->type = 0;
        $this->data = $data;
        $this->columnNames = $columnSpecs;
    }

    public function setType($type): void
    {
        $this->type = $type;
    }

    public function addLink($link): void
    {
        $this->link = $link;
    }

    public function addDataset($key, $value): void
    {
        $this->dataset = [
            0 => true,
            1 => $key,
            2 => $value
        ];
    }

    public function getData()
    {
        return $this->data;
    }

    /* every index of the keys array is interpreted as a key for the data array */
    public function createKeys(): void
    {
        $this->keys = [];
        for ($i = 0; $i < sizeof($this->data); $i++) {
            $key = bin2hex(random_bytes(6));
            while (in_array($key, $this->keys)) {
                $key = bin2hex(random_bytes(6));
            }
            array_push($this->keys, $key);
        }
    }

    /*
     * function adds a new column for selections
     * can currently only be used to add an input checkbox
     */
    public function addSelector($type): void
    {
        switch ($type) {
            case "check":
                $array = [];
                if ($this->keys == null) {
                    $this->createKeys();
                }

                for ($i = 0; $i < sizeof($this->data); $i++) {
                    $btn = $this->addCheckSelector($this->keys[$i]);
                    $array[$i] = $btn;
                }
                $this->addColumn("Auswählen", $array);
                break;
        }
    }

    /* helper function for addSelector */
    private function addCheckSelector($key)
    {
        $button = "<input type=\"checkbox\" onchange=\"changeInput(event, '$key')\"></input>";
        return $button;
    }

    public function addAction($action, $symbol, $text)
    {
        if ($this->data == null) {
            return null;
        }

        if ($this->keys == null) {
            $this->createKeys();
        }

        $array = [];
        for ($i = 0; $i < sizeof($this->data); $i++) {
            $key = $this->keys[$i];

            if ($action != null) {
                $btn = "<button class='p-1 mr-1 actionButton' onclick=\"$action('$key', event)\" title='$text'>$symbol</button>";
            } else {
                $btn = "<button class='p-1 mr-1 actionButton' onclick=\"performAction('$key', event)\" title='$text'>$symbol</button>";
            }
            $array[$i] = $btn;
        }
        $this->addColumn("Aktionen", $array);
    }

    public function addActionButton($button, $identifier = null, $update = null)
    {
        if ($this->data == null) {
            return 0;
        }

        if ($this->keys == null) {
            $this->createKeys();
        }

        switch ($button) {
            case "update":
                $this->buttonUpdate = !$this->buttonUpdate;
                $array = [];

                for ($i = 0; $i < sizeof($this->data); $i++) {
                    $btn = $this->addUpdateButton($this->keys[$i]);
                    $array[$i] = $btn;
                }
                $this->addColumn("Aktionen", $array);

                if ($identifier != null) {
                    $this->setIdentifier($identifier);
                }

                /* maybe later callback to Auftrag.php and logic for updates there */
                $this->update = $update;
                break;
            case "edit":
                $this->buttonUpdate = !$this->buttonEdit;
                $array = [];

                for ($i = 0; $i < sizeof($this->data); $i++) {
                    $btn = $this->addEditButton($this->keys[$i]);
                    $array[$i] = $btn;
                }
                $this->addColumn("Aktionen", $array);
                break;
            case "delete":
                $this->buttonUpdate = !$this->buttonDelete;

                $array = [];
                for ($i = 0; $i < sizeof($this->data); $i++) {
                    $btn = $this->addDeleteButton($this->keys[$i]);
                    $array[$i] = $btn;
                }
                $this->addColumn("Aktionen", $array);

                if ($identifier != null) {
                    $this->setIdentifier($identifier);
                }
                break;
            case "check":
                $array = [];
                for ($i = 0; $i < sizeof($this->data); $i++) {
                    $btn = $this->addCheck($this->keys[$i]);
                    $array[$i] = $btn;
                }
                $this->addColumn("Aktionen", $array);
                $this->buttonCheck = true;
                break;
            case "move":
                $array = [];
                for ($i = 0; $i < sizeof($this->data); $i++) {
                    $btn = $this->addMove($this->keys[$i]);
                    $array[$i] = $btn;
                }
                $this->addColumn("Aktionen", $array);

                break;
        }
    }

    /* action buttons */
    private function addUpdateButton($key): string
    {
        $button = "<button class='p-1 mr-1 actionButton' onclick=\"updateIsDone('$key', event)\" title='Als erledigt markieren.'>" . Icon::getDefault("iconCheck") . "</button>";
        return $button;
    }

    private function addEditButton($key): string
    {
        $button = "<button class='p-1 mr-1 actionButton' onclick=\"editRow('$key', this)\" title='Bearbeiten'>" . Icon::getDefault("iconEdit") . "</button>";
        return $button;
    }

    private function addDeleteButton($key): string
    {
        $button = "<button class='p-1 mr-1 actionButton' onclick=\"deleteRow('$key', '$this->type', this)\" title='Löschen'>" . Icon::getDefault("iconDelete") . "</button>";
        return $button;
    }

    private function addCheck($key): string
    {
        $check = "<input type=\"checkbox\" name=\"checkRow_$key\">";
        return $check;
    }

    private function addMove($key): string
    {
        $button = "<button class='actionButton moveRow' onmousedown=\"moveInit(event)\" onmouseup=\"moveRemove(event)\" title='Reihenfolge verändern' data-key=\"$key\">" . Icon::getDefault("iconMove") . "</button>";
        return $button;
    }

    /*
     * checks if a rowname already exists and returns boolean accordingly
    */
    private function rowNameExists($rowName): bool
    {
        foreach ($this->columnNames as $c) {
            if ($c['COLUMN_NAME'] == $rowName) {
                return true;
            }
        }
        return false;
    }

    public function addColumn($rowName, $data): void
    {
        if (sizeof($data) == sizeof($this->data)) {
            if ($this->rowNameExists($rowName)) {
                for ($i = 0; $i < sizeof($this->data); $i++) {
                    $this->data[$i][$rowName] .= $data[$i];
                }
            } else {
                for ($i = 0; $i < sizeof($data); $i++) {
                    $this->data[$i][$rowName] = $data[$i];
                }

                $columnName = [
                    "COLUMN_NAME" => $rowName
                ];
                array_push($this->columnNames, $columnName);
            }
        } else {
            throw new \Exception("Array sizes do not match");
        }
    }

    public function setIdentifier($val): void
    {
        if (is_string($val)) {
            $this->identifier = $val;
        } else {
            throw new \Exception("wrong data type, String required");
        }
    }

    public static function updateValue($table, $action, $key)
    {
        if (!is_string($table) || !is_string($action) || !is_string($key)) {
            return "data cannot be processed";
        }

        if (isset($_SESSION[$table])) {
            $actionObject = unserialize($_SESSION[$table]);
            //$actionObject->update($action, $key);

            if ($actionObject->callback != null) {
                $actionObject->callback();
            }

            $rowId = self::getIdentifierValue($table, $key);

            if ($action == "delete") {
                /* if there is a session object for deletion, then this one is used */
                if (isset($_SESSION[$table . "_del"])) {
                    $actionObject = unserialize($_SESSION[$table . "_del"]);
                    $actionObject->delete();
                } else {
                    DBAccess::deleteQuery("DELETE FROM $actionObject->type WHERE $actionObject->identifier = $rowId");
                }
            } elseif ($action == "check") {
                /* data string for checked rows is $_POST["checked"] as JSON */
                $data = $_POST["checked"];
                $data = json_decode($data, true);
            } elseif ($action == "update") {
                DBAccess::updateQuery("UPDATE $actionObject->type SET $actionObject->update WHERE $actionObject->identifier = $rowId");
            }
        } else {
            return "no data found";
        }
    }

    public static function getIdentifierValue(string $table, string $key)
    {
        $actionObject = unserialize($_SESSION[$table]);

        /* gets the row by key, then the row identifier for the db action is selected */
        if (is_array($actionObject->keys)) {
            $number = array_search($key, $actionObject->keys);

            $rowId = $actionObject->data[$number][$actionObject->getIdentifier()];

            return $rowId;
        } else {
            return null;
        }
    }

    public static function getValueByIdentifierColumn(string $table, string $key, string $column)
    {
        $actionObject = unserialize($_SESSION[$table]);

        /* gets the row by key, then the row identifier for the db action is selected */
        if (is_array($actionObject->keys)) {
            $number = array_search($key, $actionObject->keys);

            $result = $actionObject->data[$number][$column];

            return $result;
        } else {
            return null;
        }
    }

    /*
     * erstellt die Tabelle
     * wenn $this->data null ist, wird eine Nachricht zurückgegeben
     */
    public function getTable(bool $zeroTable = false): string
    {
        if ($this->data == null && !$zeroTable) {
            return "<p>Keine Einträge vorhanden</p>";
        }

        $html = "";

        if ($this->editable) {
            $html = "<table class='table-auto overflow-x-scroll w-full allowAddingContent' data-type='{$this->type}' data-key='{$this->dataKey}'";
        } else {
            $html = "<table class='table-auto overflow-x-scroll w-full' data-type='{$this->type}' data-key='{$this->dataKey}'>";
        }

        $html .= self::html_createTableHeader($this->columnNames);

        /* for each row of the result */
        for ($i = 0; $i < sizeof($this->data); $i++) {
            $row = $this->data[$i];

            if ($this->keys == null) {
                $html .= self::html_createRow2($row, $this->columnNames, $this->getLink($i), $this->dataset);
            } else {
                $html .= self::html_createRow2($row, $this->columnNames, $this->getLink($i), $this->dataset, true);
            }
        }

        $html .= "</table>";

        /* adds a button after table to verify the action */
        if ($this->buttonCheck) {
            $html .= "<button>Übernehmen</button><br>";
        }

        if ($this->addNewLineButtonTrue) {
            $html .= "<br><button class=\"addToTable\" data-table=\"$this->dataKey\">+</button>";
        }

        return $html;
    }

    public function getLink(int $id)
    {
        if (is_string($this->link)) {
            return $this->link;
        } elseif (!is_null($this->link)) {
            return $this->link->getLink($id);
        }
    }

    /*
     * function to generate a html button to add a new line to the table
     */
    public function addNewLineButton(bool $add = true): void
    {
        $this->addNewLineButtonTrue = $add;
    }

    /**
     * @param array<mixed> $row
     * @param array<mixed> $rowNames
     * @param string $link
     * @param array<mixed> $dataset
     * @param bool $lastColumnIsActionButton
     * @return string
     */
    private static function html_createRow2(array $row, array $rowNames, string $link, array $dataset, bool $lastColumnIsActionButton = false): string
    {
        $html = "<tr>";

        if ($dataset[0] == true) {
            $data = $row[$dataset[2]];
            $html = "<tr data-{$dataset[1]}=\"{$data}\">";
        }

        for ($i = 0; $i < sizeof($rowNames); $i++) {
            $column = $rowNames[$i]["COLUMN_NAME"];
            $cssClasses = $rowNames[$i]["CSS"] ?? "";

            $nowrap = false;
            if (isset($rowNames[$i]["NOWRAP"])) {
                $nowrap = $rowNames[$i]["NOWRAP"];
            }
            $nowrap = $nowrap ? "nowrap" : "";

            $data = $row[$column];

            /* sets the link to null, if the last column is reached and it is an action button in this column */
            if ($lastColumnIsActionButton == true && $i == sizeof($rowNames) - 1) {
                $link = null;
            }

            if ($link == null) {
                $html .= "<td class=\"$nowrap $cssClasses\">" . $data . "</td>";
            } else {
                $html .= "<td class=\"linkTable $nowrap $cssClasses\"><a href=\"$link\">" . $data . "</a>\r\n</td>";
            }
        }

        $html .= "</tr>";
        return $html;
    }

    /**
     * @param mixed $type
     * @return array<int, array<string, string>>|null
     */
    private static function getColumnNames($type): array|null
    {
        return DBAccess::selectColumnNames($type);
    }

    /**
     * @param array<int, mixed> $column_names
     * @return string
     */
    private static function html_createTableHeader(array $column_names): string
    {
        $table_header = "<tr>";

        foreach ($column_names as $entry) {
            $showColumnName = $entry["COLUMN_NAME"];

            if (isset($entry["ALT"])) {
                $showColumnName = $entry["ALT"];
            }

            $table_header .= "<th class='tableHead whitespace-nowrap' onclick='sortTableNew(event)'>" . $showColumnName . " <span class='sortIcon'>" . Icon::getDefault("iconSortUndirected") . "</span></th>";
        }

        return $table_header . "</tr>";
    }
}
