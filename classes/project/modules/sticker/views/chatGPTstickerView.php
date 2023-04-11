<span>
    <button class="iconGenerate m-0 p-0" title="Textvorschlag erstellen" data-binding="true" data-fun="textGeneration" data-type="<?=$type?>" data-text="<?=$text?>">
        <?=Icon::$iconGenerate?>
    </button>
    <button class="m-0 p-0 border-none cursor-pointer"><</button>
    <?=$gpt->getChatCount($type, $text)?>
    <button class="m-0 p-0 border-none">></button>
    <button class="m-0 p-0 border-none align-middle"><?=Icon::$iconSettings?></button>
</span>