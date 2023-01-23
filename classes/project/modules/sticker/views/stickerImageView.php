<div class="innerDefCont imageMovableContainer">
    <?php foreach ($images as $image): ?>
    <div class="imageMovable">
        <img class="imgPreview" src="<?=$image["link"]?>" alt="<?=$image["alt"]?>">
    </div>
    <?php endforeach; ?>
</div>