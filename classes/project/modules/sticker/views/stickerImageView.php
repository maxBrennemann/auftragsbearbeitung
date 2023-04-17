<div class="innerDefCont imageMovableContainer" data-drop-type="<?=$imageCategory?>">
    <?php foreach ($images as $image): ?>
    <div class="imageMovable">
        <img class="imgPreview" data-deletable="true" data-file-id="<?=$image["id"]?>" src="<?=$image["link"]?>" alt="<?=$image["alt"]?>" draggable="true">
    </div>
    <?php endforeach; ?>
</div>