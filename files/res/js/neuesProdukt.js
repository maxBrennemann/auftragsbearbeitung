/**
 * @file neuesProdukt.js
 * @description This file is used to handle the creation of a new product.
 * 
 * https://stackoverflow.com/questions/24923469/modeling-product-variants
 * https://dba.stackexchange.com/questions/123467/schema-design-for-products-with-multiple-variants-attributes?newreg=9504cc9890d1461ea745070f28f70543
 * https://stackoverflow.com/questions/19144200/designing-a-sql-schema-for-a-combination-of-many-to-many-relationship-variation
 */

import { ajax } from "./classes/ajax.js"; 

function init() {
    const save = document.getElementById("save");
    save.addEventListener("click", saveProduct);

    const abort = document.getElementById("abort");
    abort.addEventListener("click", function() {
        window.location.href = "index.php";
    });

    document.getElementById("source").addEventListener("change", function(event) {
        if (event.target.value == "addNew") {
            const el = document.getElementById("addSource");
            el.classList.remove("hidden");
        }
    });

    document.getElementById("saveSource").addEventListener("click", function() {
        sendSource();
    });

    document.getElementById("abortSource").addEventListener("click", function() {
        const el = document.getElementById("addSource");
        el.classList.add("hidden");

        document.getElementById("source").value = -1;
    });
}

if (document.readyState !== 'loading' ) {
    init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        init();
    });
}

function sendSource() {
    var name = document.getElementById("getName").value;
    var desc = document.getElementById("getDesc").value;

    ajax.post("/api/v1/product/source", {
        name: name,
        desc: desc
    }).then(() => {
        
    }).catch((error) => {
        console.error(error);
    });
}

function saveProduct() {
    const title = document.getElementById("productName").value;
    const brand = document.getElementById("productBrand").value;
    const category = document.getElementById("category").value;
    const source = document.getElementById("source").value;
    const price = document.getElementById("productPrice").value;
    const purchasePrice = document.getElementById("purchasingPrice").value;
    const description = document.getElementById("productDescription").value;
    //const attributes = document.getElementById("productAttributes").value || [];

    /* check if all required fields are filled */
    if (title == "" || brand == "" || source == "" || price == "" || purchasePrice == "" || description == "" || category == "") {
        return;
    }

    ajax.post("/api/v1/product", {
        title: title,
        brand: brand,
        category: category,
        source: source,
        price: price.replace(",", "."),
        purchasePrice: purchasePrice.replace(",", "."),
        description: description,
        //attributes: attributes
    }).then(data => {
        const id = data.id;
        window.location.href = `produkt?id=${id}`;
    });
}
