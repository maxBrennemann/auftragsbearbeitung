<div data-fun="fileUploader" data-drop="true" data-dragover="true">
    <label class="fileUploader">
        <?= Classes\Project\Icon::get("iconUpload", 30, 30, [], "Hier ablegen") ?>
        <p class="text-center"><span class="font-semibold">Datei(en) auswählen</span> oder hier ablegen.</p>
        <input type="file" accept="<?= $accept ?? "" ?>" hidden data-fun="fileUploader" data-write="true" data-type="<?= $target ?? "" ?>" <?= isset($singleFile) ? "" : "multiple" ?>>
    </label>
</div>