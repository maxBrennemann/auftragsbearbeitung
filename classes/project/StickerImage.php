<?php

require_once('classes/project/StickerShopDBController.php');

class StickerImage {

    private $id;
    private $name;
    private $number;

    private $shopProducts;

    public $files;

    function __construct($id) {
        /*$query = "SELECT dateiname, typ, motive.name, motive.id FROM motive JOIN dateien_motive ON dateien_motive.id_motive = motive.id, JOIN dateien dateien.id = dateien_motive.id_datei WHERE motive.id = $id";
        $data = DBAccess::selectQuery($query);
        $this->id = $data[0]["id"];
        $this->files = [];

        foreach ($data as $d) {
            array_push($this->files, $d["dateiname"]);
        }*/

        $this->id = $id;
    }

    public static function creatStickerImage() {
        $query = "";
        $id = DBAccess::insertQuery($query);
        return new StickerImage($id);
    }

    private function generateLinks() {

    }

    public function isInShop() {
        $query = "SELECT * FROM prstshp_product WHERE reference = {$this->id}";
        $stickerDB = new StickerShopDBController();
        $stickerDB->select($query);
        $this->shopProducts = $stickerDB->getResult();
    }

    public function getSizeTable() {
        $query = "SELECT id, width, height FROM module_sticker_sizes WHERE id_sticker = {$this->id}";
        $data = DBAccess::selectQuery($query);
        $column_names = array(
            0 => array("COLUMN_NAME" => "id", "ALT" => "Kombinummer"),
            1 => array("COLUMN_NAME" => "width", "ALT" => "Breite"),
            2 => array("COLUMN_NAME" => "height", "ALT" => "Höhe")
        );

        foreach ($data as &$d) {
            $d["width"] = str_replace(".", ",", ((int) $d["width"]) / 10) . "cm";
            $d["height"] = str_replace(".", ",", ((int) $d["height"]) / 10) . "cm";
        }

		$t = new Table();
		$t->createByData($data, $column_names);
		$t->addActionButton("edit");
		$t->setType("schritte");
		$t->addActionButton("delete", "id");
		$t->addNewLineButton();
			
        $pattern = [
            "id_sticker" => [
                "status" => "preset",
                "value" => $this->id,
            ],
            "width" => [
                "status" => "unset",
                "value" => 1,
                "type" => "cm",
                "cast" => [],
            ],
            "height" => [
                "status" => "unset",
                "value" => 2,
                "type" => "cm",
                "cast" => [],
            ],
        ];

		$t->defineUpdateSchedule(new UpdateSchedule("module_sticker_sizes", $pattern));
        $_SESSION[$t->getTableKey()] = serialize($t);
		return $t->getTable();
    }

}

?>