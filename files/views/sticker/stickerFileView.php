<?php

use Src\Classes\Link;
use Src\Classes\Project\Icon;

?>

<div class="innerDefCont imageMovableContainer cursor-pointer" data-drop-type="general">
    <?php foreach ($images as $image): ?>
        <div class="imageMovable">
            <img class="imgPreview" data-deletable="true" data-file-id="<?=$image["id"]?>" src="<?=$image["link"]?>" alt="<?=$image["alt"]?>">
        </div>
    <?php endforeach; ?>
    <?php foreach ($files as $file): ?>
        <?php
            $type = pathinfo("storage/upload/" . $file["dateiname"])["extension"];
        $icon = Icon::getDefault("iconFile");
        $link = Link::getResourcesShortLink($file["dateiname"], "upload");
        $originalname = $file["alt"] ?: "ohne Name";
        ?>
        <div class="imageMovable">
            <a class="imageTag" data-deletable="true" data-file-id="<?=$file["id"]?>" download="<?=$file["alt"]?>" href="<?=$link?>" data-image-id="<?=$file["id"]?>" data-deletable="true" title="Zum Herunterladen von '<?=$originalname?>' klicken">
                <?php if ($type == "svg"): ?>
                    <svg class="w-8 h-8">
                        <image xlink:href="<?= $link ?>" />
                    </svg>
                <?php elseif ($type == "png" || $type == "jpeg" || $type = "jpg"): ?>
                    <img class="w-8 h-8" src="<?= $link ?>">
                <?php else: ?>
                    <?=$icon?>
                <?php endif; ?>
            </a>
        </div>
    <?php endforeach; ?>
</div>