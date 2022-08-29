<?php
require_once('classes/Link.php');
require_once('classes/project/Rechnung.php');

$rechnung =  Link::getPageLink("rechnung");
$home =  Link::getPageLink("");
$rechnungsnummer = 0;
$rechnungslink;
$rechnungsadressen;

/* Daten für die Rechnung, falls vorhanden werden sie vom Server geladen */
$rechnungsdatum = "0000-00-00";
$leistungsdatum = "0000-00-00";

$target = isset($_GET["target"]) ? $_GET["target"] : -1;
$id = isset($_GET["id"]) ? $_GET["id"] : -1;

function formatAddresses($addresses) {
	$data = array();
	foreach($addresses as $address) {
		if (checkEmpty($address))
			continue;
		$element = $address["strasse"] . " " . $address["hausnr"] . " , " . $address["plz"] . " " . $address["ort"];
		$data[$address["id"]] = $element;
	}
	return $data;
}

function checkEmpty($address) {
	if ($address["strasse"] == "" || $address["hausnr"] == "" || $address["ort"] == "" || $address["plz"] == 0)
		return true;
	return false;
}

if ($target != -1) {
	switch ($target) {
		case "view":
			$rechnungsnummer = $id;
			$order = DBAccess::selectQuery("SELECT Kundennummer FROM auftrag WHERE Rechnungsnummer = $id")[0]["Kundennummer"];
			$rechnungslink = WEB_URL . "/files/generated/invoice/" . $order . "_" . $id . ".pdf";
			break;
		case "create":
			$auftrag = new Auftrag($id);
			$rechnungsadressen = Address::loadAllAddresses($auftrag->getKundennummer());
			$rechnungsadressen = formatAddresses($rechnungsadressen);
			$_SESSION['currentInvoice_orderId'] = $id;
			$naechsteRechnungsnummer = Rechnung::getNextNumber();

			$query = "SELECT creation_date, performance_date FROM invoice WHERE order_id = $id";
			$data = DBAccess::selectQuery($query);
			if (!null == $data) {
				$data = $data[0];
				$rechnungsdatum = $data["creation_date"];
				$leistungsdatum = $data["performance_date"];
			}

			/*if (isset($_SESSION['tempInvoice'])) {
				$rechnung = unserialize($_SESSION['tempInvoice']);
				
			}*/
			break;
		default:
			break;
	}
}

$link = Link::getPageLink('pdf') . "?type=rechnung";
$rechnungsPDF = "<iframe src=\"" . $link . "\" id=\"showOffer\"></iframe>";

/**
 * TODO:
 * - Logo hinzufügen
 * - Rechnung teilen zwischen Kunden
 * - Rechnungsadresse auswählen
 * - Rechnungsdatum und Leistungsdatum festlegen
 */

if ($target == "create"): ?>
	<div class="defCont">
		<h3>Auftrag <span id="orderId"><?=$id?></span></h3>
		<p>Nächste Rechnungsnummer: <b><?=$naechsteRechnungsnummer?></b><br><i>Diese Nummer ist vorläufig reserviert und kann sich noch ändern.</i></p>
		<div class="innerDefCont">
			<form></form>
			<p>Adresse auswählen</p>
			<?php if ($rechnungsadressen == null || empty($rechnungsadressen)): ?>
				<i>Keine Rechnungsadressen vorhanden oder unvollständig. Bei Bedarf unter dem Kunden ergänzen.</i>
			<?php else: ?>
				<select id="addressId">
				<?php foreach ($rechnungsadressen as $i => $r): ?>
					<option value="<?=$i?>"><?=$r?></option>
				<?php endforeach; ?>
				</select>
			<?php endif; ?>

			<p>Rechnungsdatum festlegen</p>
			<input type="date" id="rechnungsdatum" value="<?=$rechnungsdatum?>">
			<p>Leistungsdatum festlegen</p>
			<input type="date" id="leistungsdatum" value="<?=$leistungsdatum?>">
		</div>
		<hr>
		<a href="<?=$home?>" style="display: none" id="goHome"></a>
		<h4>Vordefinierte Texte</h4>
		<p>Den Text zum (ab)wählen einmal anklicken. Die Rechnungsvorschau wird dann neu generiert.</p>
		<div class="standardtexte">
			<p>Zu den Bilddaten: Bei der Benutzung von Daten aus fremden Quellen richten sich die Nutzungsbedingungen über Verwendung und Weitergabe nach denen der jeweiligen Anbieter.</p>
			<p>Bitte beachten Sie, dass wir keine Haftung für eventuell entstehende Schäden übernehmen, die auf Witterungseinflüsse zurückzufüren sind (zerrisene Banner, herausgerissen Ösen o. Ä.). Sie als Kunde müssen entscheiden, wie die Banner konfektioniert werden sollen. Für die Art der Konfektionierung übernehmen wir keine Haftung. Wir übernehmen außerdem keine Haftung für unfachgerechte Montage der Banner.</p>
			<p>Pflegehinweise beachten: Keine Bleichmittel und Weichspüler verwenden. Nicht in den Trockner geben. Links gewendet waschen. Nicht über den Transfer bügeln. Nicht chemisch reinigen.</p>
			<p>Wir weisen darauf hin, dass Logos eventuell Bildrechte anderer berühren und wir hierfür keine Haftung übernehmen. Der Kunde garantiert uns Straffreiheit gegenüber einer eventuell geschädigten Partei im Fall einer Verletzung des Rechts des geistigen Eigentums und/ oder des Bildrechts und/ oder den durch eine solche Verletzung verursachten Schadens. Für einen eventuellen Fall solch einer Verletzung willigt der Kunde ein, uns in Höhe aller entstandenen Kosten (inkl. Anwaltkosten) zu entschädigen.</p>
			<span><input id="newText" class="visibility"><button onclick="addText()" id="addToTexts">+</button></span>
		</div>
		<hr>
		<?php if ($auftrag != null && $auftrag->getAuftragspostenData() != null): ?>
		<button onclick="generatePDF();">Rechnung abschließen</button>
		<?php else: ?>
		<button disabled>Rechnung abschließen</button>
		<?php endif; ?>
		<button action="action" onclick="window.history.go(-1); return false; "type="submit">Abbrechen</button>
	</div>
	<br>
	<span>
		<?=$rechnungsPDF?>
	</span>
<?php elseif ($target == "view"): ?>
	<div>Rechnung <span id="rechnungsnummer"><?=$rechnungsnummer;?></span></div>
	<iframe src="<?=$rechnungslink?>"></iframe>
<?php else: ?>
	<p>Es ist ein unerwarteter Fehler aufgetreten.</p>
	<button action="action" onclick="window.history.go(-1); return false; "type="submit">Zurück</button>
<?php endif; ?>