<div class="mt-2">
    <div class="ml-1">
        <p class="font-semibold">Alle Texte</p>
        <?php foreach($texts as $text): ?>
            <div>
                <p>Erstellt am <?=$text["creationDate"]?> mit <textarea readonly><?=$text["additionalQuery"]?></textarea></p>
                <textarea data-id="<?=$text["id"]?>"><?=$text["chatgptResponse"]?></textarea>
                <button>Text auswählen</button>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="ml-1">
        <p class="font-semibold">Neuen Text erstellen</p>
        <div>
            <p>Art/ Stilrichtung des Textes</p>
            <dt class="px-2 py-2 m-2 bg-slate-100 border-none inline rounded-lg">Lustig</dt>
            <dt class="px-2 py-2 m-2 bg-slate-100 border-none inline rounded-lg">Ernst</dt>
            <dt class="px-2 py-2 m-2 bg-slate-100 border-none inline rounded-lg">Informativ</dt>
            <dt class="px-2 py-2 m-2 bg-slate-100 border-none inline rounded-lg">Für Gewerbetreibende</dt>
            <dt class="px-2 py-2 m-2 bg-slate-100 border-none inline rounded-lg">Für Privat</dt>
            <dt class="px-2 py-2 m-2 bg-slate-100 border-none inline rounded-lg">Hobbyaufkleber</dt>
            <dt class="px-2 py-2 m-2 bg-slate-100 border-none inline rounded-lg">Traurig</dt>
            <dt class="px-2 py-2 m-2 bg-slate-100 border-none inline rounded-lg">Bayrisch</dt>
        </div>
        <div class="mt-2">
            <p>Zusätzliche Anweisungen</p>
            <input class="w-64" type="text" placeholder="Schreibe hier zusätzliche Anweisunge für ChatGPT">
        </div>
        <button>Neuen Text generieren</button>
    </div>
</div>