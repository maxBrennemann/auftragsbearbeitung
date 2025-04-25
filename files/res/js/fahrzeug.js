import { ajax } from "./classes/ajax.js";
import { addBindings } from "./classes/bindings.js";
import { notification } from "./classes/notifications.js";

const fnNames = {};

const init = () => {
    addBindings(fnNames);
}

fnNames.write_updateName = e => {
    const vehicleId = document.getElementById("vehicleId").value;
    ajax.put(`/api/v1/order/vehicles/${vehicleId}/name`, {
        "name": e.currentTarget.value,
    }).then(r => {
        if (r.message == "ok") {
            notification("", "success");
        }
    });
}

fnNames.write_updateLicensePlate = e => {
    const vehicleId = document.getElementById("vehicleId").value;
    ajax.put(`/api/v1/order/vehicles/${vehicleId}/license-plate`, {
        "licensePlate": e.currentTarget.value,
    }).then(r => {
        if (r.message == "ok") {
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
