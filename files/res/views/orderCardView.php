<div class="grid grid-cols-3 2xl:grid-cols-4 gap-3 mt-3">
    <?php foreach ($orders as $order): ?>
        <div class="bg-white p-3 rounded-md">
			<?php if ($order["archived"]): ?>
			    <div class="relative">
					<h3 class="font-bold"><?=$order["orderTitle"]?> <button onclick="((e) => {e.target.parentNode.nextElementSibling.classList.toggle('hidden')})(event)" class="float-right border-none" id="extraOptions">⋮</button></h3>
					<div class="hidden absolute right-0 top-0 bg-white rounded-lg drop-shadow-lg p-3 mt-5" id="showExtraOptions">
						<button class="btn-attention mt-5" onclick="rearchive(<?=$order['id']?>)">Auftrag aus dem Archiv holen</button>
					</div>
				</div>
            <?php else: ?>
				<h3 class="font-bold"><?=$order["orderTitle"]?></h3>
			<?php endif; ?>
			<a class="text-blue-500 font-semibold" href="<?=Classes\Link::getPageLink("auftrag")?>?id=<?=$order["id"]?>">Zum Auftrag <?=$order["id"]?></a>
			<textarea class="m-1 p-1 rounded-lg w-full block" disabled><?=$order["orderDescription"]?></textarea>
			<table class="rounded-md m-1 mt-2 w-full">
				<tr class="border-b-2 border-black">
					<th class="bg-gray-200 text-gray-800">Datum</th>
					<td class="bg-gray-100 text-gray-800 rounded-tr-lg"><?=$order["date"]?></td>
				</tr>
				<tr class="border-b-2 border-black">
					<th class="bg-gray-200 text-gray-800">Termin</th>
					<td class="bg-gray-100 text-gray-800"><?=$order["deadline"]?></td>
				</tr>
				<tr>
					<th class="bg-gray-200 text-gray-800 rounded-bl-lg">Fertigstellung</th>
					<td class="bg-gray-100 text-gray-800"><?=$order["finished"]?></td>
				</tr>
			</table>
            <?php if ($order["archived"]): ?>
			    <button class="btn-primary" disabled>archiviert</button>
            <?php endif; ?>
			<?php if ($order["invoice"] != 0): ?>
			    <p>Rechnung <?=$order["invoice"]?></p>
            <?php endif; ?>
            <?php if ($order["summe"] != 0): ?>
			    <p>Auftragssumme: <?=$order["summe"]?> €</p>
            <?php endif; ?>
		</div>
    <?php endforeach; ?>
</div>