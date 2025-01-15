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

export const addRow = (data, table, conditions = {}) => {
    const tbody = table.querySelector("tbody");
    const row = document.createElement("tr");

    Object.keys(data).forEach(key => {
        if (conditions.hide.includes(key)) {
            return;
        }

        const cell = document.createElement("td");
        cell.textContent = data[key];
        row.appendChild(cell);
    });

    const actionsCell = document.createElement("td");
    const editBtn = document.createElement("button");
    editBtn.innerHTML = getEditBtn();
    editBtn.title = "Bearbeiten";
    editBtn.className = "inline-flex border-0 bg-green-400 p-1 rounded-md";
    editBtn.addEventListener("click", () => {
        dispatchActionEvent("rowEdit", data, table);
    });

    const deleteBtn = document.createElement("button");
    deleteBtn.innerHTML = getDeleteBtn();
    deleteBtn.title = "Löschen";
    deleteBtn.className = "inline-flex border-0 bg-red-400 p-1 rounded-md ml-1";
    deleteBtn.addEventListener("click", () => {
        dispatchActionEvent("rowDelete", data, table);
    });

    actionsCell.appendChild(editBtn);
    actionsCell.appendChild(deleteBtn);
    row.appendChild(actionsCell);

    tbody.appendChild(row);
}

const addCustomAction = () => {

}

const dispatchActionEvent = (actionType, rowData, table) => {
    console.log(rowData);
    const event = new CustomEvent(actionType, {
        details: rowData,
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
