import { ajax } from "./classes/ajax.js";
import { initBindings } from "./classes/bindings.js";
import { infoSaveSuccessfull } from "./classes/statusInfo.js";

const fnNames = {};

const init = () => {
    initBindings(fnNames);
}

fnNames.write_updateName = e => {
    const vehicleId = document.getElementById("vehicleId").value;
    ajax.put(`/api/v1/order/vehicles/${vehicleId}/name`, {
        "name": e.currentTarget.value,
    }).then(r => {
        if (r.message == "ok") {
            infoSaveSuccessfull("success");
        }
    });
}

fnNames.write_updateLicensePlate = e => {
    const vehicleId = document.getElementById("vehicleId").value;
    ajax.put(`/api/v1/order/vehicles/${vehicleId}/license-plate`, {
        "licensePlate": e.currentTarget.value,
    }).then(r => {
        if (r.message == "ok") {
            infoSaveSuccessfull("success");
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
