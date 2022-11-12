<?php
    require_once('classes/Upload.php');

    $externalUrl = "https://klebefux.de/folienplotter_files/img/clothes_img/test/bc_men_black.jpg";

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