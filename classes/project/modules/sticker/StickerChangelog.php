<?php

class StickerChangelog /*implements StickerShopController*/ {

    private $idSticker;
    private $changelogData;

    function __construct(int $idSticker) {
        $this->idSticker = $idSticker;
        $query = "SELECT * FROM `module_sticker_changelog` WHERE id_sticker = :idSticker";
        $this->changelogData = DBAccess::selectQuery($query, ["idSticker" => $idSticker]);
    }

    public function getStickerId() {
        return $this->idSticker;
    }

    public function getChangelogData() {
        return $this->changelogData;
    }

    public function getTable() {
        $column_names = array(
            0 => array("COLUMN_NAME" => "id", "ALT" => "Nummer"),
            1 => array("COLUMN_NAME" => "type", "ALT" => "Art"),
            2 => array("COLUMN_NAME" => "table", "ALT" => "Tabelle (intern)"),
            3 => array("COLUMN_NAME" => "column", "ALT" => "Spalte (intern)"),
            4 => array("COLUMN_NAME" => "newValue", "ALT" => "Neuer Wert"),
        );

        $t = new Table();
		$t->createByData($this->changelogData, $column_names);
		return $t->getTable();
    }

    /**
     * Logs new or changed entries into the sticker data module
     * @param int $stickerId the id of the current sticker
     * @param String $stickerType aufkleber, wandtattoo or textil
     * @param int $rowId specific id to identify the changed row
     * @param mixed $table the table in which the content changed
     * @param mixed $column the column in which the content changed
     * @param mixed $newValue if it is the first entry, it is the init value, else it is the new value
     * @return null
     */
    public static function log(int $stickerId, String $stickerType, int $rowId, $table, $column, $newValue) {
        $query = "INSERT INTO `module_sticker_changelog` (`id_sticker`, `type`, `rowId`, `table`, `column`, `newValue`) VALUES (:id_sticker, :type, :rowId, :table, :column, :newValue)";
        $values =  [
            "id_sticker" => $stickerId,
            "type" => $stickerType,
            "rowId" => $rowId,
            "table" => $table,
            "column" => $column,
            "newValue" => $newValue,
        ];
        DBAccess::insertQuery($query, $values);
    }

    /**
     * reverts the last change from the changelog table
     * @param int $stickerId the stickerId where a changelog must be reversed
     * @param String $stickerType the type of the rollback
     * @param int $rowId specific row identifier
     * @return boolean true if the revert was successfull
     */
    public static function revert(int $stickerId, String $stickerType, int $rowId) {
        $lastChanges = DBAccess::selectQuery("SELECT * FROM `module_sticker_changelog` WHERE `id_sticker` = :id_sticker AND `type` = :type ORDER BY id DESC LIMIT 2", ["id_sticker" => $stickerId, "type" => $stickerType]);

        if ($lastChanges == null) {
            return false;
        }

        if (sizeof($lastChanges) == 2) {
            $revert = $lastChanges[0];
            $before = $lastChanges[1];

            $table = $revert["table"];
            $column = $revert["row"];
            $value = $before["newValue"];

            /* TODO: fallback einbauen, falls keine id vorhanden */
            DBAccess::updateQuery("UPDATE $table SET $column = $value WHERE id = $rowId");

            $revertId = $revert["id"];
            DBAccess::deleteQuery("DELETE FROM `module_sticker_changelog` WHERE id = $revertId");

            return true;
        } else if (sizeof($lastChanges) == 1) {
            $revert = $lastChanges[0];

            $table = $revert["table"];
            $column = $revert["row"];
            $value = $revert["newValue"];

            /* TODO: fallback einbauen, falls keine id vorhanden */
            DBAccess::updateQuery("DELETE FROM $table WHERE id = $rowId AND $column = $value");

            $revertId = $revert["id"];
            DBAccess::deleteQuery("DELETE FROM `module_sticker_changelog` WHERE id = $revertId");

            return true;
        }

        return false;
    }

    /*
     TODO: implement in this manner: https://stackoverflow.com/questions/12563706/is-there-a-mysql-option-feature-to-track-history-of-changes-to-records/12657012#12657012
    */
}

?>