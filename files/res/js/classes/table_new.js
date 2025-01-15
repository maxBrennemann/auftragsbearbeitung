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
}

export const createHeader = (headers, table) => {
    const thead = table.querySelector("thead");
    const row = document.createElement("tr");

    headers.forEach(header => {
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
}

export const addRow = (data, table) => {
    const tbody = table.querySelector("tbody");
    const row = document.createElement("tr");

    Object.keys(data).forEach(key => {
        const cell = document.createElement("td");
        cell.textContent = data[key];
        row.appendChild(cell);
    });

    const actionsCell = document.createElement("td");
    const editBtn = document.createElement("button");
    editBtn.textContent = "";
    editBtn.title = "Bearbeiten";
    editBtn.addEventListener("click", () => {
        dispatchActionEvent("rowEdit", data);
    });

    const deleteBtn = document.createElement("button");
    deleteBtn.textContent = "";
    deleteBtn.title = "Löschen";
    deleteBtn.addEventListener("click", () => {
        dispatchActionEvent("rowDelete", data);
    });

    actionsCell.appendChild(editBtn);
    actionsCell.appendChild(deleteBtn);
    row.appendChild(actionsCell);

    tbody.appendChild(row);
}

const addCustomAction = () => {

}

const dispatchActionEvent = (actionType, rowData, table) => {
    const event = new CustomEvent(actionType, {
        details: rowData,
        bubbles: true,
    });

    table.dispatchEvent(event);
}
