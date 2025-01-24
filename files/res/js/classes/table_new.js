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
        createAddRow(count, table);
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

const createAddRow = (count, table) => {
    const row = document.createElement("tr");
    const cell = document.createElement("td");
    cell.setAttribute("colspan", count);
    cell.style.textAlign = "center";
    
    const container = document.createElement("div");
    container.className = "inline-flex cursor-pointer";
    container.title = "Neuen Eintrag hinzufügen";

    const btn = document.createElement("button");
    btn.innerHTML = getAddBtn();
    btn.className = "inline-flex border-0 bg-green-400 p-1 rounded-md";
    btn.addEventListener("click", () => {
        addEditableRow(table);
    });
    container.appendChild(btn);

    const text = document.createElement("p");
    text.textContent = "Neuer Eintrag";
    text.className = "ml-1";

    container.appendChild(text);
    cell.appendChild(container);
    row.appendChild(cell);
    table.querySelector("tbody").appendChild(row);
}

export const addRow = (data, table, options = {}) => {
    const tbody = table.querySelector("tbody");
    const row = document.createElement("tr");

    if (Object.keys(data).includes(options?.primaryKey)) {
        row.dataset.id = data[options?.primaryKey];
    }

    const placeholderRow = table.querySelector("tr.empty-placeholder");
    if (placeholderRow) {
        tbody.removeChild(placeholderRow);
    }

    Object.keys(data).forEach(key => {
        if (options?.hide?.includes(key)) {
            return;
        }

        const cell = document.createElement("td");
        cell.textContent = data[key];

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

export const deleteRow = (data, table) => {

}

const addEditableRow = (table) => {
    const tbody = table.querySelector("tbody");
    const lastRowAnchor = tbody.lastChild;

    if (lastRowAnchor == null) {
        return;
    }
}

const dispatchActionEvent = (actionType, rowData, table, options = {}) => {
    const event = new CustomEvent(actionType, {
        detail: { ...rowData, ...options },
        bubbles: true,
    });

    table.dispatchEvent(event);
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
    <svg class="inline" style="width:15px;height:15px" viewBox="0 0 24 24" title="Löschen">
        <path class="fill-white" d="M9,20.42L2.79,14.21L5.62,11.38L9,14.77L18.88,4.88L21.71,7.71L9,20.42Z" />
    </svg>`;
}
