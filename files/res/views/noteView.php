<?php foreach ($notes as $note) :?>
    <div class="notes">
        <div class="noteheader">Notiz 
            <span class="inline"><?=$iconNotebook?></span>
        </div>
        <div class="notecontent"><?=$note['Notiz']?></div>
        <div class="notebutton" data-binding="true" data-fun="removeNote">×</div>
    </div>
<?php endforeach; ?>