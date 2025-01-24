<div class="mt-4 p-3 mr-5 overflow-y-auto bg-gray-100 w-1/3">
    <div class="ml-1 my-3">
        <p class="font-bold">Alle Texte</p>
        <div class="grid grid-cols-3">
        <?php foreach($texts as $text): ?>
            <div class="ml-1 mt-2 bg-slate-100 p-3 rounded-lg">
                <p>Erstellt am <?=$text["creationDate"]?></p> 
                <p>Weitere Anweisungen:</p>
                <textarea readonly><?=$text["additionalQuery"]?></textarea>
                <p>Text:</p>
                <textarea data-id="<?=$text["id"]?>"><?=$text["chatgptResponse"]?></textarea>
                <button class="block px-4 py-2 m-1 font-semibold text-sm bg-blue-200 text-slate-600 rounded-lg shadow-xs border-none">Text auswählen</button>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
    <div class="ml-1">
        <p class="font-bold">Neuen Text erstellen</p>
        <div class="ml-1 mt-2 bg-slate-100 p-3 rounded-lg selectTextStyle">
            <p>Art/ Stilrichtung des Textes</p>
            <dt class="px-2 py-2 m-2 bg-blue-200 border-none inline-block rounded-lg font-semibold text-slate-600">Lustig</dt>
            <dt class="px-2 py-2 m-2 bg-blue-200 border-none inline-block rounded-lg font-semibold text-slate-600">Ernst</dt>
            <dt class="px-2 py-2 m-2 bg-blue-200 border-none inline-block rounded-lg font-semibold text-slate-600">Informativ</dt>
            <dt class="px-2 py-2 m-2 bg-blue-200 border-none inline-block rounded-lg font-semibold text-slate-600">Für Gewerbetreibende</dt>
            <dt class="px-2 py-2 m-2 bg-blue-200 border-none inline-block rounded-lg font-semibold text-slate-600">Für Privat</dt>
            <dt class="px-2 py-2 m-2 bg-blue-200 border-none inline-block rounded-lg font-semibold text-slate-600">Hobbyaufkleber</dt>
            <dt class="px-2 py-2 m-2 bg-blue-200 border-none inline-block rounded-lg font-semibold text-slate-600">Traurig</dt>
            <dt class="px-2 py-2 m-2 bg-blue-200 border-none inline-block rounded-lg font-semibold text-slate-600">Bayrisch</dt>
        </div>
        <div class="ml-1 mt-2 bg-slate-100 p-3 rounded-lg">
            <p>Zusätzliche Anweisungen</p>
            <input class="w-96 m-1 text-slate-600 rounded-lg p-2" type="text" id="additionalTextGPT" placeholder="Schreibe hier zusätzliche Anweisunge für ChatGPT">
        </div>
        <button class="px-4 py-2 m-1 font-semibold text-sm bg-blue-200 text-slate-600 rounded-lg shadow-xs border-none" data-binding="true" data-fun="textGenerationExtended" id="generateNewText">Neuen Text generieren</button>
    </div>
</div>