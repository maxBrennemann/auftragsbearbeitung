export const deleteButton = (id) => {
    const button = `
        <button class="btn-delete ml-1" title="Löschen" data-id="${id}">
            <svg xmlns="http://www.w3.org/2000/svg" width="15px" height="15px" viewBox="0 0 24 24">
                <title>Löschen</title>
                <path d="M19,4H15.5L14.5,3H9.5L8.5,4H5V6H19M6,19A2,2 0 0,0 8,21H16A2,2 0 0,0 18,19V7H6V19Z" />
            </svg>
        </button>`;
    
    const template = document.createElement("template");
    template.innerHTML = button;

    return template.content.firstElementChild;
}

export const editButton = (id) => {
    const button = `
        <button class="btn-edit" title="Bearbeiten" data-id="${id}">
            <svg xmlns="http://www.w3.org/2000/svg" width="15px" height="15px" viewBox="0 0 24 24">
                <title>Bearbeiten</title>
                <path d="M20.71,7.04C21.1,6.65 21.1,6 20.71,5.63L18.37,3.29C18,2.9 17.35,2.9 16.96,3.29L15.12,5.12L18.87,8.87M3,17.25V21H6.75L17.81,9.93L14.06,6.18L3,17.25Z" />
            </svg>
        </button>`;

    const template = document.createElement("template");
    template.innerHTML = button;

    return template.content.firstElementChild;
}

export const resetInputs = (inputs) => {
    Array.from(inputs).forEach(input => {
        const defaultValue = input.dataset.default;
        input.value = defaultValue ?? "";
    });
}

export const parseInput = (value) => {
    value *= 100;
    value = parseInt(value);
    return value;
}

export const parseEuro = (value) => {
    return value;
}
