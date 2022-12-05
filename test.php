<?php

define('_PS_CORE_DIR_', 'null');

class ImageCreationTest {

    private function imageCreation($motivId, $motivColor, $shirtId, $shirtColor) {
        $svgPath = _PS_CORE_DIR_ ."/mainconf/img/svgFiles/{$motivId}.svg";
        if ($motivColor == "unset") {
            $imagePath = _PS_CORE_DIR_ ."/mainconf/img/imageFiles/{$shirtId}_{$shirtColor}_{$motivId}.png";
        } else {
            $imagePath = _PS_CORE_DIR_ ."/mainconf/img/imageFiles/{$shirtId}_{$shirtColor}_{$motivId}_{$motivColor}.png";
        }
        if (!file_exists($imagePath)) {
            /* load contents into the variables */
            $svgFile = file_get_contents($svgPath);
            $imageFile = file_get_contents(_PS_CORE_DIR_ ."/mainconf/img/product/{$shirtId}/{$shirtColor}.jpg");
        
            /* sets the color of the motiv */
            $dom = new DOMDocument('1.0', 'utf-8');
            $dom->load($svgPath);
            $svg = $dom->documentElement;

            if ($motivColor != "unset") {
                $svg->setAttribute("fill", "#" . $this->getColor($motivColor));
            }
            $svg->setAttribute("width", "1210px");
            $svg->setAttribute("height", "1452px");
            $svgFile = $dom->saveXML();

            /* create the image data with imagick */
            $layoutData = new Imagick();
            $layoutData->setBackgroundColor(new ImagickPixel('transparent'));
            $layoutData->readImageBlob($svgFile);
            $layoutData->setImageFormat("png24");
            $layoutData->setImageColorSpace(3);
            
            $backgroundImage = new Imagick();
            $backgroundImage->readImageBlob($imageFile);
            $backgroundImage->setImageColorSpace(3);
            $backgroundImage->compositeImage($layoutData, Imagick::COMPOSITE_DEFAULT, 0, 0);
            
            $backgroundImage->writeImage($imagePath);
            $backgroundImage->clear();
            $backgroundImage->destroy();
        }
    }

    private function getColor($colorName) {
		$colors = array(
            'maisgelb' => 'FCCC00',
            'gelb' => 'F5E61A',
            'rot' => '910C19',
            'orange' => 'DB3400',
            'schwarz' => '000000',
            'konigsblau' => '11307D',
            'enzianblau' => '0053AA',
            'helltuerkis' => '009999',
            'dunkelgruen' => '004429',
            'hellgruen' => '008955',
            'apfelgruen' => '60C340',
            'braun' => '45291E',
            'anthrazit' => '2C2E31',
            'silber' => '748289',
            'grau' => '878A8D',
            'weiss' => 'FFFFFF',
            'neongelb' => 'ccff00',
            'neongruen' => '00ff00',
            'neonorange' => 'fd5f00',
            'neonpink' => 'ff019a'
        );

        return $colors[$colorName];
	}
}

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