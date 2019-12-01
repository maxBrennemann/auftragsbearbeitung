<?php
    require_once('classes/project/FormGenerator.php');

    $verbesserungen = DBAccess::selectQuery("SELECT verbesserungen AS Verbesserungen, erledigt FROM verbesserungen");
    $column_names = array(0 => array("COLUMN_NAME" => "Verbesserungen"), 1 => array("COLUMN_NAME" => "erledigt"));
    $table = new FormGenerator("", "", "");
    echo $table->createTableByData($verbesserungen, $column_names);
?>
<textarea id="verbesserung"></textarea>
<input type="button" id="submit" value="Abschicken">
<script>
    document.getElementById("submit").addEventListener("click", function(event) {
        var data = document.getElementById("verbesserung").value;
        var getHTML = new AjaxCall(`getReason=insertVerbesserung&verbesserung=${data}`, "POST", window.location.href);
        getHTML.makeAjaxCall(function (response) {
            location.reload();
        });
    });
</script>