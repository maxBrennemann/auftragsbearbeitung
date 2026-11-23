<div class="w-96 max-w-full flex flex-col gap-2">
    <p class="section-title">Kopfzeile anpassen</p>
    <p class="field-hint">Ersetzt auf der Rechnung den Firmennamen. Berücksichtigt werden höchstens vier Zeilen.</p>
    <?php foreach ($altNames as $altName): ?>
        <div class="flex items-center gap-2">
            <input type="text" value="<?= $altName["text"] ?>" data-id="<?= $altName["id"] ?>" class="input-primary flex-1" aria-label="Zeile der Kopfzeile">
            <button type="button" class="btn-danger btn-sm" data-fun="removeAltName" data-binding="true">Entfernen</button>
        </div>
    <?php endforeach; ?>
    <button type="button" class="btn-secondary self-start" data-binding="true" data-fun="addNewAltName">Zeile hinzufügen</button>
    <template id="invoiceAltNameTemplate">
        <div class="flex items-center gap-2">
            <input type="text" value="" class="input-primary flex-1" aria-label="Zeile der Kopfzeile">
            <button type="button" class="btn-danger btn-sm" data-fun="removeAltName" data-binding="true">Entfernen</button>
        </div>
    </template>
</div>
