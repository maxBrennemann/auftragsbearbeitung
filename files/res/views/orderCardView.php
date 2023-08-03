<div class="grid grid-cols-3 2xl:grid-cols-4 gap-3 mt-3">
    <?php foreach ($orders as $order): ?>
        <div class="bg-white p-3 rounded-md">
			<h3 class="font-bold"><?=$order["orderTitle"]?></h3>
			<a class="text-blue-500 font-semibold" href="<?=Link::getPageLink("auftrag")?>?id=<?=$order["id"]?>">Zum Auftrag <?=$order["id"]?></a>
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