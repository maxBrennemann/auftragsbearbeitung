<?php

use Src\Classes\Project\Icon;

?>

<?php if ($f["type"] == "image") :?>
    <span>
        <img src="<?= $f["link"] ?>" alt="<?= $f["name"] ?>" width="40px" data-image-id="<?= $f["file"] ?>" class="inline">
        <p class="inline ml-2"><?= $f["name"] ?></p>
        <a target="_blank" rel="noopener noreferrer" href="<?= $f["link"] ?>" class="inline"><?= Icon::getDefault("iconNewTab") ?></a>
    </span>
<?php else : ?>
    <span>
        <a target="_blank" rel="noopener noreferrer" href="<?= $f["link"] ?>"><?= $f["name"] ?></a>
    </span>
<?php endif; ?>