window.addFileVehicle = function(key, event) {
    var form = document.getElementById("fileVehicle");
    form.style.display = "";

    /* hidden key input form */
    let hidden = document.createElement("input");
    hidden.name = "key";
    hidden.hidden = true;
    hidden.type = "text";
    hidden.value = key;
    form.appendChild(hidden);

    /* hidden key input form */
    let tableKey = document.createElement("input");
    tableKey.name = "tableKey";
    tableKey.hidden = true;
    tableKey.type = "text";
    tableKey.value = event.target.parentNode.parentNode.parentNode.parentNode.dataset.key;
    form.appendChild(tableKey);
}

export function addExistingVehicle() {
    if (globalData.vehicleId == 0) {
        return;
    }
        
    ajax.put(`/api/v1/order/${globalData.auftragsId}/vehicles/${globalData.vehicleId}`)
    .then(response => {
        document.getElementById("fahrzeugTable").innerHTML = response.table;
    });
}

export function addNewVehicle() {
    const kfz = document.getElementById("kfz").value;
    const fahrzeug = document.getElementById("fahrzeug").value;
    const kundennummer = document.getElementById("kundennummer").innerText;

    ajax.post({
        r: "insertCar",
        kfz: kfz,
        fahrzeug: fahrzeug,
        kdnr: kundennummer,
        auftrag: globalData.auftragsId,
    }, true).then(response => {
        /* table is in the div after the addVehicle form */
        let el = document.getElementById("addVehicle");
        el = el.nextElementSibling;
        el.innerHTML = response;
    });
}

export function selectVehicle(event) {
    globalData.vehicleId = event.target.value;
}
