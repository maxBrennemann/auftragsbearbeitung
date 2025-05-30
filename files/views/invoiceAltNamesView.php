<div class="w-96">
    <p class="font-semibold">Kopfzeile erstellen</p>
    <p class="text-xs">Es werden aktuell maximal vier Zeilen berücksichtigt.</p>
    <?php foreach ($altNames as $altName): ?>
        <div>
            <input type="text" value="<?= $altName["text"] ?>" data-id="<?= $altName["id"] ?>" class="input-primary w-72">
            <button class="btn-delete" data-fun="removeAltName" data-binding="true">Entfernen</button>
        </div>
    <?php endforeach; ?>
    <button class="btn-primary" data-binding="true" data-fun="addNewAltName">Neue Zeile</button>
    <template id="invoiceAltNameTemplate">
        <div>
            <input type="text" value="" class="input-primary w-72">
            <button class="btn-delete" data-fun="removeAltName" data-binding="true">Entfernen</button>
        </div>
    </template>
</div>