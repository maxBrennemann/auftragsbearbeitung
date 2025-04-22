import { tableConfig } from "../tableconfig.js";
import { ajax } from "./ajax.js";
import { loadFromLocalStorage, saveToLocalStorage } from "../global.js";

export const renderTable = (containerId, header, data, options = {}) => {
    const table = createTable(containerId, options);
    createHeader(header, table, options);
    data.forEach(row => {
        addRow(row, table, options, header);
    });

    autoSortTable(table, options);
    if ('sum' in options) {
        createSumRow(data, table, options, header);
    }

    return table;
}

const autoSortTable = (table, options) => {
    if (!options?.autoSort) {
        return;
    }

    const sort = loadFromLocalStorage(table.parentNode.id);
    const th = table.querySelector(`th[data-key="${sort?.orderBy}"]`);
    if (th == null) {
        return;
    }

    const sorter = th.querySelector(".sorter");
    if (sort?.orderBy === th.dataset.key) {
        th.dataset.direction = sort.order;
        sorter.innerHTML = sort.order === "asc" ? getSortAsc() : getSortDesc();
    }

    sortTable(table, th, sorter, options);
}

export const fetchAndRenderTable = async (containerId, tableName, options = {}) => {
    const config = tableConfig[tableName];
    if (config == null) {
        return;
    }

    const conditions = options?.conditions ?? {}
    const data = await ajax.get(`/api/v1/tables/${tableName}`, {
        "conditions": JSON.stringify(conditions),
    });

    if (!options?.primaryKey) {
        options.primaryKey = config.primaryKey;
    }

    return renderTable(containerId, config.columns, data, options);
}

export const createTable = (containerId, options = {}) => {
    const container = document.getElementById(containerId);
    if (container == null) {
        return;
    }

    const table = document.createElement("table");
    const thead = document.createElement("thead");
    const tbody = document.createElement("tbody");

    table.appendChild(thead);
    table.appendChild(tbody);
    container.appendChild(table);

    if (options?.styles?.table?.className) {
        const className = options.styles.table.className.join(" ");
        table.className = className;
    }

    return table;
}

export const createHeader = (header, table, options = {}) => {
    const thead = table.querySelector("thead");
    const row = document.createElement("tr");

    let count = 0;
    header.forEach(header => {
        if (options?.hide?.includes(header.key)) {
            return;
        }
        count++;

        let className = "cursor-pointer";
        if (options?.styles?.thead?.className) {
            className += " " + options.styles.thead.className.join(" ");
        }

        const th = document.createElement("th");
        th.className = className;
        th.dataset.key = header.key;
        th.dataset.sort = "none";

        const innerSpan = document.createElement("span");
        innerSpan.textContent = header.label;
        innerSpan.className = "inline-flex items-center";
        th.appendChild(innerSpan);

        const sorter = document.createElement("span");
        sorter.className = "inline-flex ml-1 sorter";
        sorter.innerHTML = getSortNone();
        innerSpan.appendChild(sorter);

        th.addEventListener("click", () => sortTable(table, th, sorter, options));

        row.appendChild(th);
    });

    if (!options?.hideOptions?.includes("all")) {
        const actionsTh = document.createElement("th");
        actionsTh.textContent = "Aktionen";
        row.appendChild(actionsTh);
        count++;
    }

    thead.appendChild(row);

    createPlaceholderRow(count, table);

    if (!options?.hideOptions?.includes("addRow")
        && !options?.hideOptions?.includes("all")) {
        createAddRow(count, header, table, options);
    }
}

const createPlaceholderRow = (count, table) => {
    const row = document.createElement("tr");
    row.className = "empty-placeholder";
    const cell = document.createElement("td");
    cell.setAttribute("colspan", count);
    cell.style.textAlign = "center";
    cell.textContent = "Keine Daten verfügbar.";
    row.appendChild(cell);
    table.querySelector("tbody").appendChild(row);
}

