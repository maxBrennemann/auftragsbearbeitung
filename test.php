<?php

class UpdateSchedule {

    private $tableName;
    private $pattern;

    private $columns;
    private $values;

    function __construct($tableName, $pattern) {
        $this->tableName = $tableName;
        $this->pattern = $pattern;
    }

    public function executeTableUpdate($data) {
        $this->applyPattern($data);

        $query = "INSERT INTO $this->tableName ($this->columns) VALUES ($this->values)";
        echo $query;
        //DBAccess::insertQuery($query);
    }

    private function applyPattern($data) {
        $columns = $values = "";

        foreach ($this->pattern as $key => $value) {
            $columns .= $key . ", ";

            /* checks if value is preset or it is in data array */
            $val = "";
            if ($value['status'] == "preset") {
                $val = $value['value'];
            } else if ($value['status'] == "unset") {
                $val = $data[$value['value']];
            }

            /* checks if value is string or int to insert it correctly */
            if (is_string($val)) {
                $values .= "'$val', ";
            } else if (is_int($val)) {
                $values .= $val . ", ";
            }
        }

        $this->columns =  substr($columns, 0, -2);
        $this->values =  substr($values, 0, -2);
    }

}

$pattern = [
    "Kundennummer" => [
        "status" => "preset",
        "value" => 66
    ],
    "Vorname" => [
        "status" => "unset",
        "value" => 0
    ],
    "Nachname" => [
        "status" => "unset",
        "value" => 1
    ],
    "Email" => [
        "status" => "unset",
        "value" => 2
    ],
    "Durchwahl" => [
        "status" => "unset",
        "value" => 3
    ],
    "Mobiltelefonnummer" => [
        "status" => "unset",
        "value" => 4
    ]
];
$us = new UpdateSchedule("ansprechpartner", $pattern);

$data = '{"0":"test","1":"test","2":"test","3":"test","4":"test","5":""}';
$data = json_decode($data, true);

$us->executeTableUpdate($data);

/*require_once('classes/project/PDF_Auftrag.php');
//PDF_Auftrag::getPDF();

$result = ["id" => -1, "articleUrl" => "test", "pageName" => "test"];
$articleUrl = $result["articleUrl"];
$pageName = $result["pageName"];

require_once('classes/project/Table.php');

include('files/header.php');

$t = new Table("kunde", 10);
//$t->addColumn("test", ["test"]);
//$t->addRow(["id" => 37, "articleUrl" => "none", "pageName" => "tolle seite", "src" => "keine Qeulle", "test" => "test"]);
//$t->addLink("https://klebefux.de");
$t->addActionButton("check", $identifier = "Kundennummer");

echo $t->getTable();

$_SESSION["undefined"] = serialize($t);

?>Test

<script>
    function updateIsDone(key) {
        var tableId = document.querySelector("table").dataset.name;
        //var key = event.target.dataset.key;
        var setTo = "37";
        var editTable = new AjaxCall(`getReason=table&name=${tableId}&action=update&key=${key}&setTo=${setTo}`);
        editTable.makeAjaxCall(function (response) {
            console.log(response);
        });
    }
    function deleteRow(key) {
        var tableId = document.querySelector("table").dataset.name;
        //var key = event.target.dataset.key;
        var setTo = "37";
        var editTable = new AjaxCall(`getReason=table&name=${tableId}&action=delete&key=${key}&setTo=${setTo}`);
        editTable.makeAjaxCall(function (response) {
            console.log(response);
        });
    }
</script>

<div>
    <form class="fileUploader" method="post" enctype="multipart/form-data" data-target="order">
        Dateien zum Auftrag hinzufügen:
        <input type="file" name="uploadedFile" multiple>
    </form>
    <div class="filesList defCont"></div>
</div>

<?php

if (isset($_GET['ajaxUpload'])) {
    echo "ajaxUpload";
}*/

include('files/footer.php');




return null;
?>