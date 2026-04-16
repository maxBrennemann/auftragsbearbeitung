<?php

/* TODO: adapt new translate feature later */
$text = "";
if (isset($singleFile)) {
    $text = t("upload.singleFile");
} else {
    $text = t("upload.multipleFiles");
}

$text .= " " . t("upload.orDragAndDrop");
?>

<div data-fun="fileUploader" data-drop="true" data-dragover="true" title="<?= $text ?>">
    <label class="fileUploader">
        <?= \Src\Classes\Project\Icon::get("iconUpload", 30, 30, []) ?>
        <p class="text-center"><?= $text ?></p>
        <input type="file" accept="<?= $accept ?? "" ?>" hidden data-fun="fileUploader" data-write="true" data-type="<?= $target ?? "" ?>" <?= isset($singleFile) ? "" : "multiple" ?>>
    </label>
</div>