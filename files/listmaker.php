<script src="<?=Classes\Link::getResourcesShortLink("listcreator.js", "js")?>" type="module"></script>
<div>
    <div class="defCont">
        <h1>Listenübersicht</h1>
        <div>
        </div>
        <button class="btn-primary">Neue Liste erstellen</button>
        <div>
            <div>
                <label>
                    Listenname
                    <input type="text" placeholder="Listenname" class="rounded-sm m-1 p-1 w-80">
                </label>
                <span class="text-green-500 text-xl">✓</span>
            </div>
            <div>
                <label>
                    Listenpunktbezeichnung
                    <input type="text" placeholder="Listenpunktbezeichnung" class="rounded-sm m-1 p-1 w-80">
                </label>
                <label>
                    Listenpunkt Typ auwählen
                    <select id="listElementType">
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
                <button class="btn-primary" data-binding="true" data-fun="addListElementType">Hinzufügen</button>
            </div>
        </div>
    </div>
    <div class="defCont" id="previewContainer">
        <!-- preview -->
    </div>
</div>