const createAddRow = (count, header, table, options = {}) => {
    const row = document.createElement("tr");
    const cell = document.createElement("td");
    cell.setAttribute("colspan", count);
    cell.style.textAlign = "center";

    const container = document.createElement("div");
    container.className = "inline-flex cursor-pointer";
    container.title = "Neuen Eintrag hinzufügen";

    const btn = document.createElement("button");
    btn.dataset.status = "add";
    btn.className = "border-0 inline-flex items-center";
    const btnSpan = document.createElement("span");
    btnSpan.innerHTML = getAddBtn();
    btnSpan.className = "inline-flex border-0 bg-green-400 p-1 rounded-md";
    btn.appendChild(btnSpan);

    const text = document.createElement("span");
    text.textContent = "Neuer Eintrag";
    text.className = "ml-1";
    btn.appendChild(text);

    btn.addEventListener("click", () => {
        switch (btn.dataset.status) {
            case "add":
                btnSpan.innerHTML = getSaveBtn();
                text.textContent = "Speichern";
                addEditableRow(header, table, options);
                btn.dataset.status = "save";
                break;
            case "save":
                btnSpan.innerHTML = getAddBtn();
                text.textContent = "Neuer Eintrag";
                dispatchActionEvent("rowAdd", [], table);
                btn.dataset.status = "add";
                break;
        }
    });

    container.appendChild(btn);
    cell.appendChild(container);
    row.appendChild(cell);
    table.querySelector("tbody").appendChild(row);
}

const createSumRow = (data, table, options = {}, header = {}) => {
    const tfoot = document.createElement("tfoot");
    const tr = document.createElement("tr");
    //tr.className = "bg-blue-700";
    const sumUp = options.sum ?? [];
    const results = {};

    data.forEach(row => {
        sumUp.forEach(el => {
            let value = row[el.key];
            value = value.replace(",", ".");
            value = value.trim();

            if (results[el.key] == undefined) {
                results[el.key] = 0;
            }

            const result = parseFloat(value);
            if (!isNaN(result)) {
                results[el.key] += result;
            }
        });
    });

    header.forEach(el => {
        const td = document.createElement("td");
        //td.className = "bg-blue-700";

        let sumBy = sumUp.find(val => val.key === el.key);
        if (sumBy) {
            if (sumBy.format) {
                td.innerHTML = format(results[el.key] ?? 0, sumBy.format);
            } else {
                td.innerHTML = results[el.key] ?? "";
            }
        }

        tr.appendChild(td);
    });

    tfoot.appendChild(tr);
    table.appendChild(tfoot);
}

export const addRow = (data, table, options = {}, header = {}) => {
    const tbody = table.querySelector("tbody");
    const row = document.createElement("tr");
    clearTable(table);

    if (Object.keys(data).includes(options?.primaryKey)) {
        row.dataset.id = data[options?.primaryKey];
    }

    const orderedKeys = header.length > 0 ? header.map(h => h.key) : Object.keys(data);
    orderedKeys.forEach(key => {
        if (options?.hide?.includes(key)) {
            return;
        }

        const cssClasses = options?.styles?.key?.[key] ?? [];
        const cell = document.createElement("td");
        if (options?.link) {
            cssClasses.push("cursor-pointer");
            cell.innerHTML = `<a href="${options.link}${data[options.primaryKey]}">${data[key]}</a>`;
            row.appendChild(cell);
            return;
        }

        cell.className = cssClasses.join(" ");
        cell.innerHTML = data[key];
        row.appendChild(cell);
    });

    const actionsCell = document.createElement("td");

    addEditBtn(data, table, row, actionsCell, options);
    addDeleteBtn(data, table, row, actionsCell, options);
    addCheckBtn(data, table, row, actionsCell, options);
    addMoveBtn(data, table, row, actionsCell, options);
    addAddBtn(data, table, row, actionsCell, options);

    if (!options?.hideOptions?.includes("all")) {
        row.appendChild(actionsCell);
    }

    dispatchActionEvent("rowAdd", data, table, { row });
    tbody.appendChild(row);
}

const addEditBtn = (data, table, row, actionsCell, options) => {
    if (!options?.hideOptions?.includes("edit")
        && !options?.hideOptions?.includes("all")) {
        const editBtn = document.createElement("button");
        editBtn.innerHTML = getEditBtn();
        editBtn.title = "Bearbeiten";
        editBtn.className = "inline-flex border-0 bg-green-400 p-1 rounded-md";
        editBtn.addEventListener("click", () => {
            dispatchActionEvent("rowEdit", data, table, { row });
        });

        actionsCell.appendChild(editBtn);
    }
}

const addDeleteBtn = (data, table, row, actionsCell, options) => {
    if (!options?.hideOptions?.includes("delete")
        && !options?.hideOptions?.includes("all")) {
        const deleteBtn = document.createElement("button");
        deleteBtn.innerHTML = getDeleteBtn();
        deleteBtn.title = "Löschen";
        deleteBtn.className = "inline-flex border-0 bg-red-400 p-1 rounded-md ml-1";
        deleteBtn.addEventListener("click", () => {
            dispatchActionEvent("rowDelete", data, table, { row });
        });

        actionsCell.appendChild(deleteBtn);
    }
}

