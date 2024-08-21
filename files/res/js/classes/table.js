export const getTable = (data) => {
    const thead = createHeader(data.config);
    const tbody = createRows(data.rows, data.config);
    const table = createTable(thead, tbody, data.tableCss);

    return table;
}

const createHeader = (config) => {
    const tr = document.createElement("tr");
    config.forEach((element) => {
        const th = document.createElement("th");
        const css = element.css || [];
        th.classList.add(...css);
        th.innerText = element.title;
        tr.appendChild(th);
    });

    const thead = document.createElement("thead");
    thead.appendChild(tr);

    return thead;
}

const createRows = (rows, config) => {
    const tbody = document.createElement("tbody");
    rows.forEach((row) => {
        const tr = createRow(row, config);
        tbody.appendChild(tr);
    });

    return tbody;
}

const createRow = (row, config) => {
    const tr = document.createElement("tr");
    const rowConfig = config.map((element) => element.name);
    rowConfig.forEach((element) => {
        const css = config[rowConfig.indexOf(element)].css || [];
        const td = createCell(row[element], css);
        tr.appendChild(td);
    });

    return tr;
}

const createCell = (element, css) => {
    const td = document.createElement("td");
    const div = document.createElement("div");
    div.classList.add(...css);
    div.innerText = element;
    td.appendChild(div);

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
