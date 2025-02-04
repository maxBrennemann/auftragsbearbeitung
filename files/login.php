<div class="defCont">
    <div>
        <label>
            <p>Nutzername oder Email-Adresse</p>
            <input type="text" class="input-primary-new w-60" id="name">
        </label>
        <label>
            <p>Passwort</p>
            <input type="password" class="input-primary-new w-60" name="password" placeholder="Passwort" id="password">
        </label>
    </div>
    <div class="mt-2">
        <label>
            <input type="checkbox" name="setAutoLogin" id="autologin">
            <span>Eingeloggt bleiben</span>
        </label>
        <button class="btn-primary-new block mt-2" data-binding="true" data-fun="login">Einloggen</button>
    </div>
    <div class="innerDefCont inline-flex m-0 mt-2" id="autoLogin">
        <div id="spinningStatus" class="lds-ring"><div></div><div></div><div></div><div></div></div>
        <p id="autologinStatus" class="ml-2">Prüfe automatischen Login</p>
    </div>
    <span id="loginStatus"></span>
</div>