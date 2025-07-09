<?php

use Classes\Project\Icon;

?>

<span class="ml-3 px-2 border-none bg-gray-300 rounded-lg flex items-center" data-type="<?= $type ?>" data-text="<?= $text ?>">
    <button class="border-none m-0 p-0" title="Textvorschlag erstellen" data-binding="true" data-fun="textGeneration">
        <?= Icon::get("iconGenerate", 10, 10, ["block"]) ?>
    </button>

    <button class="m-0 mx-1 p-0 border-none cursor-pointer text-sm" data-binding="true" data-fun="iterateText" data-direction="back" title="Vorheriger Text">⬅</button>

    <span class="bg-slate-50 px-1 chatCount text-sm" title="Aktueller Textindex"><?= $textModification->getChatCount($type, $text) ?></span>

    <button class="m-0 mx-1 p-0 border-none text-sm" data-binding="true" data-fun="iterateText" data-direction="next" title="Nächster Text">⮕</button>

    <button class="m-0 p-0 border-none" data-binding="true" data-fun="showTextSettings" title="Texteinstellungen">
        <?= Icon::get("iconSettings", 10, 10, ["block"]) ?>
    </button>
</span>