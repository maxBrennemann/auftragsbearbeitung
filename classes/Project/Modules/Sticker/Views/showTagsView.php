<dl class="tagList block bg-gray-50 rounded-lg p-2" id="tagList">
    <?php foreach ($tags as $tag):?>
        <dt class="cursor-default inline-flex rounded-lg font-semibold overflow-hidden">
            <span class="px-2 py-1 bg-blue-100"><?=$tag["content"]?></span>
            <span class="remove cursor-pointer px-2 py-1 bg-red-400 hover:bg-red-600" data-tag="<?=$tag["id"]?>">x</span>
        </dt>
    <?php endforeach; ?>
    <?php if (count($suggestionTags) == 0): ?>
        <p id="noTags">Keine Tagvorschläge gefunden</p>
    <?php else: ?>
        <?php foreach ($suggestionTags as $tag): ?>
            <dt class="suggestionTag inline-flex bg-gray-400 rounded-lg font-semibold px-2 py-1 cursor-pointer text-slate-700 hover:text-slate-950 hover:bg-gray-300">
                <span><?=$tag?></span>
            </dt>
        <?php endforeach; ?>
    <?php endif; ?>
</dl>