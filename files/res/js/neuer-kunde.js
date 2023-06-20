const globalProperties = {
    company: null,
    person: null,
    companyForm: null,
    privateForm: null,
    current: null,
};

if (document.readyState !== 'loading' ) {
    initAddCustomer();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        initAddCustomer();
    });
}

function initAddCustomer() {
    /* https://stackoverflow.com/questions/17651207/mask-us-phone-number-string-with-javascript */
    /*document.getElementById('telmobil').addEventListener('input', function (e) {
        var x = e.target.value.replace(/\D/g, '').match(/(\d{0,4})(\d{0,})/);
        e.target.value = !x[2] ? x[1] : x[1] + ' ' + x[2];
    });*/

    globalProperties.company = document.getElementById("showCompanies");
    globalProperties.person = document.getElementById("showPersons");
    globalProperties.current = globalProperties.company;

    globalProperties.companyForm = document.getElementById("companyForm");
    globalProperties.privateForm = document.getElementById("privateForm");

    globalProperties.company.addEventListener("click", function () {
        if (globalProperties.current == globalProperties.company) {
            return;
        }

        globalProperties.companyForm.classList.remove("hidden");
        globalProperties.companyForm.classList.add("grid");
        globalProperties.privateForm.classList.add("hidden");

        toggleStyles(globalProperties.person);
        toggleStyles(globalProperties.company);

        globalProperties.current = globalProperties.company;
    });

    globalProperties.person.addEventListener("click", function () {
        if (globalProperties.current == globalProperties.person) {
            return;
        }

        globalProperties.privateForm.classList.remove("hidden");
        globalProperties.privateForm.classList.add("grid");
        globalProperties.companyForm.classList.add("hidden");

        toggleStyles(globalProperties.person);
        toggleStyles(globalProperties.company);

        globalProperties.current = globalProperties.person;
    });

    const addNewCustomer = document.getElementById("addNewCustomer");
    addNewCustomer.addEventListener("click", sendCustomerData);
}

function toggleStyles(element) {
    element.classList.toggle("hover:text-gray-600");
    element.classList.toggle("hover:border-gray-300");
    element.classList.toggle("dark:hover:text-gray-300");

    element.classList.toggle("text-blue-600");
    element.classList.toggle("border-blue-600");
    element.classList.toggle("dark:text-blue-500");
    element.classList.toggle("dark:border-blue-500");
}

function sendCustomerData() {
    const addNewCustomer = document.getElementById("addNewCustomer");
    addNewCustomer.disabled = true;

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

    ajax.post({
        r: "addCustomer",
        data: JSON.stringify(object),
    }).then(r => {
        if (r.status == "success") {
            location.href = r.link;
        }
    });
}
