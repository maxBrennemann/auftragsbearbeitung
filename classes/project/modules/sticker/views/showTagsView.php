<dl class="tagList">
    <?php foreach ($tags as $tag):?>
        <dt class="py-1 px-2"><?=$tag["content"]?><span class="remove" data-tag="<?=$tag["id"]?>">x</span></dt>
    <?php endforeach; ?>
    <?php if (count($suggestionTags) == 0): ?>
        <p>Keine Tagvorschläge gefunden</p>
    <?php else: ?>
        <?php foreach ($suggestionTags as $tag): ?>
            <dt class="suggestionTag rounded-lg px-2"><?=$tag?><span class="remove cursor-pointer w-4 inline-block p-2 pr-4 ml-1 -mr-2 h-full rounded-r-lg bg-pink-100">x</span></dt>
        <?php endforeach; ?>
    <?php endif; ?>
</dl>