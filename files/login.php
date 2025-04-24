<div class="defCont">
    <div>
        <label>
            <p>Nutzername oder Email-Adresse</p>
            <input type="text" class="input-primary w-60" id="name">
        </label>
        <label>
            <p>Passwort</p>
            <input type="password" class="input-primary w-60" name="password" placeholder="Passwort" id="password">
        </label>
    </div>
    <div class="mt-2">
        <label>
            <input type="checkbox" name="setAutoLogin" id="autologin">
            <span>Eingeloggt bleiben</span>
        </label>
        <button class="btn-primary-new block mt-2" data-binding="true" data-fun="login">Einloggen</button>
    </div>
    <div class="innerDefCont inline-flex m-0 mt-2 items-center" id="autoLogin">
        <svg id="spinningStatus" xmlns="http://www.w3.org/2000/svg" class="fill-black h-4 w-4 block" viewBox="0 0 24 24">
            <path d="M12,1A11,11,0,1,0,23,12,11,11,0,0,0,12,1Zm0,19a8,8,0,1,1,8-8A8,8,0,0,1,12,20Z" opacity=".25" />
            <path d="M10.14,1.16a11,11,0,0,0-9,8.92A1.59,1.59,0,0,0,2.46,12,1.52,1.52,0,0,0,4.11,10.7a8,8,0,0,1,6.66-6.61A1.42,1.42,0,0,0,12,2.69h0A1.57,1.57,0,0,0,10.14,1.16Z">
                <animateTransform attributeName="transform" type="rotate" dur="0.75s" values="0 12 12;360 12 12" repeatCount="indefinite" />
            </path>
        </svg>
        <p id="autologinStatus" class="ml-2">Prüfe automatischen Login</p>
    </div>
    <span id="loginStatus"></span>
</div>