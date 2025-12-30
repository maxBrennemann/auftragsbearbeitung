export const numberInput = (value: number, onChange: (v: number) => void) => {
	const i = document.createElement("input");
	i.className = "input-primary";
	i.type = "number";
	i.step = "0.01";
	i.value = String(value ?? 0);
	i.addEventListener("input", () => onChange(Number(i.value)));
	return i;
};

export const dateInput = (value: string, onChange: (v: string) => void) => {
	const i = document.createElement("input");
	i.className = "input-primary";
	i.type = "date";
	i.value = value ?? "";
	i.addEventListener("input", () => onChange(i.value));
	return i;
};
