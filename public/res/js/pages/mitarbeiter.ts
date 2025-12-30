import { addBindings } from "js-classes/bindings";
import { ajax } from "js-classes/ajax";
import { FunctionMap } from "../types/types";

const fnNames: FunctionMap = {};

function init() {
    addBindings(fnNames);
}

fnNames.click_showAddUserForm = () => {
    const addNewUserForm = document.getElementById('addNewUserForm') as HTMLElement;
    addNewUserForm.classList.toggle('hidden');
}

fnNames.click_addNewUser = () => {
    const usernameInput = document.getElementById("newUsername") as HTMLInputElement;
    const prenameInput = document.getElementById("newPrename") as HTMLInputElement;
    const lastnameInput = document.getElementById("newLastname") as HTMLInputElement;
    const emailInput = document.getElementById("newEmail") as HTMLInputElement;
    const passwordInput = document.getElementById("newPassword") as HTMLInputElement;
    const confirmPasswordInput = document.getElementById("newPasswordRepeat") as HTMLInputElement;

    const username = usernameInput.value;
    const prename = prenameInput.value;
    const lastname = lastnameInput.value;
    const email = emailInput.value;
    const password = passwordInput.value;
    const confirmPassword = confirmPasswordInput.value;

    const infoEl = document.getElementById("addUserWarnings") as HTMLParagraphElement;

    if (username == "" || prename == "" || lastname == "" || email == "" || password == "" || confirmPassword == "") {
        infoEl.innerHTML = "Alle Felder sind Pflichtfelder.";
        return;
    }

    if (password !== confirmPassword) {
        infoEl.innerHTML = "Passwörter stimmen nicht überein.";
        return;
    }

    if (password.length < 8) {
        infoEl.innerHTML = "Passwort ist zu kurz.";
        return;
    }

    ajax.post(`/api/v1/user`, {
        "username": username,
        "prename": prename,
        "lastname": lastname,
        "email": email,
        "password": password,
    }).then((r: any) => {
        window.location.reload();
    })
}

if (document.readyState !== 'loading') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        init();
    });
}
