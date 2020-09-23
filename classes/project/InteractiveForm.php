<?php 

require_once('classes/DBAccess.php');

class InteractiveForm {


    function __construct() {
        
    }

    public function addActionColumn() {

    }

    private function addActionButton() {

    }

    private function addUpdateButton() {
        $button = "<button class='actionButton' onclick=\"updateIsDone($row)\" title='Als erledigt markieren.'>&#x2714;</button>";
		return $button;
    }

    private function addEditButton() {
        $button = "<button class='actionButton' onclick=\"editRow()\" = 'Bearbeiten' disabled>&#x270E;</button>";
		return $button;
    }

    private function addDeleteButton($row) {
		$button = "<button class='actionButton' onclick=\"deleteRow($row)\" title='Löschen'>&#x1F5D1;</button>";
		return $button;

    }

}

?>