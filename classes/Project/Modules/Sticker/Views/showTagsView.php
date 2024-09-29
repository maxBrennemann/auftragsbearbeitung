<dl class="tagList inline">
    <?php foreach ($tags as $tag):?>
        <dt class="suggestionTag rounded-lg px-2 inline-block m-1 bg-blue-500 cursor-default"><?=$tag["content"]?><span class="remove cursor-pointer w-4 inline-block p-2 pr-4 ml-1 -mr-2 h-full rounded-r-lg bg-pink-100 z-10" data-tag="<?=$tag["id"]?>">x</span></dt>
    <?php endforeach; ?>
    <?php if (count($suggestionTags) == 0): ?>
        <p>Keine Tagvorschläge gefunden</p>
    <?php else: ?>
        <?php foreach ($suggestionTags as $tag): ?>
            <dt class="suggestionTag rounded-lg px-2 inline-block m-1 bg-blue-200 cursor-pointer"><?=$tag?><span class="remove cursor-pointer w-4 inline-block p-2 pr-4 ml-1 -mr-2 h-full rounded-r-lg bg-pink-100">x</span></dt>
        <?php endforeach; ?>
    <?php endif; ?>
</dl>