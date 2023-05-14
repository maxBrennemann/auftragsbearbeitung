<div class="mt-2">
    <div>
        <p>Alle Texte</p>
        <?php foreach($texts as $text): ?>
            <div>
                <p>Erstellt am <?=$text["creationDate"]?> mit <textarea readonly><?=$text["additionalQuery"]?></textarea></p>
                <textarea data-id="<?=$text["id"]?>"><?=$text["chatgptResponse"]?></textarea>
                <button>Text auswählen</button>
            </div>
        <?php endforeach; ?>
    </div>
    <div>
        <p>Neuen Text erstellen</p>
        <div>
            <p>Art/ Stilrichtung des Textes</p>
            <dt>Lustig</dt>
            <dt>Ernst</dt>
            <dt>Informativ</dt>
            <dt>Für Gewerbetreibende</dt>
            <dt>Für Privat</dt>
            <dt>Hobbyaufkleber</dt>
            <dt>Traurig</dt>
            <dt>Bayrisch</dt>
        </div>
        <div class="mt-2">
            <p>Zusätzliche Anweisungen</p>
            <input class="w-64" type="text" placeholder="Schreibe hier zusätzliche Anweisunge für ChatGPT">
        </div>
        <button>Neuen Text generieren</button>
    </div>
</div>