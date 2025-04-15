<?php

use Classes\Link;
use MaxBrennemann\PhpUtilities\DBAccess;

use Classes\Project\Auftrag;
use Classes\Project\Rechnung;
use Classes\Project\Address;
use Classes\Project\Icon;

$rechnung = Link::getPageLink("rechnung");
$rechnungsnummer = 0;
$rechnungslink;
$rechnungsadressen;

/* Daten für die Rechnung, falls vorhanden werden sie vom Server geladen */
$rechnungsdatum = "";
$leistungsdatum = "";

$target = isset($_GET["target"]) ? $_GET["target"] : -1;
$id = isset($_GET["id"]) ? $_GET["id"] : -1;

function formatAddresses($addresses)
{
	$data = array();
	foreach ($addresses as $address) {
		if (checkEmpty($address))
			continue;
		$element = $address["strasse"] . " " . $address["hausnr"] . " , " . $address["plz"] . " " . $address["ort"];
		$data[$address["id"]] = $element;
	}
	return $data;
}

function checkEmpty($address)
{
	if ($address["strasse"] == "" || $address["hausnr"] == "" || $address["ort"] == "" || $address["plz"] == 0)
		return true;
	return false;
}

if ($target != -1) {
	switch ($target) {
		case "view":
			$rechnungsnummer = $id;
			$order = DBAccess::selectQuery("SELECT Kundennummer FROM auftrag WHERE Rechnungsnummer = $id")[0]["Kundennummer"];
			$rechnungslink = $_ENV["WEB_URL"] . "/files/generated/invoice/" . $order . "_" . $id . ".pdf";
			break;
		case "create":
			$auftrag = new Auftrag($id);
			$rechnungsadressen = Address::loadAllAddresses($auftrag->getKundennummer());
			$rechnungsadressen = formatAddresses($rechnungsadressen);
			$_SESSION['currentInvoice_orderId'] = $id;
			$nextInvoiceId = Rechnung::getNextNumber();

			$query = "SELECT creation_date, performance_date FROM invoice WHERE order_id = $id";
			$data = DBAccess::selectQuery($query);
			if (!null == $data) {
				$data = $data[0];
				$rechnungsdatum = $data["creation_date"];
				$leistungsdatum = $data["performance_date"];
			}
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
		<div>
			<h3 class="font-bold">Auftrag <span id="orderId"><?= $id ?></span></h3>
			<p title="Diese Nummer ist vorläufig reserviert und kann sich noch ändern.">Nächste Rechnungsnummer: <b><?= $nextInvoiceId ?></b></p>
		</div>

		<div class="innerDefCont">
			<p>Adresse auswählen</p>
			<?php if ($rechnungsadressen == null || empty($rechnungsadressen)): ?>
				<i>Keine Rechnungsadressen vorhanden oder unvollständig. Bei Bedarf unter dem Kunden ergänzen.</i>
			<?php else: ?>
				<select id="addressId" class="input-primary-new">
					<?php foreach ($rechnungsadressen as $i => $r): ?>
						<option value="<?= $i ?>"><?= $r ?></option>
					<?php endforeach; ?>
				</select>
			<?php endif; ?>

			<p class="mt-2">Rechnungsdatum festlegen</p>
			<input type="date" data-write="true" data-fun="invoiceDate" class="input-primary-new" value="<?= $rechnungsdatum ?>">
			<p class="mt-2">Leistungsdatum festlegen</p>
			<input type="date" data-write="true" data-fun="serviceDate" class="input-primary-new" value="<?= $leistungsdatum ?>">
		</div>

		<hr>

		<div class="mt-3">
			<h4 class="font-semibold inline-flex items-center" data-fun="togglePredefinedTexts" data-binding="true">
				<span>Vordefinierte Texte</span>
				<span class="cursor-pointer">
					<span class="toggle-up hidden"><?= Icon::getDefault("iconChevronUp") ?></span>
					<span class="toggle-down"><?= Icon::getDefault("iconChevronDown") ?></span>
				</span>
			</h4>
			<div class="predefinedTexts hidden">
				<p>Den Text zum (ab)wählen einmal anklicken. Die Rechnungsvorschau wird dann neu generiert.</p>
				<div class="standardtexte grid grid-cols-3 gap-4 mt-2">
					<p class="bg-white rounded-xl cursor-pointer p-3 select-none" title="Übernehmen" data-binding="true" data-fun="toggleText">Zu den Bilddaten: Bei der Benutzung von Daten aus fremden Quellen richten sich die Nutzungsbedingungen über Verwendung und Weitergabe nach denen der jeweiligen Anbieter.</p>
					<p class="bg-white rounded-xl cursor-pointer p-3 select-none" title="Übernehmen" data-binding="true" data-fun="toggleText">Bitte beachten Sie, dass wir keine Haftung für eventuell entstehende Schäden übernehmen, die auf Witterungseinflüsse zurückzufüren sind (zerrisene Banner, herausgerissen Ösen o. Ä.). Sie als Kunde müssen entscheiden, wie die Banner konfektioniert werden sollen. Für die Art der Konfektionierung übernehmen wir keine Haftung. Wir übernehmen außerdem keine Haftung für unfachgerechte Montage der Banner.</p>
					<p class="bg-white rounded-xl cursor-pointer p-3 select-none" title="Übernehmen" data-binding="true" data-fun="toggleText">Pflegehinweise beachten: Keine Bleichmittel und Weichspüler verwenden. Nicht in den Trockner geben. Links gewendet waschen. Nicht über den Transfer bügeln. Nicht chemisch reinigen.</p>
					<p class="bg-white rounded-xl cursor-pointer p-3 select-none" title="Übernehmen" data-binding="true" data-fun="toggleText">Wir weisen darauf hin, dass Logos eventuell Bildrechte anderer berühren und wir hierfür keine Haftung übernehmen. Der Kunde garantiert uns Straffreiheit gegenüber einer eventuell geschädigten Partei im Fall einer Verletzung des Rechts des geistigen Eigentums und/ oder des Bildrechts und/ oder den durch eine solche Verletzung verursachten Schadens. Für einen eventuellen Fall solch einer Verletzung willigt der Kunde ein, uns in Höhe aller entstandenen Kosten (inkl. Anwaltkosten) zu entschädigen.</p>
				</div>
				<div class="my-2">
					<input id="newText" class="input-primary-new">
					<button onclick="addText()" id="addToTexts" class="btn-primary-new">Hinzufügen</button>
				</div>
			</div>
			<div class="predefinedTexts"></div>
		</div>

		<hr>

		<div class="mt-3">
			<?php if ($auftrag != null && $auftrag->getAuftragspostenData() != null): ?>
				<button onclick="generatePDF();" class="btn-primary-new">Rechnung abschließen</button>
			<?php else: ?>
				<button disabled class="btn-primary-new">Rechnung abschließen</button>
			<?php endif; ?>
			<button action="action" onclick="window.history.go(-1); return false; " type="submit" class="btn-primary-new">Abbrechen</button>
		</div>
	</div>
	<div class="mt-3">
		<?= $rechnungsPDF ?>
	</div>
<?php elseif ($target == "view"): ?>
	<div>Rechnung <span id="rechnungsnummer"><?= $rechnungsnummer; ?></span></div>
	<iframe src="<?= $rechnungslink ?>"></iframe>
<?php else: ?>
	<p>Es ist ein unerwarteter Fehler aufgetreten.</p>
	<button action="action" class="btn-primary-new" onclick="window.history.go(-1); return false; " type="submit">Zurück</button>
<?php endif; ?>