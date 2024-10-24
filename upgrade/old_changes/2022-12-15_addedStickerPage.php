<?php

require_once "UpdateMySql.php";

return new class extends UpdateMySql {

    private $query1 = "INSERT INTO `articles` (`id`, `articleUrl`, `pageName`, `src`) VALUES (NULL, 'sticker.php', 'Sticker', 'sticker')";
    private $query2 = "UPDATE `articles` SET `articleUrl` = 'stickerOverview.php' WHERE `articles`.`articleUrl` = 'stickerImage.php'";
    private $query3 = "UPDATE `articles` SET `src` = 'sticker-add' WHERE `articles`.`articleUrl` = 'addSticker.php'
    ";

    public function upgrade() {
        DBAccess::insertQuery($this->query1);
        DBAccess::insertQuery($this->query2);
        DBAccess::insertQuery($this->query3);

        return "upgraded database";
    }

    public function downgrade() {
        DBAccess::deleteQuery("DELETE FROM articles WHERE articleUrl = 'sticker.php'");
    }

    /* testing if anonymous class is accessible */
    public function getHello() {
        return "hello";
    }

}

?>