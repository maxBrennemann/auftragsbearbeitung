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
    $verbesserungen;

    if (isset($_GET['t']) && $_GET['t'] == 'details') {
        $query = "SELECT verbesserungen AS Verbesserungen, erledigt, erstelldatum AS Datum, mitarbeiter.Vorname AS `Erstellt von` 
        FROM verbesserungen
        LEFT JOIN members ON verbesserungen.creator = members.id
        LEFT JOIN members_mitarbeiter ON members_mitarbeiter.id_member = members.id
        LEFT JOIN mitarbeiter ON mitarbeiter.id = members_mitarbeiter.id_mitarbeiter
        ORDER BY Datum DESC";
        $verbesserungen = DBAccess::selectQuery($query);
    } else {
        $query = "SELECT verbesserungen AS Verbesserungen, erledigt, erstelldatum AS Datum, mitarbeiter.Vorname AS `Erstellt von` 
        FROM verbesserungen
        LEFT JOIN members ON verbesserungen.creator = members.id
        LEFT JOIN members_mitarbeiter ON members_mitarbeiter.id_member = members.id
        LEFT JOIN mitarbeiter ON mitarbeiter.id = members_mitarbeiter.id_mitarbeiter
        WHERE erledigt = ''
        ORDER BY Datum DESC";
        $verbesserungen = DBAccess::selectQuery($query);
    }

    $column_names = array(
        0 => array("COLUMN_NAME" => "Verbesserungen"),
        1 => array("COLUMN_NAME" => "Erstellt von"),
        2 => array("COLUMN_NAME" => "erledigt"),
        3 => array("COLUMN_NAME" => "Datum")
    );

    $table = new Table();
	$table->createByData($verbesserungen, $column_names);

    echo "<div id=\"tableContainer\">" . $table->getTable() . "</div>";
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