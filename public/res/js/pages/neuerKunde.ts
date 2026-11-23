//@ts-nocheck

import { ajax } from "js-classes/ajax";
import { addBindings } from "js-classes/bindings"

const globalProperties = {
    company: null,
    person: null,
    companyForm: null,
    privateForm: null,
    current: null,
};

const fnNames = {};

function initAddCustomer() {
    addBindings(fnNames);

    globalProperties.company = document.getElementById("showCompanies");
    globalProperties.person = document.getElementById("showPersons");
    globalProperties.current = globalProperties.company;

    globalProperties.companyForm = document.getElementById("companyForm");
    globalProperties.privateForm = document.getElementById("privateForm");

    globalProperties.company.addEventListener("click", () => selectTab(globalProperties.company));
    globalProperties.person.addEventListener("click", () => selectTab(globalProperties.person));
}

/** Umschalten zwischen Firma und Privatkunde: Zustand über aria-selected/is-active, Formulare nur über `hidden` */
function selectTab(tab) {
    if (globalProperties.current == tab) {
        return;
    }

    const isCompany = tab == globalProperties.company;
    globalProperties.companyForm.classList.toggle("hidden", !isCompany);
    globalProperties.privateForm.classList.toggle("hidden", isCompany);

    [globalProperties.company, globalProperties.person].forEach(button => {
        const selected = button == tab;
        button.classList.toggle("is-active", selected);
        button.setAttribute("aria-selected", String(selected));
    });

    globalProperties.current = tab;
}

fnNames.click_sendCustomerData = () => {
    let form = null;
    switch (globalProperties.current) {
        case globalProperties.company:
            form = document.getElementById("cForm");
            break;
        case globalProperties.person:
            form = document.getElementById("pForm");
            break;
        default:
            return;
    }
    const data = new FormData(form);

    var object = {};
    data.forEach((value, key) => object[key] = value);
    object.type = globalProperties.current == globalProperties.company ? "company" : "private";

    ajax.post(`/api/v1/customer`, object).then(r => {
        if (r.data.status == "success") {
            location.href = r.data.link;
        }
    });
}

if (document.readyState !== 'loading' ) {
    initAddCustomer();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        initAddCustomer();
    });
}
