<?php

require_once("classes/project/modules/sticker/Aufkleber.php");
require_once("classes/project/modules/sticker/Wandtattoo.php");
require_once("classes/project/modules/sticker/Textil.php");

class StickerCollection {

    private $aufkleber;
    private $wandtattoo;
    private $textil;

    private $id;

    function __construct(int $id) {
        $this->id = $id;
    }

    public function getAufkleber() {
        if ($this->aufkleber == null) {
            $this->aufkleber = new Aufkleber($this->id);
        }

        return $this->aufkleber;
    }

    public function getWandtattoo() {
        if ($this->wandtattoo == null) {
            $this->wandtattoo = new Wandtattoo($this->id);
        }

        return $this->wandtattoo;
    }

    public function getTextil() {
        if ($this->textil == null) {
            $this->textil = new Textil($this->id);
        }

        return $this->textil;
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

}

?>