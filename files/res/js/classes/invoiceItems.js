import { ajax } from "./ajax.js";
import { renderTable } from "./table.js";

export const getItems = async (id, type = "order") => {
    let query = ``;

    switch (type) {
        case "order":
            query = `/api/v1/order-items/${id}/all`;
            break;
        case "invoice":
            query = `/api/v1/order-items/invoice/${id}/all`;
            break;
        case "offer":
            query = `/api/v1/order-items/offer/${id}/all`;
            break;
    }

    const data = await ajax.get(query);
    return data;
}

export const getItemsTable = async (tableName, id, type = "order") => {
    const data = await getItems(id, type);
    const header = [
        {
            "key": "id",
            "label": "Id",
        },
        {
            "key": "position",
            "label": "Position",
        },
        {
            "key": "name",
            "label": "Bezeichnung",
        },
        {
            "key": "description",
            "label": "Beschreibung",
        },
        {
            "key": "quantity",
            "label": "Menge",
        },
        {
            "key": "unit",
            "label": "MEH",
        },
        {
            "key": "price",
            "label": "Preis [€]",
        },
        {
            "key": "totalPrice",
            "label": "Gesamt [€]",
        },
        {
            "key": "purchasePrice",
            "label": "EK [€]",
        },
    ];

    const table = renderTable(tableName, header, data, {
        "primaryKey": "id",
        "hide": ["id"],
        "hideOptions": ["addRow"],
        "styles": {
            "table": {
                "className": ["w-full"],
            },
        },
        "autoSort": true,
    });
    return table;
}
