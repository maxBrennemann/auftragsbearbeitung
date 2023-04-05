<div class="innerDefCont imageMovableContainer" ondrop="itemDropHandler(event, 'general');" ondragover="itemDragOverHandler(event);">
    <?php foreach ($images as $image): ?>
        <div class="imageMovable">
            <img class="imgPreview" src="<?=$image["link"]?>" alt="<?=$image["alt"]?>" onclick="imagePreview(event)">
        </div>
    <?php endforeach; ?>
    <?php foreach ($files as $file): ?>
        <?php
            $type = pathinfo("upload/" . $file["dateiname"])["extension"];
            $icon = Icon::$iconFile;
            $link = Link::getResourcesShortLink($file["dateiname"], "upload");
            $originalname = $file["alt"] ?: "ohne Name";
            switch($type) {
                case "cdr":
                    $icon = Icon::iconCorel();
                    break;
                case "ltp":
                    $icon = Icon::iconLetterPlott();
                    break;
            }
        ?>
        <div class="imageMovable">
            <a class="imageTag" download="<?=$file["alt"]?>" href="<?=$link?>" data-image-id="<?=$file["id"]?>" data-deletable="true" title="Zum Herunterladen von '<?=$originalname?>' klicken">
                <?=$icon?>
            </a>
        </div>
    <?php endforeach; ?>
</div>