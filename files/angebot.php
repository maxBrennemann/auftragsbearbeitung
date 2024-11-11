<?php

use Classes\Link;
use Classes\Project\Offer;

$openOffers = Offer::getOpenOffers();

?>
<div class="defCont">
	<a href="<?=Link::getPageLink("angebot")?>?open" class="link-primary">Offene Angebote durchsehen</a>
	<p>Kundennummer:
		<input type="number" id="kdnr" class="autosubmit input-primary" autofocus data-btnid="1">
		<button id="autosubmit_1" onclick="neuesAngebot()" class="btn-primary">Bestätigen</button>
		<br>Oder <a class="link-primary" href="<?=Link::getPageLink("neuer-kunde")?>">hier</a> einen neuen Kunden anlegen.
	</p>
</div>
<div id="insTemp"></div>