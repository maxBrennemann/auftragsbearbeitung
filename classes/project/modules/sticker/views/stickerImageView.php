<div class="bg-zinc-300 rounded-lg imageUpload h-24 flex items-center cursor-pointer" data-drop-type="<?=$imageCategory?>">
    <div class="m-auto w-full text-center">
        <p class="font-bold text-gray-500">Bild hochladen</p>
    </div>
</div>
<div>
    <table class="w-full" data-image-type="<?=$imageCategory?>">
        <tr>
            <th>Bild</th>
            <th>Text</th>
            <th>Status</th>
            <th>Aktion</th>
        </tr>
        <?php foreach ($images as $image): ?>
        <tr>
            <td><img class="imgPreview cursor-pointer" data-file-id="<?=$image["id"]?>" src="<?=$image["link"]?>" alt="<?=$image["alt"]?>"></td>
            <td><input class="px-2 bg-inherit w-32" type="text" maxlength="125" placeholder="Beschreibung" data-write="true" data-fun="updateImageDescription" data-file-id="<?=$image["id"]?>" value="<?=$image["description"]?>"></td>
            <td></td>
            <td>
                <button class="p-1 mr-1 actionButton" title="Löschen" data-file-id="<?=$image["id"]?>" data-binding="true" data-fun="deleteImage"><?=Icon::getDefault("iconDelete")?></button>
                <button class="p-1 mr-1 actionButton moveRow" title="Verschieben" onmousedown="moveInit(event)" onmouseup="moveRemove(event)" data-file-id="<?=$image["id"]?>"><?=Icon::getDefault("iconMove")?></button>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>