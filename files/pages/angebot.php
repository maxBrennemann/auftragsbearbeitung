<?php

use Src\Classes\Link;
use Src\Classes\Project\Offer;

$openOffers = Offer::getOpenOffers();

?>
<div class="defCont" id="newOffer">
	<p>Kundennummer:
		<input type="number" id="kdnr" class="autosubmit input-primary" autofocus data-btnid="1">
		<button data-binding="true" data-fun="newOffer" id="autosubmit_1" class="btn-primary">Bestätigen</button>
		<br>Oder <a class="link-primary" href="<?=Link::getPageLink("neuer-kunde")?>">hier</a> einen neuen Kunden anlegen.
	</p>
</div>
<div class="defCont" id="listOpenOffers">
	<p>Offene Angebote:</p>
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th>Nr.</th>
				<th>Datum</th>
				<th>Kundennummer</th>
				<th>Name</th>
			</tr>
		</thead>
		<tbody id="angebotList">
			<?php foreach ($openOffers as $offer): ?>
				<tr data-binding="true" data-fun="loadOffer" data-id="<?=$offer['id']?>">
					<td><?=$offer['id']?></td>
					<td><?=$offer['creation_date']?></td>
					<td><?=$offer['customer_id']?></td>
					<td><?=$offer['name']?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
<div id="insTemp"></div>