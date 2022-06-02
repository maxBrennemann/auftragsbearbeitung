<?php
	require_once('classes/Link.php');
	require_once('classes/project/FormGenerator.php');
	require_once('classes/project/Rechnung.php');
	
	$rechnung =  Link::getPageLink("rechnung");
	$home =  Link::getPageLink("");
	$rechnungsnummer = 0;
	$rechnungslink;
	
	$target = isset($_GET["target"]) ? $_GET["target"] : -1;
	$id = isset($_GET["id"]) ? $_GET["id"] : -1;

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
				$_SESSION['currentInvoice_orderId'] = $id;
				$naechsteRechnungsnummer = Rechnung::getNextNumber();

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
		<p>Nächste Rechnungsnummer: <?=$naechsteRechnungsnummer?></p>
		<div>
			<p>Adresse auswählen</p>
			<select id="rechnungsnummer"></select>
			<p>Rechnungsdatum festlegen</p>
			<input type="date" id="rechnungsdatum" value="">
			<p>Leistungsdatum festlegen</p>
			<input type="date" id="leistungsdatum" value="">
		</div>
		<hr>
		<a href="<?=$home?>" style="display: none" id="goHome"></a>
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