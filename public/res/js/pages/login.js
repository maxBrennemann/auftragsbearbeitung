//@ts-nocheck

import { ajax } from "js-classes/ajax.js";
import { addBindings } from "js-classes/bindings.js"

import { DeviceDetector } from "../classes/deviceDetector.js";
import { setCookie, getCookie } from "../global.js";

const fnNames = {};

const init = () => {
    addBindings(fnNames);

    if (document.getElementById("isLoggedIn").value != "1") {
        autoLogin();
    }
}

fnNames.click_login = () => {
    if (document.getElementById("autologin").checked) {
        setCookie("autologin", "on", 356);
    }

    ajax.post(`/api/v1/auth/login`, {
        "name": document.getElementById("name").value,
        "password": document.getElementById("password").value,
        "setAutoLogin": document.getElementById("autologin").checked,
        "deviceKey": getCookie("deviceKey"),
        /* data for device detection */
        "userAgent": window.navigator.userAgent,
        "browser": DeviceDetector.getBrowser(),
        "os": DeviceDetector.getOS(),
        "isMobile": DeviceDetector.isMobile(),
        "isTablet": DeviceDetector.isMobileTablet(),
    }).then(r => {
        if (r.status == "success") {
            const deviceKey = r.deviceKey;
            const loginKey = r.loginKey;
            setCookie("deviceKey", deviceKey, 356);
            setCookie("loginKey", loginKey, 28);
            location.reload();
        } else if (r.status == "error") {
            document.getElementById("loginStatus").innerHTML = "Falscher Benutzername oder falsches Passwort.";
        }
    });
}

const autoLogin = async () => {
    if (getCookie("autologin") != "on") {
        return;
    }

    document.getElementById("autologin").checked = true;

    const autoLoginData = await ajax.post(`/api/v1/auth/login/auto`, {
        "loginKey": getCookie("loginKey"),
        "deviceKey": getCookie("deviceKey"),
        "setAutoLogin": document.getElementById("autologin").checked,
        /* data for device detection */
        "userAgent": window.navigator.userAgent,
        "browser": DeviceDetector.getBrowser(),
        "os": DeviceDetector.getOS(),
        "isMobile": DeviceDetector.isMobile(),
        "isTablet": DeviceDetector.isMobileTablet(),
    });

    if (autoLoginData.status == "success") {
        setTimeout(function () {
            document.getElementById("autologinStatus").innerHTML = "Sie werden eingeloggt...";
            document.getElementById("autologin").checked = true;
            setCookie("loginKey", autoLoginData.loginKey, 28);
            setCookie("autologin", "on", 356);
            location.reload();
        }, 1000);
    } else if (autoLoginData.status == "failed") {
        console.log("auto login failed");
        document.getElementById("spinningStatus").classList.add("hidden");
        setTimeout(function () {
            document.getElementById("autologinStatus").innerHTML = "Bitte geben Sie Ihre Zugangsdaten ein.";
            document.getElementById("spinningStatus").classList.add("hidden");
        }, 1000);
    }
}

if (document.readyState !== 'loading') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        init();
    });
}
