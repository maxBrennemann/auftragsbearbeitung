<span class="mx-2 px-2 border-none bg-slate-300 rounded-lg">
    <button class="border-none m-0 p-0" title="Textvorschlag erstellen" data-binding="true" data-fun="textGeneration" data-type="<?=$type?>" data-text="<?=$text?>">
        <?=Icon::$iconGenerate?>
    </button>
    <button class="m-0 p-0 border-none cursor-pointer">⬅</button>
    <span class="bg-slate-50 px-1 rounded-md"><?=$gpt->getChatCount($type, $text)?></span>
    <button class="m-0 p-0 border-none">⮕</button>
    <button class="m-0 p-0 border-none" data-binding="true" data-fun="showTextSettings"><?=Icon::$iconSettings?></button>
</span>