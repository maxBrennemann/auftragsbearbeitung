<?php 
require_once('classes/project/FormGenerator.php');

$productId = -1;

if (isset($_GET['id'])) {
	$productId = $_GET['id'];
}

if (isset($_POST['filesubmitbtn'])) {
	$upload = new Upload();
	$upload->uploadFilesProduct($productId);
}

$quelle = DBAccess::selectQuery("SELECT name, id FROM einkauf");
$attributeGroups = DBAccess::selectQuery("SELECT * FROM attribute_group");

echo "<a href=\"" . Link::getPageLink("attributes") . "\">Zu den Produktattributetn</a>";
?>
<div class="defCont">
	<p>
		<span>Marke
			<input class="dataInput" type="text" name="marke" required>
		</span>
	</p>
	<p>
		<span>Quelle
			<select id="selectSource" required>
				<option value="-1" selected disabled>Bitte auswählen</option>
				<?php foreach ($quelle as $q): ?>
					<option value="<?=$q['id']?>"><?=$q['name']?></option>
				<?php endforeach; ?>
				<option value="addNew">Neue Option hinzufügen</option>
			</select>
		</span>
	</p>
	<p>
		<span>Verkaufspreis Netto
			<input class="dataInput" type="text" name="vk_netto" required>
		</span>
	</p>
	<p>
		<span>Einkaufspreis Netto
			<input class="dataInput" type="text" name="plz" required>
		</span>
	</p>
	<p>
		<span>Kurzbezeichnung / Titel
			<input class="dataInput" type="text" name="short_description" max="64" required>
		</span>
	</p>
	<p>
		<span>Beschreibung<br>
			<textarea class="dataInput" type="text" name="description"></textarea>
		</span>
	</p>
	<p>
		<span>Attribute hinzufügen
			<button onclick="getHTMLForAttributes();">Hinzufügen</button>
		</span>
	</p>
	<form method="post" enctype="multipart/form-data">
		Dateien hinzufügen:
		<input type="file" name="uploadedFile">
		<input type="submit" value="Datei hochladen" name="filesubmitbtn">
	</form>
	<input type="submit">
</div>