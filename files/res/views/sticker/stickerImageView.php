<div class="my-2">
    <?= Classes\Project\TemplateController::getTemplate("uploadFile", [
        "target" => $imageCategory,
    ]); ?>
</div>
<div>
    <table class="w-full" data-image-type="<?= $imageCategory ?>">
        <tr>
            <th>Bild</th>
            <th>Text</th>
            <th>Status</th>
            <th>Aktion</th>
        </tr>
        <?php foreach ($images as $image): ?>
            <tr>
                <td><img class="imgPreview cursor-pointer" data-file-id="<?= $image["id"] ?>" src="<?= $image["link"] ?>" alt="<?= $image["alt"] ?>"></td>
                <td><input class="px-2 bg-inherit w-32" type="text" maxlength="125" placeholder="Beschreibung" data-write="true" data-fun="updateImageDescription" data-file-id="<?= $image["id"] ?>" value="<?= $image["description"] ?>"></td>
                <td>
                    <?php if ($image["typ"] == "avif" || $image["typ"] == "webp"): ?>
                        <p class="text-sm">Dieser Dateityp wird von Prestashop nicht unterstützt</p>
                    <?php endif; ?>
                </td>
                <td>
                    <button class="p-1 mr-1 actionButton" title="Löschen" data-file-id="<?= $image["id"] ?>" data-binding="true" data-fun="deleteImage"><?= Classes\Project\Icon::getDefault("iconDelete") ?></button>
                    <button class="p-1 mr-1 actionButton moveRow" title="Verschieben" onmousedown="moveInit(event)" onmouseup="moveRemove(event)" data-file-id="<?= $image["id"] ?>"><?= Classes\Project\Icon::getDefault("iconMove") ?></button>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <div>
        <button class="text-xs border-0 float-right p-1 hover:underline" onclick="((e) => {e.target.nextElementSibling.classList.toggle('hidden')})(event)">Mehr</button>
        <div class="hidden absolute h-48 place-items-center bg-white z-20 rounded-md p-8">
            <!-- TODO: add close button -->
            <p class="text-base">Vorsicht: Diese Option überschreibt die aktuellen Bilder des Artikels!</p>
            <div class="px-2">
                <p class="text-sm italic">Die Einstellung bleibt nur für diese Sitzung erhalten.</p>
                <input type="checkbox" id="forceUpload-<?= $imageCategory ?>" name="forceUpload" value="true" data-type="<?= $imageCategory ?>" data-binding="true" data-fun="updateImageOverwrite">
                <label for="forceUpload-<?= $imageCategory ?>">Bilder erneut hochladen</label>
            </div>
        </div>
    </div>
</div>