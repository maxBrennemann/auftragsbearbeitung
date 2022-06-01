<?php
	require_once('classes/Link.php');
	require_once('classes/project/FormGenerator.php');
	require_once('classes/project/Rechnung.php');
	
	$rechnung =  Link::getPageLink("rechnung");
	$rechnungsnummer = 0;
	
	$target = isset($_GET["target"]) ? $_GET["target"] : -1;
	$id = isset($_GET["id"]) ? $_GET["id"] : -1;

	if ($target != -1) {
		switch ($target) {
			case "view":
				$rechnungsnummer = $id;
				$rechnung = new Rechnung($rechnungsnummer);
				break;
			case "create":
				$auftrag = new Auftrag($id);
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

if ($target == "create"): ?>
	<div class="defCont">
		<h3>Auftrag <span id="orderId"><?=$id?></span></h3>
		<p>Nächste Rechnungsnummer: <?=$naechsteRechnungsnummer?></p>
		<div class="standardtexte">
			<p>Zu den Bilddaten: Bei der Benutzung von Daten aus fremden Quellen richten sich die Nutzungsbedingungen über Verwendung und Weitergabe nach denen der jeweiligen Anbieter.</p>
			<p>Bitte beachten Sie, dass wir keine Haftung für eventuell entstehende Schäden übernehmen, die auf Witterungseinflüsse zurückzufüren sind (zerrisene Banner, herausgerissen Ösen o. Ä.). Sie als Kunde müssen entscheiden, wie die Banner konfektioniert werden sollen. Für die Art der Konfektionierung übernehmen wir keine Haftung. Wir übernehmen außerdem keine Haftung für unfachgerechte Montage der Banner.</p>
			<p>Pflegehinweise beachten: Keine Bleichmittel und Weichspüler verwenden. Nicht in den Trockner geben. Links gewendet waschen. Nicht über den Transfer bügeln. Nicht chemisch reinigen.</p>
			<p>Wir weisen darauf hin, dass Logos eventuell Bildrechte anderer berühren und wir hierfür keine Haftung übernehmen. Der Kunde garantiert uns Straffreiheit gegenüber einer eventuell geschädigten Partei im Fall einer Verletzung des Rechts des geistigen Eigentums und/ oder des Bildrechts und/ oder den durch eine solche Verletzung verursachten Schadens. Für einen eventuellen Fall solch einer Verletzung willigt der Kunde ein, uns in Höhe aller entstandenen Kosten (inkl. Anwaltkosten) zu entschädigen.</p>
			<span><input id="newText" class="visibility"><button onclick="addText()" id="addToTexts">+</button></span>
		</div>
		<?php if ($auftrag != null || $auftrag->getAuftragspostenData() != 0): ?>
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
	<div>Rechnung:</div>
	<span id="rechnungsnummer"><?=$rechnungsnummer;?></span>
	<button onclick="print('rechnungsnummer', 'Rechnung');">Rechnungsblatt generieren</button>
<?php else: ?>
	<p>Es ist ein unerwarteter Fehler aufgetreten.</p>
	<button action="action" onclick="window.history.go(-1); return false; "type="submit">Zurück</button>
<?php endif; ?>
<style>
	.standardtexte {
		display: flex;
		flex-wrap: wrap;
		flex-direction: row;
		box-sizing: border-box;
	}

	.standardtexte * {
		flex: 1 1 auto;
		width: 30%;
		padding: 7px;
		margin: 5px;
		background: white;
		border-radius: 6px;
		box-sizing: border-box;
	}

	.highlightBlue {
		border: 2px solid blue;
	}

	.visibility {
		display: none !important;
	}

	#addToTexts {
		width: 40px;
		height: 40px;
		border-radius: 50%;
		line-height: 20px;
		text-align: center;
		font-size: 20px;
		border: none;
		box-shadow: 1px 0px 5px 0px grey;
		font-weight: bold;
		color: grey;
	}

	#newText {
		max-width: 100%;
		border: 1px solid grey;
		-webkit-box-shadow: 0 1px 2px 0 rgb(0 0 0 / 10%);
		box-shadow: 0 1px 2px 0 rgb(0 0 0 / 10%);
		border-radius: 4px;
		height: 30px;
		background: #fff;
		display: block;
		width: 100%;
		box-sizing: border-box;
		padding: 0.375rem 0.75rem;
		outline: none;
		color: #1a1a1a;
	}
</style>