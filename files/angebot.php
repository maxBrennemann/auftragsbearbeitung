<?php

use Classes\Link;
use MaxBrennemann\PhpUtilities\DBAccess;

$showAngebot = 0;
$showOffer = false;

if (isset($_GET['id'])) {
	$id = (int) $_GET['id'];
	if ($id <= 0) {
		echo "Angebotsnummer existiert nicht.";
	} else {
		$showAngebot = $id;
	}
}

if (isset($_SESSION["offer_id"])) {
	$showOffer = true;
}

if (isset($_GET['open'])) :
	$offeneAngebote = DBAccess::selectQuery("SELECT angebot.id, IF(kunde.Firmenname = '', CONCAT(kunde.Vorname, ' ', kunde.Nachname), kunde.Firmenname) as Name FROM angebot, kunde WHERE status = 0 AND angebot.kdnr = kunde.Kundennummer");
?>
<h2>Offene Angebote</h2>
<div class="defCont">
	<?php foreach ($offeneAngebote as $angebot): ?>
		<span><a href="<?=Link::getPageLink("angebot")?>?id=<?=$angebot['id']?>">Angebot <?=$angebot['id']?></a> für Kunde  <?=$angebot['Name']?></span><br>
	<?php endforeach; ?>
</div>
<!-- offeneAngeboteEnde -->
<?php elseif ($showAngebot != 0): ?>

<!-- showAngebotEnde -->
<?php else : ?>
<div class="defCont">
	<a href="<?=Link::getPageLink("angebot")?>?open" class="link-primary">Offene Angebote durchsehen</a>
	<p>Kundennummer:
		<input type="number" id="kdnr" class="autosubmit input-primary" autofocus data-btnid="1">
		<button id="autosubmit_1" onclick="neuesAngebot()" class="btn-primary">Bestätigen</button>
		<br>Oder <a class="link-primary" href="<?=Link::getPageLink("neuer-kunde")?>">hier</a> einen neuen Kunden anlegen.
	</p>
</div>
<div id="insTemp"></div>
<?php endif; ?>
<style>

	.address {
		padding-right: 15px;
	}

	.fleft {
		float: left;
	}

	.fright {
		float: right;
	}

	.inlineC {
		display: inline-block;
	}

</style>