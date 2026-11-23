<section class="defCont">
    <div class="section-head">
        <h2 class="section-title">Liste erstellen</h2>
        <span class="section-meta">Checklisten und Formulare, die an Aufträge angehängt werden können</span>
    </div>
    <div class="field-grid max-w-2xl">
        <label class="field">
            <span class="field-label">Name der Liste</span>
            <input type="text" class="input-primary" data-binding="true" data-fun="">
        </label>
        <label class="field">
            <span class="field-label">Element hinzufügen</span>
            <select id="nodeElementType" class="input-primary">
                <option>Auswählen</option>
                <option value="select">Auswahlliste</option>
                <option value="checkbox">Checkliste zum Abhaken</option>
                <option value="radio">Auswahloption</option>
                <option value="text">Freitext</option>
                <option value="upload">Dateiupload</option>
                <option value="date">Datum</option>
                <option value="textarea">Textfeld</option>
                <option value="link">Link</option>
                <option value="download">Download</option>
                <option value="number">Zahl</option>
                <option value="email">E-Mail</option>
            </select>
        </label>
    </div>
</section>

<section class="defCont">
    <div class="section-head">
        <h2 class="section-title">Vorschau</h2>
    </div>
    <div id="listPreview" class="sheet min-h-24"></div>
</section>
