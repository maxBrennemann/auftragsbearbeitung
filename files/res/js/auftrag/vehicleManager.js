import { ajax } from "../classes/ajax.js";
import { addBindings } from "../classes/bindings.js";
import { clearInputs } from "../global.js";

const fnNames = {};

fnNames.click_addNewVehicle = () => {
    const kfz = document.getElementById("kfz").value;
    const fahrzeug = document.getElementById("fahrzeug").value;
    const kundennummer = document.getElementById("kundennummer").innerText;

    ajax.post(`/api/v1/customer/${kundennummer}/vehicle`, {
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

fnNames.click_addExistingVehicle = e => {
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

export const initVehicles = () => {
    addBindings(fnNames);
}
