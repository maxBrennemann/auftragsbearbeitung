export const getTable = (data) => {
    const thead = createHeader(data.config, data.callback);
    const tbody = createRows(data.rows, data.config, data.link);
    const table = createTable(thead, tbody, data.tableCss);

    return table;
}

const createHeader = (config, callback) => {
    const tr = document.createElement("tr");
    config.forEach((element) => {
        const th = document.createElement("th");
        const css = element.css || [];
        th.classList.add(...css);
        th.innerText = element.title;

        if (callback) {
            th.addEventListener("click", () => {
                callback(element.name);
            });
        }

        tr.appendChild(th);
    });

    const thead = document.createElement("thead");
    thead.appendChild(tr);

    return thead;
}

const createRows = (rows, config, link) => {
    const tbody = document.createElement("tbody");
    rows.forEach((row) => {
        const tr = createRow(row, config, link);
        tbody.appendChild(tr);
    });

    return tbody;
}

const createRow = (row, config, link) => {
    const tr = document.createElement("tr");
    const rowConfig = config.map((element) => element.name);
    link = link + row.id;
    rowConfig.forEach((element) => {
        const css = config[rowConfig.indexOf(element)].css || [];
        const td = createCell(row[element], css, link);
        tr.appendChild(td);
    });

    return tr;
}

const createCell = (element, css, link) => {
    const td = document.createElement("td");
    const div = document.createElement("div");
    div.classList.add(...css);
    div.innerText = element;
    td.appendChild(div);

    if (link) {
        const a = document.createElement("a");
        a.href = link;
        a.innerText = element;
        div.innerText = "";
        div.appendChild(a);
    }

    return td;
}

const createTable = (thead, tbody, css) => {
    const table = document.createElement("table");
    table.appendChild(thead);
    table.appendChild(tbody);

    if (css) {
        table.classList.add(...css);
    }

    return table;
}
