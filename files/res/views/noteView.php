<?php foreach ($notes as $note) :?>
    <div class="notes">
        <div class="noteheader">Notiz 
            <span class="inline"><?=$icon?></span>
        </div>
        <div class="notecontent"><?=$note['Notiz']?></div>
        <div class="notebutton" data-binding="true" data-fun="removeNote" data-note-id="<?=$note["Nummer"]?>">×</div>
    </div>
<?php endforeach; ?>