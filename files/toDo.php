<?php
    require_once('classes/Link.php');
    $link = Link::getPageLink("verbesserungen");
?>
<p><a href="<?=$link?>?t=details">Details</a> oder <a href="<?=$link?>?t=unsolved">nicht erledigte</a> Verbesserungen.</p>
<div class="defCont">
    <p>Nachricht eingeben:</p>
    <textarea id="verbesserung" type="text" max="128" oninput="this.style.height = '';this.style.height = this.scrollHeight + 'px'"></textarea>
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
    </script>
</div>
<br>
<?php
    require_once('classes/project/FormGenerator.php');

    $verbesserungen;

    if (isset($_GET['t']) && $_GET['t'] == 'details') {
        $verbesserungen = DBAccess::selectQuery("SELECT verbesserungen AS Verbesserungen, erledigt, erstelldatum AS Datum FROM verbesserungen ORDER BY Datum DESC");
    } else {
        $verbesserungen = DBAccess::selectQuery("SELECT verbesserungen AS Verbesserungen, erledigt, erstelldatum AS Datum FROM verbesserungen WHERE erledigt = '' ORDER BY Datum DESC");
    }

    $column_names = array(
        0 => array("COLUMN_NAME" => "Verbesserungen"), 
        1 => array("COLUMN_NAME" => "erledigt"),
        2 => array("COLUMN_NAME" => "Datum")
    );
    $table = new FormGenerator("", "", "");
    echo "<div id=\"tableContainer\">" . $table->createTableByData($verbesserungen, $column_names) . "</div>";
?>
<style>
	 header {
        z-index: 2;
    }

	#tableContainer {
		position: relative;
		max-height: 500px;
		overflow: auto;
	}

	table {
        display: table;
        position: relative;
        text-align: left;
        z-index: 1;
    }

    tbody {
        display: table-header-group;
    }

	table th {
        position: -webkit-sticky;
		position: sticky;
        top: 0;
	}
</style>