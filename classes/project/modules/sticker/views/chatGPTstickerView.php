<button class="iconGenerate" title="Textvorschlag erstellen" data-binding="true" data-fun="textGeneration" data-type="<?=$type?>" data-text="<?=$text?>">
    <?=Icon::$iconGenerate?>
</button>
<span>
    <button><</button>
    <?=$gpt->getChatCount($type, $text)?>
    <button>></button>
    <button><?=Icon::$iconSettings?></button>
</span>