const addCheckBtn = (data, table, row, actionsCell, options) => {
    if (!options?.hideOptions?.includes("check")
        && !options?.hideOptions?.includes("all")) {
        const checkBtn = document.createElement("button");
        checkBtn.innerHTML = getCheckBtn();
        checkBtn.title = "Erledigt";
        checkBtn.className = "inline-flex border-0 bg-blue-400 p-1 rounded-md ml-1";
        checkBtn.addEventListener("click", () => {
            dispatchActionEvent("rowCheck", data, table, { row });
        });

        actionsCell.appendChild(checkBtn);
    }
}

const addMoveBtn = (data, table, row, actionsCell, options) => {
    if (!options?.hideOptions?.includes("move")
        && !options?.hideOptions?.includes("all")) {
        const checkBtn = document.createElement("button");
        checkBtn.innerHTML = getMoveBtn();
        checkBtn.title = "Bewegen";
        checkBtn.className = "inline-flex border-0 bg-zinc-400 p-1 rounded-md ml-1";
        checkBtn.addEventListener("click", () => {
            dispatchActionEvent("rowMove", data, table, { row });
        });

        actionsCell.appendChild(checkBtn);
    }
}

const addAddBtn = (data, table, row, actionsCell, options) => {
    if (!options?.hideOptions?.includes("add")
        && !options?.hideOptions?.includes("all")) {
        const checkBtn = document.createElement("button");
        checkBtn.innerHTML = getAddBtn();
        checkBtn.title = "Hinzufügen";
        checkBtn.className = "inline-flex border-0 bg-yellow-400 p-1 rounded-md ml-1";
        checkBtn.addEventListener("click", () => {
            dispatchActionEvent("rowAdd", data, table, { row });
        });

        actionsCell.appendChild(checkBtn);
    }
}

export const clearRows = (table) => {
    const tbody = table.querySelector("tbody");
    const rows = tbody.querySelectorAll("tr");
    Array.from(rows).forEach(row => {
        row.remove();
    });
}

const addEditableRow = (headers, table, options = {}) => {
    const tbody = table.querySelector("tbody");
    const lastRowAnchor = tbody.lastChild;

    if (lastRowAnchor == null) {
        return;
    }

    const tr = document.createElement("tr");
    tr.className = "editable-row";
    headers.forEach(header => {
        const key = header.key;
        const td = document.createElement("td");

        if (key !== options?.primaryKey) {
            td.contentEditable = true;
        }

        td.dataset.key = key;
        tr.appendChild(td);
    });

    if (!options?.hideOptions?.includes("all")) {
        const td = document.createElement("td");
        tr.appendChild(td);
    }

    tbody.appendChild(tr);
}

const dispatchActionEvent = (actionType, rowData, table, options = {}) => {
    const event = new CustomEvent(actionType, {
        detail: { ...rowData, ...options },
        bubbles: true,
    });

    table.dispatchEvent(event);
}

const sortTable = (table, th, sorter, options) => {
    const skipFirstRow = !options?.hideOptions?.includes("addRow")
    && !options?.hideOptions?.includes("all");

    const index = Array.from(th.parentNode.children).indexOf(th);
    const rows = table.querySelectorAll(skipFirstRow ? "tbody tr:nth-child(n+2)" : "tbody tr");
    const tbody = table.querySelector("tbody");
    
    let sort = getSortDirection(th, sorter);

    Array.from(rows)
        .sort(comparer(index, sort, options?.link? false : true))
        .forEach(tr => {
            tbody.appendChild(tr);
        });

    resetTableHeaders(table, th);
    const containerId = table.parentNode.id;
    const tableOrder = {
        orderBy: th.dataset.key,
        order: th.dataset.direction,
    };
    saveToLocalStorage(containerId, tableOrder);
}

const getSortDirection = (th, sorter) => {
    let sort = th.dataset.direction;
    if (sort == "asc") {
        th.dataset.direction = "desc";
        sorter.innerHTML = getSortAsc();
        sort = false;
    } else {
        th.dataset.direction = "asc";
        sorter.innerHTML = getSortDesc();
        sort = true;
    }

    th.classList.add("bg-sky-800");
    return sort;
}

const resetTableHeaders = (table, th) => {
    const thead = table.querySelector("thead");
    const ths = thead.querySelectorAll("th");
    ths.forEach(currentTh => {
        if (currentTh === th) {
            return;
        }
        currentTh.dataset.direction = "";
        const sorter = currentTh.querySelector(".sorter") ?? null;
        if (sorter == null) {
            return;
        }
        currentTh.classList.remove("bg-sky-800");
        sorter.innerHTML = getSortNone();
    });
}

