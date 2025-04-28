<div>
    <label class="fileUploader">
        <?= Classes\Project\Icon::get("iconUpload", 30, 30, [], "Hier ablegen") ?>
        <p><strong>Datei auswählen</strong> oder hier ablegen.</p>
        <input type="file" hidden data-fun="fileUploader" data-write="true" data-type="<?= $target ?? "" ?>">
    </label>
</div>