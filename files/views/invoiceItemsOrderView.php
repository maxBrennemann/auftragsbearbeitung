<div>
    <p class="font-semibold">Rechnungsposten Reihenfolge</p>
    <p class="text-xs">Um die Reihenfolge festzulegen, einzelne Posten einfach verschieben.</p>

    <div class="invoiceItemsGroup">
        <?php
        $count = 1;
        $max = count($items);
        foreach ($items as $item) : ?>
            <div data-id="<?= $item["id"] ?>" data-type="<?= $item["type"] ?>" class="flex my-2 px-2 bg-slate-50 rounded cursor-move">
                <div><input type="number" value="<?= $count++ ?>" class="input-primary w-16" min="1" max="<?= $max ?>" disabled></div>
                <div class="flex-1 ml-2 flex items-center">
                    <p><?= $item["content"] ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>