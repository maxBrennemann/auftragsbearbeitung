//@ts-nocheck

import { ajax } from "js-classes/ajax";
import { ArrowDownWideNarrow, ArrowUpDown, ArrowUpNarrowWide, Check, GripVertical, Pencil, Plus, Save, Trash2 } from "lucide";

import { loadFromLocalStorage, saveToLocalStorage } from "../global";

import { iconHtml } from "./icon";
import { tableConfig } from "./tableconfig";

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
        th.dataset.direction = sort.order == "asc" ? "desc" : "asc";
    }

    sortTable(table, th, sorter, options);
}

export const fetchAndRenderTable = async (containerId, tableName, options = {}) => {
    const config = tableConfig[tableName];
    if (config == null) {
        console.warn(`No table config found for ${tableName}`);
        return;
    }

    const conditions = options?.conditions ?? {}
    const response = await ajax.get(`/api/v1/tables/${tableName}`, {
        "conditions": JSON.stringify(conditions),
        ...options.joins
    });
    const data = response.data;

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
        const cssClasses = options.styles.table.className;
        cssClasses.push("shadow-sm");
        const className = cssClasses.join(" ");
        table.className = className;
    }

    return table;
}

export const createHeader = (header, table, options = {}) => {
    const thead = table.querySelector("thead");
    const row = document.createElement("tr");

    let className = "cursor-pointer";
    if (options?.styles?.thead?.className) {
        className += " " + options.styles.thead.className.join(" ");
    }

    let count = 0;
    header.forEach(header => {
        if (options?.hide?.includes(header.key)) {
            return;
        }
        count++;

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

        th.addEventListener("click", () => {
            const data = sortTable(table, th, sorter, options);
            saveToLocalStorage(data.containerId, data.tableOrder);
        });

        row.appendChild(th);
    });

    if (!options?.hideOptions?.includes("all")) {
        const actionsTh = document.createElement("th");
        actionsTh.className = className;
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
    row.className = "add-row";
    const cell = document.createElement("td");
    cell.setAttribute("colspan", count);
    cell.style.textAlign = "center";

    const container = document.createElement("div");
    container.className = "inline-flex cursor-pointer";
    container.title = "Neuen Eintrag hinzufügen";

    const btn = document.createElement("button");
    btn.dataset.status = "add";
    btn.className = "btn-secondary btn-sm";
    const btnSpan = document.createElement("span");
    btnSpan.innerHTML = getAddBtn();
    btnSpan.className = "inline-flex";
    btn.appendChild(btnSpan);

    const text = document.createElement("span");
    text.textContent = "Neuer Eintrag";
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
                dispatchActionEvent("rowInsert", [], table);
                btn.dataset.status = "add";
                break;
        }
    });

    container.appendChild(btn);
    cell.appendChild(container);
    row.appendChild(cell);
    table.querySelector("tbody").appendChild(row);
}

