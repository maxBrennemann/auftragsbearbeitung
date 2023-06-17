function login() {
    ajax.post({
        r: "login",
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
        if (r.length == 2) {
            setCookie("loginkey", r[1], 14);
        }
        //location.reload();
    });
}

function autoLogin() {
    if (getCookie("autologin") != "on") {
        return;
    }

    ajax.post({
        r: "checkAutoLogin",
        loginkey: getCookie("loginkey"),
        /* data for device detection */
        userAgent: window.navigator.userAgent,
        browser: DeviceDetector.getBrowser(),
        os: DeviceDetector.getOS(),
        isMobile: DeviceDetector.isMobile(),
        isTablet: DeviceDetector.isMobileTablet(),
    }).then(r => {
        if (r.status == "success") {
            setTimeout(function(){
                //location.reload();
            }, 1000);
        } else if (r.status == "failed") {
            console.log("auto login failed");
            setTimeout(function(){
                document.getElementById("autoLogin").innerHTML = "Bitte geben Sie Ihre Zugangsdaten ein.";
            }, 1000);
        }
    });
}

if (document.readyState !== 'loading' ) {
    autoLogin();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        autoLogin();
    });
}
