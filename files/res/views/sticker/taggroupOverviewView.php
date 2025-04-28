<div class="mt-2">
    <p>Taggruppen:</p>
    <?php foreach ($taggroup as $group): ?>
    <div class="defCont">
        <p><?=$group["name"]?></p>
        <?php foreach ($group["tags"] as $tag): ?>
        <dt class="inline"><?=$tag["value"]?>
        <?php endforeach; ?>
        <button>Alle übernehmen</button>
        <input type="text" class="tagInput" maxlength="32" onkeydown="addTagToGroup(event)">
    </div>
    <?php endforeach; ?>
</div>