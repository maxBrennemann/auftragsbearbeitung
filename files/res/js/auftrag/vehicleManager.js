import { ajax } from "../classes/ajax.js";
import { addBindings } from "../classes/bindings.js";
import { clearInputs, createPopup, getTemplate } from "../global.js";
import { tableConfig } from "../classes/tableconfig.js";
import { fetchAndRenderTable } from "../classes/table.js";
import { initFileUploader } from "../classes/upload.js";

const fnNames = {};
const customerId = document.getElementById("kundennummer").innerText;

fnNames.click_addNewVehicle = () => {
    const kfz = document.getElementById("kfz").value;
    const fahrzeug = document.getElementById("fahrzeug").value;

    ajax.post(`/api/v1/customer/${customerId}/vehicle`, {
        "licensePlate": kfz,
        "name": fahrzeug,
        "orderId": globalData.auftragsId,
    }).then(r => {
        document.getElementById("fahrzeugTable").innerHTML = r.table;
        document.getElementById("addVehicle").classList.add("hidden");

        clearInputs({
            "ids": ["kfz", "fahrzeug"],
        });
    });
}

fnNames.click_addExistingVehicle = () => {
    const vehicleId = document.getElementById("selectVehicle").value;
    if (vehicleId == "addNew") {
        return;
    }

    ajax.put(`/api/v1/order/${globalData.auftragsId}/vehicles/${vehicleId}`)
    .then(r => {
        document.getElementById("fahrzeugTable").innerHTML = r.table;
    });
}

fnNames.write_selectVehicle = e => {
    const target = e.currentTarget;
    if (target.value == "addNew") {
        document.getElementById("addVehicle").classList.remove("hidden");
    } else {
        document.getElementById("addVehicle").classList.add("hidden");
    }
}

const createVehicleTable = async () => {
    const config = tableConfig["fahrzeuge"];
    const options = {
        "hideOptions": ["check", "move", "edit", "addRow"],
        "primaryKey": config.primaryKey,
        "hide": ["Kundennummer"],
        "autoSort": true,
        "styles": {
            "table": {
                "className": ["w-full"],
            },
        },
        "conditions": {
            "Kundennummer": customerId,
            "id_auftrag": globalData.auftragsId,
        },
        "link": "/fahrzeug?id=",
        "joins": {
            "connected_vehicles": globalData.auftragsId
        }
    };

    const table = await fetchAndRenderTable("fahrzeugTable", "fahrzeuge", options);
    table.addEventListener("rowUpload", e => uploadVehicleFile(e));
    table.addEventListener("rowDelete", e => {
        const data = e.detail;

        ajax.delete(`/api/v1/order/${globalData.auftragsId}/vehicles/${data.Nummer}`).then(() => {
            data.row.remove();
        });
    })
}

const uploadVehicleFile = async (e) => {
    const data = e.detail;
    const uploadFile = await ajax.get(`/api/v1/template/uploadFile`, {
        "params": JSON.stringify({
            "target": "vehicle",
        }),
    });
    const div = document.createElement("div");
    div.innerHTML = uploadFile.content;
    const optionsContainer = createPopup(div);
    const btnCancel = optionsContainer.querySelector("button.btn-cancel");
    initFileUploader({
        "vehicle": {
            "location": `/api/v1/order/${globalData.auftragsId}/vehicle/${data.Nummer}/add-files`,
        },
    });

    div.addEventListener("fileUploaded", () => btnCancel.click());
}

export const initVehicles = () => {
    addBindings(fnNames);
    createVehicleTable();
}
