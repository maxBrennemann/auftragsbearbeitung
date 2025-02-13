import { tableConfig } from "../tableconfig.js";
import { ajax } from "./ajax.js";

export const renderTable = (containerId, headers, data, options = {}) => {
    const table = createTable(containerId, options);
    createHeader(headers, table, options);
    data.forEach(row => {
        addRow(row, table, options);
    });

    return table;
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
        table.className = options.styles.table.className;
    }

    return table;
}

export const createHeader = (headers, table, options = {}) => {
    const thead = table.querySelector("thead");
    const row = document.createElement("tr");

    let count = 0;
    headers.forEach(header => {
        if (options?.hide?.includes(header.key)) {
            return;
        }
        count++;

        const th = document.createElement("th");
        th.textContent = header.label;
        th.dataset.key = header.key;
        th.addEventListener("click", () => sortTable(header.key));
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
        createAddRow(count, headers, table, options);
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

const createAddRow = (count, headers, table, options = {}) => {
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
                addEditableRow(headers, table, options);
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

export const addRow = (data, table, options = {}) => {
    const tbody = table.querySelector("tbody");
    const row = document.createElement("tr");

    clearTable(table);

    if (Object.keys(data).includes(options?.primaryKey)) {
        row.dataset.id = data[options?.primaryKey];
    }

    Object.keys(data).forEach(key => {
        if (options?.hide?.includes(key)) {
            return;
        }

        const cell = document.createElement("td");
        cell.innerHTML = data[key];

        row.appendChild(cell);
    });

    const actionsCell = document.createElement("td");

    if (!options?.hideOptions?.includes("edit")
        && !options?.hideOptions?.includes("all")) {
        const editBtn = document.createElement("button");
        editBtn.innerHTML = getEditBtn();
        editBtn.title = "Bearbeiten";
        editBtn.className = "inline-flex border-0 bg-green-400 p-1 rounded-md";
        editBtn.addEventListener("click", () => {
            dispatchActionEvent("rowEdit", data, table, {row});
        });

        actionsCell.appendChild(editBtn);
    }

    if (!options?.hideOptions?.includes("delete")
        && !options?.hideOptions?.includes("all")) {
        const deleteBtn = document.createElement("button");
        deleteBtn.innerHTML = getDeleteBtn();
        deleteBtn.title = "Löschen";
        deleteBtn.className = "inline-flex border-0 bg-red-400 p-1 rounded-md ml-1";
        deleteBtn.addEventListener("click", () => {
            dispatchActionEvent("rowDelete", data, table, {row});
        });

        actionsCell.appendChild(deleteBtn);
    }

    if (!options?.hideOptions?.includes("check")
        && !options?.hideOptions?.includes("all")) {
        const checkBtn = document.createElement("button");
        checkBtn.innerHTML = getCheckBtn();
        checkBtn.title = "Erledigt";
        checkBtn.className = "inline-flex border-0 bg-blue-400 p-1 rounded-md ml-1";
        checkBtn.addEventListener("click", () => {
            dispatchActionEvent("rowCheck", data, table, {row});
        });

        actionsCell.appendChild(checkBtn);
    }

    if (!options?.hideOptions?.includes("all")) {
        row.appendChild(actionsCell);
    }

    tbody.appendChild(row);
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
