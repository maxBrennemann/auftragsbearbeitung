<span class="mx-2 px-2 border-none bg-slate-300 rounded-lg" data-type="<?=$type?>" data-text="<?=$text?>">
    <button class="border-none m-0 p-0" title="Textvorschlag erstellen" data-binding="true" data-fun="textGeneration">
        <?=Icon::getDefault("iconGenerate")?>
    </button>
    <button class="m-0 p-0 border-none cursor-pointer" data-binding="true" data-fun="iterateText" data-direction="back">⬅</button>
    <span class="bg-slate-50 px-1 chatCount"><?=$gpt->getChatCount($type, $text)?></span>
    <button class="m-0 p-0 border-none" data-binding="true" data-fun="iterateText" data-direction="next">⮕</button>
    <button class="m-0 p-0 border-none" data-binding="true" data-fun="showTextSettings"><?=Icon::getDefault("iconSettings")?></button>
</span>