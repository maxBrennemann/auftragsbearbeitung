<?php

use Src\Classes\Link;
use Src\Classes\Project\Angebot;
use Src\Classes\Project\Icon;

$openOffers = Angebot::getOpenOffers();

?>
<section class="defCont" id="newOffer">
	<div class="section-head">
		<h2 class="section-title">Neues Angebot</h2>
		<a class="link-primary text-sm" href="<?= Link::getPageLink("neuer-kunde") ?>">Neuen Kunden anlegen</a>
	</div>
	<div class="flex flex-wrap items-end gap-3">
		<label class="field">
			<span class="field-label">Kundennummer</span>
			<input type="number" id="kdnr" class="autosubmit input-primary" min="1" autofocus data-btnid="1">
		</label>
		<button data-binding="true" data-fun="newOffer" id="autosubmit_1" class="btn-primary"><?= Icon::get("file-plus") ?>Angebot anlegen</button>
	</div>
</section>

<section class="defCont" id="listOpenOffers">
	<div class="section-head">
		<h2 class="section-title">Offene Angebote</h2>
		<span class="section-meta"><?= count($openOffers) == 1 ? "1 Angebot" : count($openOffers) . " Angebote" ?></span>
	</div>
	<?php if (empty($openOffers)): ?>
		<p class="text-sm text-text-muted">Es gibt keine offenen Angebote.</p>
	<?php else: ?>
		<div class="overflow-x-auto">
			<table class="w-full">
				<thead>
					<tr>
						<th>Nr.</th>
						<th>Datum</th>
						<th>Kunde</th>
						<th>Name</th>
					</tr>
				</thead>
				<tbody id="angebotList">
					<?php foreach ($openOffers as $offer): ?>
						<tr class="cursor-pointer" data-binding="true" data-fun="loadOffer" data-id="<?= $offer['id'] ?>" title="Angebot öffnen">
							<td class="font-semibold text-ink"><?= $offer['id'] ?></td>
							<td><?= $offer['creation_date'] ?></td>
							<td><?= $offer['customer_id'] ?></td>
							<td><?= $offer['name'] ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
</section>

<div id="insTemp"></div>
