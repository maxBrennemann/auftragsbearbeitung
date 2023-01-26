<div class="innerDefCont imageMovableContainer" ondrop="itemDropHandler(event);" ondragover="itemDragOverHandler(event);">
    <?php foreach ($images as $image): ?>
    <div class="imageMovable" draggable="true">
        <img class="imgPreview" src="<?=$image["link"]?>" alt="<?=$image["alt"]?>" onclick="imagePreview(event)" ondragstart="preventCopy(event)">
    </div>
    <?php endforeach; ?>
</div>