<div class="defCont">
    <div>
        <label>
            <p>Nutzername oder Email-Adresse</p>
            <input type="text" class="block rounded-sm m-1 ml-0 p-1 w-80" id="name">
        </label>
        <label>
            <p>Passwort</p>
            <input type="password" class="block rounded-sm m-1 ml-0 p-1 w-80" name="password" placeholder="Passwort" id="password">
        </label>
        <label>
            <input type="checkbox" name="setAutoLogin" id="autologin">
            <span>Eingeloggt bleiben</span>
        </label>
        <button class="btn-primary block" onclick="login()">Einloggen</button>
    </div>
    <div class="innerDefCont" id="autoLogin">
        <div class="lds-ring"><div></div><div></div><div></div><div></div></div>
        <p>Prüfe automatischen Login</p>
    </div>
</div>