<div class="p-3 overflow-y-auto w-1/3">
    <div>
        <p class="font-semibold">Alle Texte</p>
        <div class="grid grid-cols-3">
            <?php foreach ($texts as $text): ?>
                <div class="mt-2 bg-slate-100 p-3 rounded-md">
                    <p>Erstellt am <?= $text["creationDate"] ?></p>
                    <p>Weitere Anweisungen:</p>
                    <textarea readonly class="input-primary mt-1"><?= $text["additionalQuery"] ?></textarea>
                    <p>Erstellter Text:</p>
                    <textarea data-id="<?= $text["id"] ?>" class="input-primary mt-1"><?= $text["chatgptResponse"] ?></textarea>
                    <button class="btn-primary mt-2">Text auswählen</button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="mt-2">
        <p class="font-semibold">Neuen Text erstellen</p>
        <div class="mt-2 bg-slate-100 p-3 rounded-md selectTextStyle">
            <p>Art/ Stilrichtung des Textes</p>
            <button class="btn-inactive mt-1">Lustig</button>
            <button class="btn-inactive mt-1">Ernst</button>
            <button class="btn-inactive mt-1">Informativ</button>
            <button class="btn-inactive mt-1">Für Gewerbetreibende</button>
            <button class="btn-inactive mt-1">Für Privat</button>
            <button class="btn-inactive mt-1">Hobbyaufkleber</button>
            <button class="btn-inactive mt-1">Traurig</button>
            <button class="btn-inactive mt-1">Bayrisch</button>
        </div>
        <div class="mt-2 bg-slate-100 p-3 rounded-md">
            <p>Zusätzliche Anweisungen</p>
            <input class="w-96 mt-1 input-primary" type="text" id="additionalTextGPT" placeholder="Schreibe hier zusätzliche Anweisunge für ChatGPT">
        </div>
    </div>
</div>