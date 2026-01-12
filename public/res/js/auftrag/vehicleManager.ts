import { ajax } from "js-classes/ajax";
import { addBindings } from "js-classes/bindings";
import { notification } from "js-classes/notifications";

import { createPopup } from "../classes/helpers";
import { addRow, fetchAndRenderTable } from "../classes/table";
import { tableConfig } from "../classes/tableconfig";
import { initFileUploader } from "../classes/upload";
import { clearInputs } from "../global";

interface VehicleData {
    customerId: number,
    orderId: number,
    tableRef: HTMLElement | null,
    options: any;
}

const fnNames: { [key: string]: (...args: any[]) => void } = {};
const vehicleData: VehicleData = {
    customerId: 0,
    orderId: 0,
    tableRef: null,
    options: {
        hideOptions: ["check", "move", "edit", "addRow"],
        primaryKey: tableConfig["fahrzeuge"].primaryKey,
        hide: ["Kundennummer"],
        autoSort: true,
        styles: {
            "table": {
                "className": ["w-full"],
            },
        },
        conditions: {
            "Kundennummer": 0,
            "id_auftrag": 0,
        },
        link: "/fahrzeug?id=",
        joins: {
            "connected_vehicles": 0
        }
    }
}

fnNames.click_addNewVehicle = () => {
    const kfz = (document.getElementById("kfz") as HTMLInputElement).value;
    const fahrzeug = (document.getElementById("fahrzeug") as HTMLInputElement).value;

    ajax.post(`/api/v1/customer/${vehicleData.customerId}/vehicle`, {
        "licensePlate": kfz,
        "name": fahrzeug,
        "orderId": vehicleData.orderId,
    }).then((r: any) => {
        (document.getElementById("addVehicle") as HTMLElement).classList.add("hidden");

        addRow({
            "Nummer": r.data.id,
            "Kundennummer": vehicleData.customerId,
            "Kennzeichen": kfz,
            "Fahrzeug": fahrzeug,
        }, vehicleData.tableRef, vehicleData.options);

        clearInputs({
            "ids": ["kfz", "fahrzeug"],
        });
    });
}

fnNames.click_addExistingVehicle = () => {
    const vehicleId = (document.getElementById("selectVehicle") as HTMLInputElement).value;
    if (vehicleId == "addNew" || vehicleId == "0") {
        return;
    }

    ajax.put(`/api/v1/order/${vehicleData.orderId}/vehicles/${vehicleId}`)
        .then((r: any) => {
            addRow({
                "Nummer": vehicleId,
                "Kundennummer": vehicleData.customerId,
                "Kennzeichen": r.data.kfz,
                "Fahrzeug": r.data.fahrzeug,
            }, vehicleData.tableRef, vehicleData.options);
        }).catch((err: any) => {
            notification("Fahrzeug konnte nicht hinzugefügt werden.", "warning", "Bad reqeust when adding vehicle");
        });
}

fnNames.write_selectVehicle = (e: Event) => {
    const target = e.currentTarget as HTMLSelectElement;
    if (target.value == "addNew") {
        (document.getElementById("addVehicle") as HTMLElement).classList.remove("hidden");
        clearInputs({
            "ids": ["kfz", "fahrzeug"],
        });
    } else {
        (document.getElementById("addVehicle") as HTMLElement).classList.add("hidden");
    }
}

const createVehicleTable = async () => {
    vehicleData.options.conditions.Kundennummer = vehicleData.customerId;
    vehicleData.options.conditions.id_auftrag = vehicleData.orderId;
    vehicleData.options.joins.connected_vehicles = vehicleData.orderId;

    const table = await fetchAndRenderTable("fahrzeugTable", "fahrzeuge", vehicleData.options) as HTMLElement;
    table.addEventListener("rowUpload", (e: Event) => uploadVehicleFile(e as CustomEvent));
    table.addEventListener("rowDelete", (e: Event) => {
        const data = (e as CustomEvent).detail;

        ajax.delete(`/api/v1/order/${vehicleData.orderId}/vehicles/${data.Nummer}`).then(() => {
            data.row.remove();
        });
    });

    vehicleData.tableRef = table;
}

const uploadVehicleFile = async (e: CustomEvent) => {
    const data = e.detail;
    const uploadFile = await ajax.get(`/api/v1/template/uploadFile`, {
        "params": JSON.stringify({
            "target": "vehicle",
        }),
    });
    const div = document.createElement("div");
    div.innerHTML = uploadFile.data.content;
    const optionsContainer = createPopup(div);
    const btnCancel = optionsContainer.querySelector("button.btn-cancel");
    initFileUploader({
        "vehicle": {
            "location": `/api/v1/order/${vehicleData.orderId}/vehicle/${data.Nummer}/add-files`,
        },
    });

    div.addEventListener("fileUploaded", () => (btnCancel as HTMLButtonElement).click());
}

export const initVehicles = (customerId: number, orderId: number) => {
    addBindings(fnNames);
    vehicleData.customerId = customerId;
    vehicleData.orderId = orderId;
    createVehicleTable();
}
