<?php

class Textil extends Sticker {

    /*
     * SELECT color, prstshp_attribute_lang.name FROM `prstshp_attribute`, prstshp_attribute_lang WHERE prstshp_attribute.id_attribute = prstshp_attribute_lang.id_attribute AND id_attribute_group = 11 AND prstshp_attribute_lang.id_lang = 1; 
     * TODO: read from shop and cache
     */
    public $textilColors = [
        ["hexCol" => "#ffffff", "name" => "Weiss"],
        ["hexCol" => "#FCCC00", "name" => "Maisgelb"],
        ["hexCol" => "#F5E61A", "name" => "Gelb"],
        ["hexCol" => "#910C19", "name" => "Rot"],
        ["hexCol" => "#DB3400", "name" => "Orange"],
        ["hexCol" => "#000000", "name" => "Schwarz"],
        ["hexCol" => "#11307D", "name" => "Königsblau"],
        ["hexCol" => "#0053AA", "name" => "Enzianblau"],
        ["hexCol" => "#009999", "name" => "Helltuerkis"],
        ["hexCol" => "#004429", "name" => "Dunkelgruen"],
        ["hexCol" => "#008955", "name" => "Hellgruen"],
        ["hexCol" => "#60C340", "name" => "Apfelgruen"],
        ["hexCol" => "#45291E", "name" => "Braun"],
        ["hexCol" => "#2C2E31", "name" => "Anthrazit"],
        ["hexCol" => "#748289", "name" => "Silber"],
        ["hexCol" => "#878A8D", "name" => "Grau"],
        ["hexCol" => "#ccff00", "name" => "Neongelb"],
        ["hexCol" => "#00ff00", "name" => "Neongruen"],
        ["hexCol" => "#fd5f00", "name" => "Neonorange"],
        ["hexCol" => "#ff019a", "name" => "Neonpink"],
    ];

    private $svg;

    private function uploadSVG() {
        $url = $this->url . "?upload=svg&id=$this->idProduct";
        $cImage = new CurlFile($this->svg, 'image/svg+xml', "image");

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('image' => $cImage));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        echo $result;
    }

    public function uploadImages($imageURLs) {
        parent::uploadImages($imageURLs);
        $this->uploadSVG();
    }

}

?>