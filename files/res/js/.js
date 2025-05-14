function ajaxSearch(query) {
    const customerOverview = document.getElementById("kundenLink").dataset.customerOverview;
    const customer = document.getElementById("kundenLink").dataset.customer;

    const link = document.getElementById("kundenLink");

    if (isNaN(query)) {
        link.href = customerOverview + '?query=' + query;
    } else {
        link.href = customer + '?id=' + query;
    }

    link.click();
}

function initInputs() {
    var kundeninput = document.getElementById("kundeninput");
    var rechnungsinput = document.getElementById("rechnungsinput");
    var auftragsinput = document.getElementById("auftragsinput");
    
    kundeninput.addEventListener("keyup", function (event) {
        if (event.key !== "Enter") {
            return;
        }

        if (event.target.value === "" || event.target.value.length == 0) {
            var link = document.getElementById('kundenLink');
            link.href = link.dataset.url + "?showDetails=list";
            link.click();
            return;
        }

        ajaxSearch(event.target.value);
    });
    
    rechnungsinput.addEventListener("keyup", function (event) {
        if (event.key === "Enter") {
            document.getElementById("rechnungsLink").click();
        }
    });
    
    auftragsinput.addEventListener("keyup", function (event) {
        if (event.key !== "Enter") {
            return;
        }

        const query = event.target.value;

        const orderOverview = document.getElementById("auftragsLink").dataset.orderOverview;
        const order = document.getElementById("auftragsLink").dataset.order;

        const link = document.getElementById("auftragsLink");

        if (isNaN(query)) {
            link.href = orderOverview + '?query=' + query;
        } else {
            link.href = order + '?id=' + query;
        }

        link.click();
    });
}

if (document.readyState !== 'loading' ) {
	initInputs();
} else {
    document.addEventListener('DOMContentLoaded', function () {
		initInputs();
    });
}
