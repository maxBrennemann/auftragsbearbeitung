<?php 
    $externalUrl = "https://klebefux.de/folienplotter_files/img/clothes_img/test/bc_men_black.jpg";

    if (isset($_POST['filesubmitbtn'])) {
		$upload = new Upload();
		$upload->uploadFilesMotive();
	}

    if () :
?>
<div class="defCont">
    <h3>Motiv</h3>
    <form method="post" enctype="multipart/form-data">
        Motiv hochladen:<br>
        <input type="file" name="uploadedFile">
        <input type="submit" value="Datei hochladen" name="filesubmitbtn">
    </form>
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
    <h3>Motiv in Textil einpassen</h3>
</div>
<button class="defCont">Speichern</button>