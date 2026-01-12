<?php

use Src\Classes\Project\Icon;

?>

<?php if ($f["type"] == "image") :?>
    <span>
        <img src="<?= $f["link"] ?>" alt="<?= $f["name"] ?>" width="40px" data-image-id="<?= $f["file"] ?>" class="inline" title="Klicken für Vorschau" alt="<?= $f["alt"] ?>">
        <a target="_blank" rel="noopener noreferrer" href="<?= $f["link"] ?>" class="inline ml-2 hover:underline hover:underline-offset-1">
            <span><?= $f["name"] ?></span>
            <?= Icon::getDefault("iconNewTab") ?>
        </a>
    </span>
<?php else : ?>
    <span>
        <a target="_blank" rel="noopener noreferrer" href="<?= $f["link"] ?>" class="hover:underline hover:underline-offset-1"><?= $f["name"] ?></a>
    </span>
<?php endif; ?>