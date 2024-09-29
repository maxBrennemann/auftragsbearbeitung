<?php

use Classes\Project\Icon;

?>

<span class="mx-2 px-2 border-none bg-slate-300 rounded-lg flex" data-type="<?=$type?>" data-text="<?=$text?>">
    <button class="border-none m-0 p-0" title="Textvorschlag erstellen" data-binding="true" data-fun="textGeneration">
        <?=Icon::get("iconGenerate", 15, 15, ["block"])?>
    </button>

    <button class="m-0 mx-1 p-0 border-none cursor-pointer" data-binding="true" data-fun="iterateText" data-direction="back">⬅</button>
    
    <span class="bg-slate-50 px-1 chatCount"><?=$gpt->getChatCount($type, $text)?></span>

    <button class="m-0 mx-1 p-0 border-none" data-binding="true" data-fun="iterateText" data-direction="next">⮕</button>

    <button class="m-0 p-0 border-none" data-binding="true" data-fun="showTextSettings">
        <?=Icon::get("iconSettings", 15, 15, ["block"])?>
    </button>
</span>