export const createSumRow = (data, table, options = {}, header = {}) => {
    const tfoot = document.createElement("tfoot");
    const tr = document.createElement("tr");
    const sumUp = options.sum ?? [];
    const results = {};

    data.forEach(row => {
        sumUp.forEach(el => {
            let value = row[el.key];
            if (typeof value === "string") {
                value = value.replace(/[^\d,.-]/g, "").trim();
                value = value.includes(",")
                    ? value.replace(/\./g, "").replace(",", ".")
                    : value;
            }

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
        if (options?.hide?.includes(el.key)) {
            return;
        }
        const td = document.createElement("td");

        let className = "";
        if (options?.styles?.sum?.[el.key]) {
            className += " " + options.styles.sum[el.key].join(" ");
        }
        td.className = className.trim();

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

    if (!options?.hideOptions?.includes("all")) {
        const td = document.createElement("td");
        tr.appendChild(td);
    }

    tfoot.appendChild(tr);
    table.appendChild(tfoot);
}

export const addRow = (data, table, options = {}, header = {}) => {
    const tbody = table.querySelector("tbody");
    const row = document.createElement("tr");

    if (Object.keys(data).length !== 0) {
        clearTable(table);
    }

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
            cell.innerHTML = `<a href="${options.link}${data[options.primaryKey]}">${data[key]}</a>`;
            const a = cell.querySelector("a");
            a.className = cssClasses.join(" ") + " cursor-pointer";
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
    addUploadBtn(data, table, row, actionsCell, options);

    if (!options?.hideOptions?.includes("all")) {
        row.appendChild(actionsCell);
    }

    dispatchActionEvent("rowAdd", data, table, { row });
    tbody.appendChild(row);
}

export const updateRow = (data, table, row, options = {}, header = {}) => {
    if (!row) return console.warn("updateRow: row not found");

    while (row.firstChild) {
        row.removeChild(row.firstChild);
    }

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
            cell.innerHTML = `<a href="${options.link}${data[options.primaryKey]}">${data[key]}</a>`;
            const a = cell.querySelector("a");
            a.className = cssClasses.join(" ") + " cursor-pointer";
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
    addUploadBtn(data, table, row, actionsCell, options);

    if (!options?.hideOptions?.includes("all")) {
        row.appendChild(actionsCell);
    }

    dispatchActionEvent("rowUpdate", data, table, { row });
}

const addEditBtn = (data, table, row, actionsCell, options) => {
    if (!options?.hideOptions?.includes("edit")
        && !options?.hideOptions?.includes("all")) {
        const editBtn = document.createElement("button");
        editBtn.innerHTML = getEditBtn();
        editBtn.title = "Bearbeiten";
        editBtn.className = "btn-ghost btn-icon btn-sm";
        editBtn.dataset.status = "edit";
        editBtn.addEventListener("click", () => {
            dispatchActionEvent("rowEdit", data, table, { row });

            switch (editBtn.dataset.status) {
                case "edit":
                    editBtn.innerHTML = getSaveBtn();
                    editBtn.dataset.status = "save";
                    break;
                case "save":
                    editBtn.innerHTML = getEditBtn();
                    editBtn.dataset.status = "edit";
                    break;
            }
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
        deleteBtn.className = "btn-ghost btn-icon btn-sm ml-1 text-danger";
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
        checkBtn.className = "btn-ghost btn-icon btn-sm ml-1 text-ok";
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
        checkBtn.className = "btn-ghost btn-icon btn-sm ml-1 drag-handle cursor-grab";
        //checkBtn.draggable = false;
        checkBtn.addEventListener("click", () => {
            dispatchActionEvent("rowMove", data, table, { row });
        });

        actionsCell.appendChild(checkBtn);
    }
}

const addUploadBtn = (data, table, row, actionsCell, options) => {
    if (!options?.hideOptions?.includes("add")
        && !options?.hideOptions?.includes("all")) {
        const checkBtn = document.createElement("button");
        checkBtn.innerHTML = getAddBtn();
        checkBtn.title = "Hinzufügen";
        checkBtn.className = "btn-ghost btn-icon btn-sm ml-1";
        checkBtn.addEventListener("click", () => {
            dispatchActionEvent("rowUpload", data, table, { row });
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

    createPlaceholderRow(Object.keys(table.querySelector("thead tr").children).length, table);
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

        if (options?.hide?.includes(key)) {
            return;
        }

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

    const sort = getSortDirection(th, sorter);

    Array.from(rows)
        .sort(comparer(index, sort, options?.link ? false : true))
        .forEach(tr => {
            tbody.appendChild(tr);
        });

    resetTableHeaders(table, th);
    const containerId = table.parentNode.id;
    const tableOrder = {
        orderBy: th.dataset.key,
        order: th.dataset.direction,
    };

    return {
        "containerId": containerId,
        "tableOrder": tableOrder,
    };
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

    th.classList.add("is-sorted");
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
        currentTh.classList.remove("is-sorted");
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

const getEditBtn = () => iconHtml(Pencil, { title: "Bearbeiten" });

const getDeleteBtn = () => iconHtml(Trash2, { title: "Löschen" });

const getAddBtn = () => iconHtml(Plus, { title: "Hinzufügen" });

const getCheckBtn = () => iconHtml(Check, { title: "Erledigt" });

const getSaveBtn = () => iconHtml(Save, { title: "Speichern" });

const getMoveBtn = () => iconHtml(GripVertical, { title: "Verschieben" });

const getSortAsc = () => iconHtml(ArrowUpNarrowWide, { size: 12, title: "Aufsteigend sortiert" });

const getSortDesc = () => iconHtml(ArrowDownWideNarrow, { size: 12, title: "Absteigend sortiert" });

const getSortNone = () => iconHtml(ArrowUpDown, { size: 12, className: "opacity-40", title: "Unsortiert" });
