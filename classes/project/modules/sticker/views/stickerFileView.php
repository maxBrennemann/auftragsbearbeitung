<div class="innerDefCont imageMovableContainer" data-drop-type="general">
    <?php foreach ($images as $image): ?>
        <div class="imageMovable">
            <img class="imgPreview" data-deletable="true" data-file-id="<?=$image["id"]?>" src="<?=$image["link"]?>" alt="<?=$image["alt"]?>">
        </div>
    <?php endforeach; ?>
    <?php foreach ($files as $file): ?>
        <?php
            $type = pathinfo("upload/" . $file["dateiname"])["extension"];
            $icon = Icon::$iconFile;
            $link = Link::getResourcesShortLink($file["dateiname"], "upload");
            $originalname = $file["alt"] ?: "ohne Name";
        ?>
        <div class="imageMovable">
            <a class="imageTag" data-deletable="true" data-file-id="<?=$file["id"]?>" download="<?=$file["alt"]?>" href="<?=$link?>" data-image-id="<?=$file["id"]?>" data-deletable="true" title="Zum Herunterladen von '<?=$originalname?>' klicken">
                <?php if ($type == "cdr"): ?>
                    <img src="<?=Link::getImageLink("CorelDraw_Logo2.svg")?>">
                <?php elseif ($type == "ltp"): ?>
                    <img src="<?=Link::getImageLink("plotter.svg")?>">
                <?php else: ?>
                    <?=$icon?>
                <?php endif; ?>
            </a>
        </div>
    <?php endforeach; ?>
</div>