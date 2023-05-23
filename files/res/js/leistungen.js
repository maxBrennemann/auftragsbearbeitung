if (document.readyState !== 'loading' ) {
    initLeistungen();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        initLeistungen();
    });
}

function initLeistungen() {
    const add = document.getElementById("addNew");
    add.addEventListener("click", addLeistung, false);

    const cancle = document.getElementById("cancleNew");
    cancle.addEventListener("click", cancleLeistung, false);

    const leistungen = document.querySelectorAll(".leistungen");
    Array.from(leistungen).forEach(l => {
        setButtonListeners(l);
    });
}

function setButtonListeners(leistung) {
    const btns = leistung.querySelectorAll("button");
    const btnRemove = btns[0];
    const btnSave = btns[1];

    btnRemove.addEventListener("click", removeLeistung, false);
    btnSave.addEventListener("click", saveLeistung, false);
}

function removeLeistung(e) {
    const remove = e.target.parentNode.dataset.removeId;
    ajax.post({
        r: "",
        id: remove,
    }).then(r => {

    });
}

function saveLeistung(e) {
    const el = e.target.parentNode;
    const id = el.dataset.removeId;
    const inputs = el.querySelectorAll("input");

    const infoHandler = new StatusInfoHandler();
    const infoBox = infoHandler.addInfoBox(StatusInfoHandler.TYPE_ERRORCOPY, "wird übertragen");

    ajax.post({
        r: "editLeistung",
        id: id,
        bezeichnung: inputs[0].value,
        description: inputs[1].value,
        source: inputs[2].value,
        aufschlag: inputs[3].value,
    }).then(r => {
        if (r.status == "success") {
            infoBox.statusUpdate(StatusInfoHandler.STATUS_SUCCESS);
        }
      }).catch(error => {
        infoBox.statusUpdate(StatusInfoHandler.STATUS_FAILURE, error); 
      });
}

function cancleLeistung() {
    const bezeichnung = document.getElementById("bezeichnung");
    const description = document.getElementById("description");
    const source = document.getElementById("source");
    const aufschlag = document.getElementById("aufschlag");

    bezeichnung.value = "";
    description.value = "";
    source.value = "";
    aufschlag.value = "";
}

function addLeistung() {
    const bezeichnung = document.getElementById("bezeichnung").value;
    const description = document.getElementById("description").value;
    const source = document.getElementById("source").value;
    const aufschlag = document.getElementById("aufschlag").value;

    ajax.post({
        r: "addLeistung",
        bezeichnung: bezeichnung,
        description: description,
        source: source,
        aufschlag: aufschlag,
    }).then(r => {
        if (r.status == "success") {
            insertNewLeistungsNode(bezeichnung, description,source, aufschlag, r.leistungId);
        }
    });
}

function insertNewLeistungsNode(bez, desc, source, aufschlag, id) {
    const leistungen = document.getElementById("leistungen");
    /* leistungen.lastChild.previousElement returns a #text,
     * here node is the penultimate div container
     */
    const node = leistungen.children[leistungen.children.length - 2];
    const clonedNode = node.cloneNode(true);
    const ref = node.parentNode.insertBefore(clonedNode, leistungen.lastChild);

    const inputs = ref.querySelectorAll("input");
    ref.dataset.removeId = id;
    inputs[0].value = bez;
    inputs[1].value = desc;
    inputs[2].value = source;
    inputs[3].value = aufschlag;

    setButtonListeners(ref);
}
