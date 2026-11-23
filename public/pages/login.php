<?php

use Src\Classes\Controller\SessionController;
use Src\Classes\Link;

$isLoggedIn = SessionController::isLoggedIn();

?>
<input type="hidden" value="<?= $isLoggedIn ?>" id="isLoggedIn">
<?php if (!$isLoggedIn) : ?>
    <div class="flex justify-center px-2.5 py-6">
        <section class="defCont w-full max-w-sm m-0">
            <div class="section-head">
                <h2 class="section-title">Anmelden</h2>
                <span class="section-meta"><?= COMPANY_NAME ?></span>
            </div>
            <!-- Enter im Formular löst den Submit-Button aus (Klick → data-fun="login"); login.ts verhindert das echte Absenden.
                 Keine Inline-Handler (onsubmit=…): die Content Security Policy blockiert sie. -->
            <form class="flex flex-col gap-3" id="loginForm">
                <label class="field">
                    <span class="field-label">Benutzername oder E-Mail-Adresse</span>
                    <input type="text" class="input-primary w-full" id="name" autocomplete="username" autofocus>
                </label>
                <label class="field">
                    <span class="field-label">Passwort</span>
                    <input type="password" class="input-primary w-full" name="password" id="password" autocomplete="current-password">
                </label>
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="setAutoLogin" id="autologin">
                    Angemeldet bleiben
                </label>
                <p id="loginStatus" class="field-hint is-error min-h-4" role="alert"></p>
                <button type="submit" class="btn-primary w-full" data-binding="true" data-fun="login">Anmelden</button>
            </form>
            <div class="flex items-center gap-2 mt-4 text-sm text-text-muted" id="autoLogin">
                <svg id="spinningStatus" xmlns="http://www.w3.org/2000/svg" class="fill-current h-4 w-4" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M12,1A11,11,0,1,0,23,12,11,11,0,0,0,12,1Zm0,19a8,8,0,1,1,8-8A8,8,0,0,1,12,20Z" opacity=".25" />
                    <path d="M10.14,1.16a11,11,0,0,0-9,8.92A1.59,1.59,0,0,0,2.46,12,1.52,1.52,0,0,0,4.11,10.7a8,8,0,0,1,6.66-6.61A1.42,1.42,0,0,0,12,2.69h0A1.57,1.57,0,0,0,10.14,1.16Z">
                        <animateTransform attributeName="transform" type="rotate" dur="0.75s" values="0 12 12;360 12 12" repeatCount="indefinite" />
                    </path>
                </svg>
                <p id="autologinStatus">Prüfe automatischen Login</p>
            </div>
        </section>
    </div>
<?php else: ?>
    <div class="flex justify-center px-2.5 py-6">
        <section class="defCont w-full max-w-sm m-0">
            <div class="section-head">
                <h2 class="section-title">Angemeldet</h2>
            </div>
            <p>Du bist bereits angemeldet.</p>
            <a href="<?= Link::getPageLink("") ?>" class="btn-primary mt-3">Zur Startseite</a>
        </section>
    </div>
<?php endif; ?>
