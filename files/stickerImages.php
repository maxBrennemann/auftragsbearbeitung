<?php

$images = Classes\Sticker\StickerImage::getAllImageFiles();

?>
<div class="grid grid-cols-5 mt-3">
    <?php foreach ($images as $image): ?>
        <div>
            <img src="<?=Classes\Link::getResourcesShortLink($image["dateiname"], "upload");?>" loading="lazy" width="150" alt="<?=$image["alt"]?>">
        </div>
    <?php endforeach; ?>
</div>