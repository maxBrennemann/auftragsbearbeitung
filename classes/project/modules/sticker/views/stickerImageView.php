<div class="innerDefCont imageMovableContainer" ondrop="itemDropHandler(event, '<?=$imageCategory?>');" ondragover="itemDragOverHandler(event);">
    <?php foreach ($images as $image): ?>
    <div class="imageMovable">
        <img class="imgPreview" src="<?=$image["link"]?>" alt="<?=$image["alt"]?>" onclick="imagePreview(event)" ondragstart="preventCopy(event)" draggable="true">
    </div>
    <?php endforeach; ?>
</div>