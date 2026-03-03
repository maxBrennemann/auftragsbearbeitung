import { addBindings } from "js-classes/bindings"
import { ajax } from "js-classes/ajax";
import { FunctionMap, TableOptions } from "../types/types";
import { loader } from "../classes/helpers";
import { addRow, fetchAndRenderTable } from "../classes/table";

const fnNames: FunctionMap = {};

loader(async () => {
    addBindings(fnNames);
    const options: TableOptions = {
        primaryKey: "Nummer",
        hide: [],
        hideOptions: ["delete", "move", "check", "add"],
        styles: {
            table: {
                className: ["w-full"],
            },
        },
        autoSort: false,
    };
    const table = await fetchAndRenderTable("serviceCont", "leistung", options);

    table?.addEventListener("rowInsert", () => addService(table, options));
    table?.addEventListener("rowEdit", (e: Event) => editService(e as CustomEvent, table, options));
});

const addService = async (table: HTMLTableElement, options: TableOptions) => {
    const lastRow = table.querySelector("tbody")?.lastElementChild;
    const data = {} as Record<string, string>;

    if (lastRow == null) return;

    Array.from(lastRow.children).forEach(cell => {
        const key = cell.getAttribute("data-key");
        if (key == null) return;

        data[key] = cell.innerHTML;
    });

    const response = await ajax.post(`/api/v1/tables/leistung`, {
        "conditions": JSON.stringify(data),
    });

    data["Nummer"] = response.data["Nummer"];

    addRow(data, table, options);
}

const editService = async (e: CustomEvent, table: HTMLTableElement, options: TableOptions) => {
    return;
    
    const data = e.detail;
    const row = data.row as HTMLTableRowElement;

    Array.from(row.children).forEach(cell => {
        const key = cell.getAttribute("data-key");
        if (key == null) return;
        if (key == options.primaryKey) return;

        cell.setAttribute("contenteditable", "true");
    });
}
