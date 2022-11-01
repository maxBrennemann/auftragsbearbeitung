<div class="defCont">
    <form id="loginform">
        <input type="text" name="loginData" autofocus placeholder="Nutzername oder Email" id="username">
        <br>
        <input type="password" name="password" placeholder="Passwort" id="password">
        <br>
        <input type="checkbox" name="setAutoLogin" id="autologin">
        <label for="setAutoLogin">Eingeloggt bleiben</label>
        <!-- lazy solution for now -->
        <input type="text" hidden name="browserInfo" id="userAgent">
        <br>
        <input type="submit" value="Einloggen" name="info" onclick="login(event)">
    </form>
    <div class="innerDefCont" id="autoLogin">
        <div class="lds-ring"><div></div><div></div><div></div><div></div></div>
        <p>Prüfe automatischen Login</p>
    </div>
</div>
<script>
    document.getElementById("userAgent").value = window.navigator.userAgent;

    function login(e) {
        e.preventDefault();
        let params = {
            info: "Einloggen",
            login_session: "logmein",
            loginData: document.getElementById("username").value,
            password: document.getElementById("password").value,
            browserInfo: document.getElementById("userAgent").value,
            setAutoLogin: document.getElementById("autologin").value,
        };

        var logmein = new AjaxCall(params, "POST", window.location.href);
        logmein.makeAjaxCall(function (response) {
           response = JSON.parse(response);
           if (response.length == 2) {
            setCookie("loginkey", response[1], 14);
           }
           location.reload();
        });
    }

    /* https://www.geekstrick.com/snippets/how-to-parse-cookies-in-javascript/ */
    function checkCookies() {
        var cookies = document.cookie.split(";");
        var cookieObj = {};
        for (let i = 0; i < cookies.length; i++) {
            var parts = cookies[i].split("=");
            parts[0] = parts[0].substring(1);
            cookieObj[parts[0]] = parts[1];
        }

        return cookieObj;
    }

    /* https://www.w3schools.com/js/js_cookies.asp */
    function setCookie(cname, cvalue, exdays) {
        const d = new Date();
        d.setTime(d.getTime() + (exdays*24*60*60*1000));
        let expires = "expires="+ d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    }

    function autoLogin() {
        var allCookies = checkCookies();
        if ("autologin" in allCookies) {
            var auto = allCookies["autologin"];
            if (auto != "on") {
                return;
            }
        }

        let params = {
            getReason: "checkAutoLogin",
            userAgent: window.navigator.userAgent,
            loginkey: allCookies["loginkey"]
        };

        var checkAutoLogin = new AjaxCall(params, "POST", window.location.href);
        checkAutoLogin.makeAjaxCall(function (response) {
            console.log(response);
            if (response == "success") {
                setTimeout(function(){
                    location.reload();
                }, 1000);
            } else if (response == "failed") {
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
</script>
<!-- https://loading.io/css/ -->
<style>
    .defCont {
        background-color: #eff0f1;
    }

    .innerDefCont {
        display: inline-block;
        background-color: #b1b1b1;
    }

    .innerDefCont p {
        display: inline-block;
    }

    form {
        border-radius: 6px;
        background-color: #eff0f1;
        padding: 10px;
        margin: 10px;
    }

    form input {
        margin: 7px;
    }

    form input::placeholder {
        color: #B2B2BE;
    }

    form input[type=text], form input[type=password] {
        padding: 5px;
        border: none;
        border-radius: 6px;
        background-color: white;
    }

    form input[type=submit] {
        border-radius: 6px;
        border: none;
        padding: 5px;
        background-color: #B2B2BE;
        color: white;
    }

    .lds-ring {
        display: inline-block;
        position: relative;
        width: 20px;
        height: 20px;
    }

    .lds-ring div {
        box-sizing: border-box;
        display: block;
        position: absolute;
        width: 16px;
        height: 16px;
        margin: 2px;
        border: 2px solid #fff;
        border-radius: 50%;
        animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
        border-color: #fff transparent transparent transparent;
    }

    .lds-ring div:nth-child(1) {
        animation-delay: -0.45s;
    }

    .lds-ring div:nth-child(2) {
        animation-delay: -0.3s;
    }
    
    .lds-ring div:nth-child(3) {
        animation-delay: -0.15s;
    }
    
    @keyframes lds-ring {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }
</style>