const getCellValue = (tr, idx, isLink) => {
    if (isLink) {
        const a = tr.children[idx].querySelector("a");
        if (a != null) {
            return a.innerText || a.textContent;
        }
    }
    return tr.children[idx].innerText || tr.children[idx].textContent;
}

const comparer = (idx, asc, isLink) => (a, b) => ((v1, v2) =>
    v1 !== '' && v2 !== '' && !isNaN(v1) && !isNaN(v2) ? v1 - v2 : v1.toString().localeCompare(v2))(getCellValue(asc ? a : b, idx, isLink), getCellValue(asc ? b : a, idx, isLink));

const clearTable = (table) => {
    const tbody = table.querySelector("tbody");

    const placeholderRow = table.querySelector("tr.empty-placeholder");
    if (placeholderRow) {
        tbody.removeChild(placeholderRow);
    }

    const editableRow = table.querySelector("tr.editable-row");
    if (editableRow) {
        tbody.removeChild(editableRow);
    }
}

const format = (value, format) => {
    switch (format) {
        case "EUR":
            return new Intl.NumberFormat("de-DE", {
                "style": "currency",
                "currency": "EUR"
            }).format(value);
    }
    return value;
}

const getEditBtn = () => {
    return `
    <svg class="inline" style="width:15px;height:15px" viewBox="0 0 24 24" title="Bearbeiten">
        <path class="fill-white" d="M20.71,7.04C21.1,6.65 21.1,6 20.71,5.63L18.37,3.29C18,2.9 17.35,2.9 16.96,3.29L15.12,5.12L18.87,8.87M3,17.25V21H6.75L17.81,9.93L14.06,6.18L3,17.25Z"></path>
    </svg>`;
}

const getDeleteBtn = () => {
    return `
    <svg class="inline" style="width:15px;height:15px" viewBox="0 0 24 24" title="Löschen">
        <path class="fill-white" d="M19,4H15.5L14.5,3H9.5L8.5,4H5V6H19M6,19A2,2 0 0,0 8,21H16A2,2 0 0,0 18,19V7H6V19Z"></path>
    </svg>`;
}

const getAddBtn = () => {
    return `
    <svg class="inline" style="width:15px;height:15px" viewBox="0 0 24 24" title="Löschen">
        <path class="fill-white" d="M20 14H14V20H10V14H4V10H10V4H14V10H20V14Z" />
    </svg>`;
}

const getCheckBtn = () => {
    return `
    <svg class="inline" style="width:15px;height:15px" viewBox="0 0 24 24" title="Check">
        <path class="fill-white" d="M9,20.42L2.79,14.21L5.62,11.38L9,14.77L18.88,4.88L21.71,7.71L9,20.42Z" />
    </svg>`;
}

const getSaveBtn = () => {
    return `
    <svg class="inline" style="width:15px;height:15px" viewBox="0 0 24 24" title="Speichern">
        <path class="fill-white" d="M15,9H5V5H15M12,19A3,3 0 0,1 9,16A3,3 0 0,1 12,13A3,3 0 0,1 15,16A3,3 0 0,1 12,19M17,3H5C3.89,3 3,3.9 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V7L17,3Z" />
    </svg>`;
}

const getMoveBtn = () => {
    return `
     <svg class="inline" style="width:15px;height:15px" viewBox="0 0 24 24" title="Bewegen">
        <path class="fill-white" d="M13,6V11H18V7.75L22.25,12L18,16.25V13H13V18H16.25L12,22.25L7.75,18H11V13H6V16.25L1.75,12L6,7.75V11H11V6H7.75L12,1.75L16.25,6H13Z" />
    </svg>`;
}

const getSortAsc = () => {
    return `
    <svg class="inline" style="width:12px;height:12px" viewBox="0 0 24 24" title="Aufsteigend sortieren">
        <path d="M19 17H22L18 21L14 17H17V3H19M2 17H12V19H2M6 5V7H2V5M2 11H9V13H2V11Z" fill="white" />
    </svg>`;
}

const getSortDesc = () => {
    return `
    <svg class="inline" style="width:12px;height:12px" viewBox="0 0 24 24" title="Absteigend sortieren">
        <path d="M19 7H22L18 3L14 7H17V21H19M2 17H12V19H2M6 5V7H2V5M2 11H9V13H2V11Z" fill="white" />
    </svg>`;
}

const getSortNone = () => {
    return `
    <svg class="inline" style="width:12px;height:12px" viewBox="0 0 24 24" title="Unsortiert">
        <path fill="currentColor" d="M18 21L14 17H17V7H14L18 3L22 7H19V17H22M2 19V17H12V19M2 13V11H9V13M2 7V5H6V7H2Z" fill="white" />
    </svg>`;
}
