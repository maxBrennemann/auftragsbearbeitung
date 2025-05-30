<?php foreach ($colors as $color): ?>
    <div class="singleColorContainer flex items-center bg-gray-100 my-1.5 mr-2 p-1 px-2 rounded" data-color-id=<?= $color['id'] ?>>
        <p class="singleColorName flex-1"><?= $color['color_name'] ?> <?= $color['short_name'] ?> <?= $color['producer'] ?></p>
        <div class="farbe" style="background-color: #<?= $color['hex_value'] ?>"></div>
        <div class="">
            <button class="btn-cancel" data-color-id=<?= $color['id'] ?> data-fun="removeColor" data-binding="true" title="Farbe entfernen"><?= \Classes\Project\Icon::getDefault("iconRemoveConnection") ?></button>
        </div>
    </div>
<?php endforeach; ?>