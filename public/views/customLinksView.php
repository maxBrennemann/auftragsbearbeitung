<?php

use Src\Classes\Project\Icon;

?>

<ul class="grid grid-cols-2 sm:grid-cols-3 gap-1.5 auto-rows-min">
    <?php foreach ($customLinks as $link): ?>
        <li class="px-3 py-5 rounded-lg bg-gray-100 hover:underline hover:bg-gray-200">
            <?php if ($link["input"]): ?>
                <input id="<?= $link["input"]["id"] ?>" type="<?= $link["input"]["type"] ?>" class="w-32 rounded-md p-1">
            <?php endif; ?>
            <a href="<?= $link["url"] ?>" class="inline-flex items-center gap-x-1">
                <?php if ($link["icon"]): ?>
                    <?= Icon::getDefault($link["icon"]) ?>
                <?php endif; ?>
                <span class="<?= ($link["icon"] != false || $link["input"]) ? "ml-1" : "" ?>"><?= $link["name"] ?></span>
            </a>
        </li>
    <?php endforeach; ?>
</ul>