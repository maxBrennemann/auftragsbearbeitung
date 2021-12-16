<?php
    require_once('classes/Link.php');
    $link = Link::getPageLink("verbesserungen");
?>
<p><a href="<?=$link?>?t=details">Details</a> oder <a href="<?=$link?>?t=unsolved">nicht erledigte</a> Verbesserungen.</p>
<div class="defCont">
    <p>Nachricht eingeben:</p>
    <input id="verbesserung">
    <br><br>
    <input type="button" id="submit" value="Abschicken">
    <script>
        document.getElementById("submit").addEventListener("click", function(event) {
            var data = document.getElementById("verbesserung").value;
            var getHTML = new AjaxCall(`getReason=insertVerbesserung&verbesserung=${data}`, "POST", window.location.href);
            getHTML.makeAjaxCall(function (response) {
                location.reload();
            });
        });

        let verbesserungsInput = document.getElementById("verbesserung");
        verbesserungsInput.addEventListener("keyup", function (event) {
            if (event.key === "Enter") {
                document.getElementById("submit").click();
            }
        });
    </script>
</div>
<br>
<?php
    require_once('classes/project/FormGenerator.php');

    $verbesserungen;

    if (isset($_GET['t']) && $_GET['t'] == 'details') {
        $verbesserungen = DBAccess::selectQuery("SELECT verbesserungen AS Verbesserungen, erledigt FROM verbesserungen");
    } else {
        $verbesserungen = DBAccess::selectQuery("SELECT verbesserungen AS Verbesserungen, erledigt FROM verbesserungen WHERE erledigt = ''");
    }

    $column_names = array(0 => array("COLUMN_NAME" => "Verbesserungen"), 1 => array("COLUMN_NAME" => "erledigt"));
    $table = new FormGenerator("", "", "");
    echo $table->createTableByData($verbesserungen, $column_names);
?>