<?php

    require_once('classes/project/StickerImage.php');

    $id = 0;
    $stickerImage = null;
    if (isset($_GET['id'])) {
        $id = (int) $_GET['id'];
        $stickerImage = new StickerImage($id);
    }

    if ($id != 0):
?>
    <?php foreach ($stickerImage->files as $file): ?>
        <img src="">
    <?php endforeach ?>
<?php endif; ?>