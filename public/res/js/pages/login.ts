//@ts-nocheck

import { ajax } from "js-classes/ajax";
import { addBindings } from "js-classes/bindings"
import { DeviceDetector } from "js-classes/deviceDetector";

import { setCookie, getCookie } from "../global";

const fnNames = {};

const init = () => {
    addBindings(fnNames);

    // Enter löst den Submit-Button aus (dessen Klick meldet an); das Formular selbst nicht absenden
    document.getElementById("loginForm")?.addEventListener("submit", e => e.preventDefault());

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
        // bei Fehlern (z. B. 401) ist r.data leer, deshalb ?. statt direktem Zugriff
        if (r.data?.status == "success") {
            const deviceKey = r.data.deviceKey;
            const loginKey = r.data.loginKey;
            setCookie("deviceKey", deviceKey, 356);
            setCookie("loginKey", loginKey, 28);
            location.reload();
        } else {
            document.getElementById("loginStatus").innerHTML = "Falscher Benutzername oder falsches Passwort.";
        }
    });
}

const autoLogin = async () => {
    if (getCookie("autologin") != "on") {
        // kein automatischer Login eingerichtet: Hinweis samt Spinner ausblenden
        document.getElementById("autoLogin")?.classList.add("hidden");
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

    if (autoLoginData.data?.status == "success") {
        setTimeout(function () {
            document.getElementById("autologinStatus").innerHTML = "Sie werden eingeloggt...";
            document.getElementById("autologin").checked = true;
            setCookie("loginKey", autoLoginData.data.loginKey, 28);
            setCookie("autologin", "on", 356);
            location.reload();
        }, 1000);
    } else {
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
