export const createTable = (containerId) => {
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

    return table;
}

export const createHeader = (headers, table, conditions = {}) => {
    const thead = table.querySelector("thead");
    const row = document.createElement("tr");

    let count = 1;
    headers.forEach(header => {
        if (conditions?.hide?.includes(header.key)) {
            return;
        }

        count++;

        const th = document.createElement("th");
        th.textContent = header.label;
        th.dataset.key = header.key;
        th.addEventListener("click", () => sortTable(header.key));
        row.appendChild(th);
    });

    const actionsTh = document.createElement("th");
    actionsTh.textContent = "Aktionen";
    row.appendChild(actionsTh);

    thead.appendChild(row);

    createPlaceholderRow(count, table);
    createAddRow(count, table);
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

    const btn = document.createElement("button");
    btn.innerHTML = "";
    cell.appendChild(btn);

    const text = document.createElement("p");
    text.textContent = "Neuer Eintrag";

    cell.appendChild(text);
    row.appendChild(cell);
    table.querySelector("tbody").appendChild(row);
}

export const addRow = (data, table, conditions = {}) => {
    const tbody = table.querySelector("tbody");
    const row = document.createElement("tr");

    const placeholderRow = table.querySelector("tr.empty-placeholder");
    if (placeholderRow) {
        tbody.removeChild(placeholderRow);
    }

    Object.keys(data).forEach(key => {
        if (conditions?.hide?.includes(key)) {
            return;
        }

        const cell = document.createElement("td");
        cell.textContent = data[key];
        row.appendChild(cell);
    });

    const actionsCell = document.createElement("td");
    if (!conditions?.hideOptions?.includes("edit")) {
        const editBtn = document.createElement("button");
        editBtn.innerHTML = getEditBtn();
        editBtn.title = "Bearbeiten";
        editBtn.className = "inline-flex border-0 bg-green-400 p-1 rounded-md";
        editBtn.addEventListener("click", () => {
            dispatchActionEvent("rowEdit", data, table);
        });

        actionsCell.appendChild(editBtn);
    }

    if (!conditions?.hideOptions?.includes("delete")) {
        const deleteBtn = document.createElement("button");
        deleteBtn.innerHTML = getDeleteBtn();
        deleteBtn.title = "Löschen";
        deleteBtn.className = "inline-flex border-0 bg-red-400 p-1 rounded-md ml-1";
        deleteBtn.addEventListener("click", () => {
            dispatchActionEvent("rowDelete", data, table);
        });

        actionsCell.appendChild(deleteBtn);
    }

    row.appendChild(actionsCell);

    tbody.appendChild(row);
}

const dispatchActionEvent = (actionType, rowData, table) => {
    const event = new CustomEvent(actionType, {
        detail: rowData,
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
