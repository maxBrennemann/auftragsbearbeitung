import { ajax } from "./classes/ajax.js";

function login() {
    if (document.getElementById("autologin").checked) {
        setCookie("autologin", "on", 356);
    }

    ajax.post(`/api/v1/login`, {
        name: document.getElementById("name").value,
        password: document.getElementById("password").value,
        setAutoLogin: document.getElementById("autologin").checked,
        deviceKey: getCookie("deviceKey"),
        /* data for device detection */
        userAgent: window.navigator.userAgent,
        browser: DeviceDetector.getBrowser(),
        os: DeviceDetector.getOS(),
        isMobile: DeviceDetector.isMobile(),
        isTablet: DeviceDetector.isMobileTablet(),
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

function autoLogin() {
    if (getCookie("autologin") != "on") {
        return;
    }

    document.getElementById("autologin").checked = true;

    ajax.post(`/api/v1/login/auto`, {
        loginKey: getCookie("loginKey"),
        deviceKey: getCookie("deviceKey"),
        setAutoLogin: document.getElementById("autologin").checked,
        /* data for device detection */
        userAgent: window.navigator.userAgent,
        browser: DeviceDetector.getBrowser(),
        os: DeviceDetector.getOS(),
        isMobile: DeviceDetector.isMobile(),
        isTablet: DeviceDetector.isMobileTablet(),
    }).then(r => {
        if (r.status == "success") {
            setTimeout(function() {
                document.getElementById("autologinStatus").innerHTML = "Sie werden eingeloggt...";
                document.getElementById("autologin").checked = true;
                setCookie("loginKey", r.loginKey, 28);
                setCookie("autologin", "on", 356);
                location.reload();
            }, 1000);
        } else if (r.status == "failed") {
            console.log("auto login failed");
            document.getElementById("spinningStatus").classList.add("hidden");
            setTimeout(function(){
                document.getElementById("autologinStatus").innerHTML = "Bitte geben Sie Ihre Zugangsdaten ein.";
            }, 1000);
        }
    });
}

function init() {
    const loginBtn = document.getElementById("loginBtn");
    loginBtn.addEventListener("click", login);
    autoLogin();
}

if (document.readyState !== 'loading' ) {
    init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        init();
    });
}
