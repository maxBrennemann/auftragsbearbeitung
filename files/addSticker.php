<?php
    require_once('classes/Upload.php');

    $externalUrl = "https://klebefux.de/folienplotter_files/img/clothes_img/test/bc_men_black.jpg";
    $id = -1;

    function prepareSVG($svgData) {
        $replarr = array("fill:none", "fill: none");
        foreach ($replarr as $el) {
            $svgData = str_replace($el, "", $svgData);
        }

        while ($pos = strpos($svgData, "fill:#") != false) {
            $svgData = substr_replace($svgData, "", $pos, 12);
        }

        while ($pos = strpos($svgData, "fill: #") != false) {
            $svgData = substr_replace($svgData, "", $pos, 13);
        }
    }

    if (isset($_GET['id'])) {
        $id = (int) $_GET['id'];
        $motivdata = DBAccess::selectQuery("SELECT `motive`.`id`, `motive`.`name`, dateien.dateiname, dateien.originalname FROM `motive`, dateien_motive, dateien WHERE motive.id = dateien_motive.id_motive AND dateien_motive.id_datei = dateien.id");
        $motivname = $motivdata[0]['name'];

        $imageLink = Link::getResourcesShortLink($motivdata[0]['dateiname'], "upload");
    }

    /*if (isset($_POST['filesubmitbtn'])) {
        $motivname = $_POST['motivname'];
		$upload = new Upload();
		$upload->uploadFilesMotive($motivname);
    }*/

    if ($id == -1) :
?>
<div class="defCont">
    <h3>Motiv</h3>
    <form class="fileUploader" method="post" enctype="multipart/form-data" data-target="motiv" id="uploadFilesMotive" name="motivUpload">
        <input name="motiv" hidden>
        <label for="motivname">Motivname: </label>
        <input type="text" max="64" id="motivname" name="motivname" required><br>
    </form>
    <p>Hier Dateien per Drag&Drop ablegen oder 
        <label class="uploadWrapper">
            <input type="file" name="uploadedFile" multiple class="fileUploadBtn" form="uploadFilesMotive">
            hier hochladen
        </label>
    </p>
    <div class="filesList defCont"></div>
    <div id="showFilePrev"></div>
</div>
<?php elseif ($id >= 1) : ?>
    <div class="defCont">
    <h3>Motiv: "<b><?=$motivname?></b>"</h3>
    <img src="<?=$imageLink?>" alt="<?=$motivname?>" width="150px" heigth="auto">
</div>
<div class="defCont">
    <h3>Wo soll das Motiv verwendet werden?</h3>
    <input type="checkbox" name="textil">
    <label for="textil">Motiv auf Textil</label>
    <br>
    <input type="checkbox" name="aufkleber">
    <label for="aufkleber">Motiv als Aufkleber</label>
    <br>
    <input type="checkbox" name="motiv">
    <ilabel for="motiv">Motiv in den Konfiguratoren anbieten</label>
</div>
<div class="defCont">
    <h3>Hintergrund auswählen</h3>
</div>
<div class="defCont">
    <h3>Motiv in Textil einpassen</h3>
</div>
<button class="defCont">Speichern</button>
<?php endif; ?>