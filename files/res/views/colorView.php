<?php foreach ($farben as $farbe) :?>
    <div class="singleColorContainer">
        <p class="singleColorName"><?=$farbe['Farbe']?>, <?=$farbe['Hersteller']?>: <?=$farbe['Bezeichnung']?></p>
        <div class="farbe" style="background-color: #<?=$farbe['Farbwert']?>"></div>
        <button onclick="removeColor(<?=$farbe['Nummer']?>);">×</button>
    </div><br>
<?php endforeach; ?>