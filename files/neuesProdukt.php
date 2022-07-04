<?php 
require_once('classes/project/FormGenerator.php');

$productId = -1;

if (isset($_GET['id'])) {
	$productId = $_GET['id'];
}

/*if (isset($_POST['filesubmitbtn'])) {
	$upload = new Upload();
	$upload->uploadFilesProduct($productId);
}*/

$quelle = DBAccess::selectQuery("SELECT name, id FROM einkauf");
$attributeGroups = DBAccess::selectQuery("SELECT * FROM attribute_group");

echo "<a href=\"" . Link::getPageLink("attributes") . "\">Zu den Produktattributetn</a><br>";
echo "<a href=\"" . Link::getPageLink("produkt") . "\">Zu den Produkten</a>";
?>
<div class="defCont">
	<p>
		<span>Marke<br>
			<input class="dataInput" type="text" name="marke" required>
		</span>
	</p>
	<p>
		<span>Quelle<br>
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
		<span>Verkaufspreis Netto<br>
			<input class="dataInput" type="text" name="vk_netto" required>
		</span>
	</p>
	<p>
		<span>Einkaufspreis Netto<br>
			<input class="dataInput" type="text" name="ek_netto" required>
		</span>
	</p>
	<p>
		<span>Kurzbezeichnung / Titel<br>
			<input class="dataInput" type="text" name="short_description" max="64" required>
		</span>
	</p>
	<p>
		<span>Beschreibung<br>
			<textarea class="dataInput" type="text" name="description"></textarea>
		</span>
	</p>
	<!--<p>
		<span>Attribute hinzufügen<br>
			<button onclick="getHTMLForAttributes();">Hinzufügen</button>
		</span>
	</p>-->
	<span id="addAttributeTable"></span>
	<button onclick="saveProduct()">Produkt speichern</button>
</div>