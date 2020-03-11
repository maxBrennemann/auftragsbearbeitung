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
	<form method="post">
		<p>
			<label>Marke
				<input class="dataInput" type="text" name="marke" required>
			</label>
		</p>
		<p>
			<label>Quelle
				<select id="selectSource" required>
					<option value="-1" selected disabled>Bitte auswählen</option>
					<?php foreach ($quelle as $q): ?>
						<option value="<?=$q['id']?>"><?=$q['name']?></option>
					<?php endforeach; ?>
					<option value="addNew">Neue Option hinzufügen</option>
				</select>
			</label>
		</p>
		<p>
			<label>Verkaufspreis Netto
				<input class="dataInput" type="text" name="vk_netto" required>
			</label>
		</p>
		<p>
			<label>Einkaufspreis Netto
				<input class="dataInput" type="text" name="plz" required>
			</label>
		</p>
		<p>
			<label>Kurzbezeichnung / Titel
				<input class="dataInput" type="text" name="short_description" max="64" required>
			</label>
		</p>
		<p>
			<label>Beschreibung
				<input class="dataInput" type="text" name="description">
			</label>
		</p>
		<p>
			<label>Attribute
				<select id="selectAttributes" required>
					<option value="-1" selected disabled>Bitte auswählen</option>
					<?php foreach ($attributeGroups as $group): ?>
						<option value="<?=$group['id']?>"><?=$group['attribute_group']?></option>
					<?php endforeach; ?>
				</select>
			</label>
		</p>
		<form method="post" enctype="multipart/form-data">
			Dateien hinzufügen:
			<input type="file" name="uploadedFile">
			<input type="submit" value="Datei hochladen" name="filesubmitbtn">
		</form>
		<input type="submit">
	</form>
</div>