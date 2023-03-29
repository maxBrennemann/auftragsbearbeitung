<?php

class AufkleberWandtattoo extends Sticker {

    public function getPrice($width, $height, $difficulty) {
        if ($width >= 1200) {
            $base = 2100;
        } else if ($width >= 900) {
            $base = 1950;
        } else if ($width >= 600) {
            $base = 1700;
        } else if ($width >= 300) {
            $base = 1500;
        } else {
            $base = 1200;
        }

        $base = $base + 200 * $difficulty;
        if ($height >= 0.5 * $width) {
            $base += 100;
        }
        
        return $base;
    }

    public function getBasePrice() {
        parent::getBasePrice();

        $query = "SELECT price FROM module_sticker_sizes WHERE id_sticker = :idSticker ORDER BY price ASC LIMIT 1;";
        $params = ["idSticker" => $this->idSticker];
        $result = DBAccess::selectQuery($query, $params);

        if ($result == null) {
            $result[] = ["price" => "1000"];
        }
        
        return number_format((float) $result[0]["price"] / 100, 2, '.', '');
    }
}

?>