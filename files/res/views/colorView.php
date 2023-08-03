<?php foreach ($farben as $farbe) :?>
    <div class="singleColorContainer">
        <p class="singleColorName"><?=$farbe['Farbe']?>, <?=$farbe['Hersteller']?>: <?=$farbe['Bezeichnung']?></p>
        <div class="farbe" style="background-color: #<?=$farbe['Farbwert']?>"></div>
        <button data-binding="true" data-fun="removeColor" data-color="<?=$farbe['Nummer']?>">×</button>
    </div><br>
<?php endforeach; ?>