import { ajax } from "js-classes/ajax";
import { addBindings } from "js-classes/bindings";
import { notification } from "js-classes/notifications";

import { FunctionMap } from "../types/types";

const fnNames: FunctionMap = {};

function init() {
    addBindings(fnNames);
}

/**
 * js-classes liefert bei Fehlern keine Server-Meldung mit (data ist null),
 * deshalb Texte je Statuscode.
 */
const errorText = (status: number | null, fallback: string): string => {
    switch (status) {
        case 403:
            return "Keine Berechtigung, diesen Benutzer zu bearbeiten.";
        case 404:
            return "Nicht gefunden, bitte Seite neu laden.";
        case 422:
            return fallback;
        default:
            return "Speichern fehlgeschlagen, bitte später erneut versuchen.";
    }
}

fnNames.click_addNewUser = async () => {
    const value = (id: string) => (document.getElementById(id) as HTMLInputElement).value.trim();

    const username = value("newUsername");
    const prename = value("newPrename");
    const lastname = value("newLastname");
    const email = value("newEmail");
    const password = (document.getElementById("newPassword") as HTMLInputElement).value;
    const confirmPassword = (document.getElementById("newPasswordRepeat") as HTMLInputElement).value;

    const infoEl = document.getElementById("addUserWarnings") as HTMLParagraphElement;
    infoEl.textContent = "";

    if (username == "" || prename == "" || lastname == "" || email == "" || password == "" || confirmPassword == "") {
        infoEl.textContent = "Alle Felder sind Pflichtfelder.";
        return;
    }

    if (password !== confirmPassword) {
        infoEl.textContent = "Passwörter stimmen nicht überein.";
        return;
    }

    if (password.length < 8) {
        infoEl.textContent = "Passwort ist zu kurz.";
        return;
    }

    const r = await ajax.post(`/api/v1/user`, {
        "username": username,
        "prename": prename,
        "lastname": lastname,
        "email": email,
        "password": password,
    });

    if (!r.success) {
        infoEl.textContent = errorText(r.status, "Benutzername oder E-Mail bereits vergeben, E-Mail ungültig oder Passwort zu schwach (mindestens 12 Zeichen oder 8 Zeichen mit drei Zeichenarten).");
        return;
    }

    window.location.reload();
}

fnNames.click_saveUser = async (e: Event) => {
    const container = document.getElementById("userData") as HTMLElement;
    const userId = container.dataset.userId;
    const value = (id: string) => (document.getElementById(id) as HTMLInputElement).value.trim();

    const email = value("email");
    const prename = value("prename");
    const lastname = value("lastname");
    const maxWorkingHours = value("maxWorkingHours");

    const infoEl = document.getElementById("userDataWarnings") as HTMLParagraphElement;
    infoEl.textContent = "";

    if (email === "" || prename === "" || lastname === "") {
        infoEl.textContent = "E-Mail, Vorname und Nachname sind Pflichtfelder.";
        return;
    }

    const button = e.currentTarget as HTMLButtonElement;
    button.disabled = true;

    const r = await ajax.put(`/api/v1/user/${userId}`, {
        email,
        prename,
        lastname,
        maxWorkingHours,
    });

    button.disabled = false;

    if (!r.success) {
        infoEl.textContent = errorText(r.status, "E-Mail ungültig oder bereits vergeben, oder Arbeitszeit außerhalb von 0 bis 168 Stunden.");
        return;
    }

    notification("", "success");
}

fnNames.click_removeDevice = async (e: Event) => {
    const button = e.currentTarget as HTMLButtonElement;
    const deviceId = button.dataset.deviceId;
    const userId = (document.getElementById("userData") as HTMLElement).dataset.userId;

    if (!confirm("Gerät entfernen? Die automatische Anmeldung auf diesem Gerät funktioniert danach nicht mehr.")) {
        return;
    }

    button.disabled = true;
    const r = await ajax.delete(`/api/v1/user/${userId}/devices/${deviceId}`);

    if (!r.success) {
        button.disabled = false;
        notification(errorText(r.status, "Gerät konnte nicht entfernt werden."), "failure");
        return;
    }

    button.closest("[data-device]")?.remove();
    notification("", "success");
}

if (document.readyState !== 'loading') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        init();
    });
}
