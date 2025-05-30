<div class="p-3 mt-2">
    <?php foreach ($resultGroups as $group) : ?>
        <h3 class="font-bold"><?=$group["groupName"]?></h3>
        <div class="searchGroup ml-2">
            <?php foreach ($group["results"] as $result): ?>
                <div class="searchItem p-4 bg-slate-200 my-2 cursor-pointer rounded-lg">
                    <p><a href="<?=$result['link']?>">Hier</a> zu <?=$result['message']?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>