<script src="<?=Link::getResourcesShortLink("listcreator.js", "js")?>" type="module"></script>
<div x-data="listcreator">
    <div class="defCont">
        <h1>Listenübersicht</h1>
        <div>
        </div>
        <button @click="toggle" class="btn-primary">Neue Liste erstellen</button>
        <div x-show="open">
            <div>
                <label>
                    Listenname
                    <input type="text" placeholder="Listenname" class="rounded-sm m-1 p-1 w-80" @input="validate">
                </label>
                <span x-show="nameValidated" class="text-green-500 text-xl">✓</span>
            </div>
            <div x-show="nameValidated">
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
    <div class="defCont" x-show="nameValidated" id="previewContainer">
        <!-- preview -->
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('listcreator', () => ({
            open: false,
            nameValidated: false,

            toggle() {
                this.open = ! this.open;
                if (this.open) {
                    history.pushState({}, null, "?addlist");
                } else {
                    history.back();
                }
            },
            validate(e) {
                const value = e.target.value;
                if (value.length < 3) {
                    this.nameValidated = false;
                    return;
                }
                this.nameValidated = true;
                history.pushState({}, null, "?addlist=" + value);
            },
        }))
    })

</script>