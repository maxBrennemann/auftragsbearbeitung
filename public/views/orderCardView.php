<div class="grid grid-cols-3 2xl:grid-cols-4 gap-5 mt-3 orderCard">
	<?php foreach ($orders as $order): ?>
		<div class="bg-white p-3 rounded-md">
			<div class="flex items-center justify-between">
				<h3 class="font-bold"><?= $order["orderTitle"] ?></h3>
				<?php if ($order["status"] === \Src\Classes\Project\OrderState::Archived): ?>
					<button onclick="((e) => {e.target.parentNode.nextElementSibling.classList.toggle('hidden')})(event)" class="btn-options float-right orderOptions" title="Mehr Optionen">⋮</button>
					<div class="hidden absolute right-0 top-0 bg-white rounded-lg drop-shadow-lg p-3 mt-5 orderOptions">
						<button class="btn-primary mt-5" data-fun="rearchive" data-binding="true" data-order-id="<?= $order['id'] ?>">Auftrag aus dem Archiv holen</button>
					</div>
				<?php endif; ?>
			</div>
			<a class="text-blue-500 font-semibold ml-0.5" href="<?= \Src\Classes\Link::getPageLink("auftrag") ?>?id=<?= $order["id"] ?>">Zum Auftrag <?= $order["id"] ?></a>
			<p class="m-1 p-1 rounded-lg w-full block text-ellipsis overflow-hidden whitespace-nowrap" title="<?= $order["orderDescription"] ?>"><?= $order["orderDescription"] ?></p>
			<table class="rounded-md m-1 mt-2 w-full">
				<tr class="border-b border-gray-800">
					<th class="bg-gray-200 text-gray-800">Datum</th>
					<td class="bg-gray-100 text-gray-800 rounded-tr-lg"><?= $order["date"] ?></td>
				</tr>
				<tr class="border-b border-gray-800">
					<th class="bg-gray-200 text-gray-800">Termin</th>
					<td class="bg-gray-100 text-gray-800"><?= $order["deadline"] ?></td>
				</tr>
				<tr>
					<th class="bg-gray-200 text-gray-800 rounded-bl-lg">Fertigstellung</th>
					<td class="bg-gray-100 text-gray-800"><?= $order["finished"] ?></td>
				</tr>
			</table>
			<?php if ($order["status"] === \Src\Classes\Project\OrderState::Archived): ?>
				<button class="btn-primary orderDisabled mt-1.5" disabled>archiviert</button>
			<?php endif; ?>
			<?php if ($order["invoice"] != 0): ?>
				<p class="font-semibold">Rechnung <?= $order["invoice"] ?></p>
			<?php endif; ?>
			<?php if ($order["summe"] != 0): ?>
				<p class="font-semibold">Auftragssumme: <?= $order["summe"] ?> €</p>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
</div>