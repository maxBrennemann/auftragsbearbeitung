<dl class="tagList">
    <?php foreach ($tags as $tag):?>
        <dt><?=$tag["content"]?><span class="remove" data-tag="<?=$tag["id"]?>">x</span></dt>
    <?php endforeach; ?>
    <?php if (count($suggestionTags) == 0): ?>
        <p>Keine Tagvorschläge gefunden</p>
    <?php else: ?>
        <?php foreach ($suggestionTags as $tag): ?>
            <dt class="suggestionTag"><?=$tag?><span class="remove">x</span></dt>
        <?php endforeach; ?>
    <?php endif; ?>
</dl>