import { ajax } from "js-classes/ajax";
import { addBindings } from "js-classes/bindings"
import { notification } from "js-classes/notifications";
import { FunctionMap } from "../types/types";

const fnNames = {} as FunctionMap;

const init = () => {
    addBindings(fnNames);
}

fnNames.write_updateName = e => {
    const vehicleId = (document.getElementById("vehicleId") as HTMLSelectElement).value;
    ajax.put(`/api/v1/order/vehicles/${vehicleId}/name`, {
        "name": e.currentTarget.value,
    }).then(r => {
        if (r.data.message == "ok") {
            notification("", "success");
        }
    });
}

fnNames.write_updateLicensePlate = e => {
    const vehicleId = (document.getElementById("vehicleId") as HTMLSelectElement).value;
    ajax.put(`/api/v1/order/vehicles/${vehicleId}/license-plate`, {
        "licensePlate": e.currentTarget.value,
    }).then(r => {
        if (r.data.message == "ok") {
            notification("", "success");
        }
    });
}

if (document.readyState !== 'loading') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        init();
    });
}
