<?php

require_once("classes/project/modules/sticker/Sticker.php");
require_once("classes/project/modules/sticker/Aufkleber.php");
require_once("classes/project/modules/sticker/Wandtattoo.php");
require_once("classes/project/modules/sticker/Textil.php");

class StickerCollection implements Iterator {

    private $products = [];
    private $current = 0;
    private $position = 0;

    private $exports = [];

    private int $id;
    private Sticker $sticker;

    function __construct(int $id) {
        $this->id = $id;
        $this->sticker = new Sticker($id);

        $this->products[0] = new Aufkleber($this->id);
        $this->products[1] = new Wandtattoo($this->id);
        $this->products[2] = new Textil($this->id);
    }

    public function getName(): String {
        return $this->sticker->getName();
    }

    public function getCreationDate() {
        return $this->sticker->getCreationDate();
    }

    public function getDirectory() {
        return $this->sticker->getDirectory();
    }

    public function getIsMarked() {
        return $this->sticker->getIsMarked();
    }

    public function getIsRevised() {
        return $this->sticker->getIsRevised();
    }

    public function getAdditionalInfo() {
        return $this->sticker->getAdditionalInfo();
    }

    public function getExportStatus($export): bool {
        if ($this->exports == []) {
            $query = "SELECT * FROM module_sticker_exports WHERE `idSticker`= :idSticker";
            $data = DBAccess::selectQuery($query, ["idSticker" => $this->id]);

            // TODO: code ändern, äußerst unschön
            if ($data == null) {
                DBAccess::insertQuery("INSERT INTO module_sticker_exports (`idSticker`) VALUES (:idSticker)", ["idSticker" => $this->id]);

                $query = "SELECT * FROM module_sticker_exports WHERE `idSticker`= :idSticker";
                $data = DBAccess::selectQuery($query, ["idSticker" => $this->id]);
            }

            $this->exports = $data[0];
            // TODO: insert mysql trigger and update table in sql updater
        }

        return $this->exports[$export] != null;
    }

    /* Iterator */
    public function current(): mixed {
        return $this->getTarget($this->current);
    }

    public function key(): mixed {
        return $this->position;
    }

    public function rewind(): void {
        $this->position = 0;
    }

    public function next(): void {
        ++$this->position;
    }

    public function valid(): bool {
        return isset($this->products[$this->position]);
    }

    public function createAll() {
        $this->getAufkleber();
        $this->getWandtattoo();
        $this->getTextil();
    }

    public function getAufkleber() {
        return $this->products[0];
    }

    public function getWandtattoo() {
        return $this->products[1];
    }

    public function getTextil() {
        return $this->products[2];
    }

    public function getTarget($type) {
        $target = null;
        switch ($type) {
            case "aufkleber":
                $target = $this->getAufkleber();
                break;
            case "wandtattoo":
                $target = $this->getWandtattoo();
                break;
            case "textil":
                $target = $this->getTextil();
                break;
        }

        return $target;
    }

    public function toggleActiveStatus() {
        $type = (String) $_POST["type"];

        $target = $this->getTarget($type);
        $status = $target->toggleActiveStatus();

        echo json_encode([
            "status" => "success",
            "icon" => $status,
        ]);
    }

    /* updates or uploads all products and writes connections */
    public function uploadAll() {
        // TODO: implement function
    }

}

?>