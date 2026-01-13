import Chart from "chart.js/auto";
import { ajax } from "js-classes/ajax";
import { addBindings } from "js-classes/bindings";
import { notification } from "js-classes/notifications";

import { loader } from "../classes/helpers";
import { QueryBuilder } from "../diagram/querybuilder";
import { dateInput, numberInput } from "../diagram/validations";
import { Filter, FilterField, FilterOp, FilterValue } from "../types/filters";
import { label } from "../types/labels";
import { FunctionMap } from "../types/types";

const refs = {} as { [key: string]: HTMLElement };
const fnNames = {} as FunctionMap;

const state = {
	dimensions: new Set<string>(),
	filters: [] as Filter[],
}

const UNIQUE_FILTER_FIELDS = new Set<FilterField>([
	"startdate",
	"enddate",
	"ordertype",
	"orderstate",
	"customer",
]);

const isUniqueField = (field: FilterField) => UNIQUE_FILTER_FIELDS.has(field);

const OPS_BY_FIELD: Record<FilterField, FilterOp[]> = {
	startdate: ["gte", "lte", "between"],
	enddate: ["gte", "lte", "between"],
	ordertype: ["in"],
	orderstate: ["in"],
	customer: ["in"],
	volume: ["gt", "gte", "lt", "lte", "between"],
	profit: ["gt", "gte", "lt", "lte", "between"],
};

const genId = () => `f_${Math.random().toString(36).slice(2, 10)}`;

const init = () => {
	refs.ctxDiagram = document.getElementById("ctxDiagram") as HTMLElement;
	initFromUrl();
	loadDiagram();
	addBindings(fnNames);
}

fnNames.click_addDimension = () => {
	const dimSelect = document.getElementById("dimSelect") as HTMLSelectElement;
	const selectedDim = dimSelect.value;
	if (!state.dimensions.has(selectedDim)) {
		state.dimensions.add(selectedDim);
		renderDimensions();
	} else {
		notification("Die Dimension existiert bereits.", "warning", "This dimension already exists.", 3000);
	}
};

fnNames.click_addFilter = () => {
	const filterSelect = document.getElementById("filterSelect") as HTMLSelectElement;
	const field = filterSelect.value as FilterField;

	if (isUniqueField(field) && state.filters.some(f => f.field === field)) {
		notification("Der Filter existiert bereits.", "warning", "This filter already exists.", 3000);
		return;
	}

	const defaultOp = OPS_BY_FIELD[field][0] ?? "eq";

	const defaultValue: FilterValue =
		field === "volume" || field === "profit"
			? 0
			: field === "startdate" || field === "enddate"
				? ""
				: [];

	state.filters.push({
		id: genId(),
		field,
		op: defaultOp,
		value: defaultOp === "between" ? [0, 0] : defaultValue,
	});

	renderFilters();
};

fnNames.click_generateDiagram = async () => {
	const response = await loadStats();
};

fnNames.click_resetDiagram = () => {
	state.dimensions.clear();
	state.filters = [];
	renderDimensions();
	renderFilters();
};

const loadDiagram = () => {
	new Chart(refs.ctxDiagram as HTMLCanvasElement, {
		type: "line",
		data: {
			labels: [],
			datasets: [{
				label: "Keine Daten geladen",
				data: [],
				borderColor: "#000",
				backgroundColor: "#eee",
			}],
		},
		options: {
			plugins: {
				legend: { display: false },
				tooltip: { enabled: false },
			},
			scales: {
				x: { display: false },
				y: { display: false },
			},
		},
		plugins: [
			emptyStatePlugin("iconDiagram", "Keine Daten geladen"),
		],
	});
}

const emptyStatePlugin = (svgElementId: string, text: string) => {
	const svgEl = document.getElementById(svgElementId) as any;
	const img = new Image();
	let isReady = false;

	if (svgEl) {
		const svgString = new XMLSerializer().serializeToString(svgEl);
		const svgBase64 = btoa(unescape(encodeURIComponent(svgString)));
		img.src = `data:image/svg+xml;base64,${svgBase64}`;

		img.onload = () => {
			isReady = true;
			const chart = Chart.getChart("ctxDiagram");
			if (chart) chart.draw();
		};
	}

	return {
		id: "emptyState",
		afterDraw(chart: any) {
			const { datasets } = chart.data;
			const hasData = datasets.length > 0 && datasets[0].data.length > 0;

			if (!hasData && isReady) {
				const { ctx, chartArea: { top, bottom, left, right, width, height } } = chart;
				const centerX = (left + right) / 2;
				const centerY = (top + bottom) / 2;
				const iconSize = 60;

				ctx.save();

				ctx.globalAlpha = 0.2;
				ctx.drawImage(img, centerX - iconSize / 2, centerY - iconSize, iconSize, iconSize);

				ctx.globalAlpha = 1.0;
				ctx.textAlign = 'center';
				ctx.textBaseline = 'middle';
				ctx.font = 'bold 14px sans-serif';
				ctx.fillStyle = '#9ca3af';
				ctx.fillText(text, centerX, centerY + 15);

				ctx.restore();
			}
		}
	}
};

const renderDimensions = () => {
	const cont = document.getElementById("dimCont") as HTMLElement;
	cont.innerHTML = "";

	state.dimensions.forEach((dim: string) => {
		const div = document.createElement("div");
		div.className = "p-2 bg-gray-200 rounded-sm mr-2 mb-2 inline-block text-gray-700";
		div.innerText = label(dim);

		const removeBtn = document.createElement("button");
		removeBtn.type = "button";
		removeBtn.className = "btn-delete mr-0.5";
		removeBtn.innerText = "✕";
		div.appendChild(removeBtn);

		removeBtn.addEventListener("click", () => {
			state.dimensions.delete(dim);
			renderDimensions();
		});

		cont.appendChild(div);
	});

	syncUrlFromState();
}

const renderFilters = () => {
	const cont = document.getElementById("filterCont") as HTMLElement;
	cont.innerHTML = "";

	state.filters.forEach(filter => {
		cont.appendChild(renderFilterRow(filter));
	});

	syncUrlFromState();
};

const renderFilterRow = (filter: Filter) => {
	const row = document.createElement("div");
	row.className = "p-2 bg-gray-200 rounded-sm mb-2 flex items-center gap-2";
	row.dataset.filterId = filter.id;

	const name = document.createElement("span");
	name.className = "font-medium text-gray-700 min-w-[120px]";
	name.innerText = label(filter.field);
	row.appendChild(name);

	const opSelect = document.createElement("select");
	opSelect.className = "input-primary";
	OPS_BY_FIELD[filter.field].forEach(op => {
		const opt = document.createElement("option");
		opt.value = op;
		opt.text = opLabel(op);
		if (op === filter.op) opt.selected = true;
		opSelect.appendChild(opt);
	});
	row.appendChild(opSelect);

	const valWrap = document.createElement("div");
	valWrap.className = "flex items-center gap-2 flex-1";
	row.appendChild(valWrap);

	const removeBtn = document.createElement("button");
	removeBtn.type = "button";
	removeBtn.className = "btn-delete";
	removeBtn.innerText = "✕";
	row.appendChild(removeBtn);

	renderValueInputs(valWrap, filter);

	opSelect.addEventListener("change", () => {
		filter.op = opSelect.value as FilterOp;

		if (filter.op === "between") {
			filter.value = [0, 0];
		} else if (filter.field === "volume" || filter.field === "profit") {
			filter.value = 0;
		} else if (filter.field === "startdate" || filter.field === "enddate") {
			filter.value = "";
		} else {
			filter.value = [];
		}

		renderFilters();
	});

	removeBtn.addEventListener("click", () => {
		state.filters = state.filters.filter(f => f.id !== filter.id);
		renderFilters();
	});

	return row;
};

const opLabel = (op: FilterOp) => {
	switch (op) {
		case "eq": return "=";
		case "neq": return "≠";
		case "in": return "ist in";
		case "gt": return ">";
		case "gte": return "≥";
		case "lt": return "<";
		case "lte": return "≤";
		case "between": return "zwischen";
		default: return op;
	}
};

const renderValueInputs = (wrap: HTMLElement, filter: Filter) => {
	wrap.innerHTML = "";

	if (filter.field === "startdate" || filter.field === "enddate") {
		if (filter.op === "between") {
			const a = dateInput((filter.value as any)?.[0] ?? "", v => {
				const cur = Array.isArray(filter.value) ? filter.value : ["", ""];
				filter.value = [v, cur[1]] as any;
			});
			const b = dateInput((filter.value as any)?.[1] ?? "", v => {
				const cur = Array.isArray(filter.value) ? filter.value : ["", ""];
				filter.value = [cur[0], v] as any;
			});
			wrap.appendChild(a);
			wrap.appendChild(b);
		} else {
			const i = dateInput((filter.value as string) ?? "", v => (filter.value = v));
			wrap.appendChild(i);
		}
		return;
	}

	if (filter.field === "volume" || filter.field === "profit") {
		if (filter.op === "between") {
			const cur = Array.isArray(filter.value) ? (filter.value as any) : [0, 0];
			const a = numberInput(cur[0] ?? 0, v => (filter.value = [v, cur[1] ?? 0]));
			const b = numberInput(cur[1] ?? 0, v => (filter.value = [cur[0] ?? 0, v]));
			wrap.appendChild(a);
			wrap.appendChild(b);
		} else {
			const i = numberInput((filter.value as number) ?? 0, v => (filter.value = v));
			wrap.appendChild(i);
		}
		return;
	}

	const input = document.createElement("input");
	input.className = "input-primary flex-1";
	input.type = "text";
	input.placeholder = "Werte (kommagetrennt), z.B. A,B,C";

	const current = Array.isArray(filter.value) ? filter.value : [];
	input.value = current.join(",");

	input.addEventListener("input", () => {
		const parts = input.value
			.split(",")
			.map(s => s.trim())
			.filter(Boolean);
		filter.value = parts;
	});

	wrap.appendChild(input);
};

async function loadStats() {
	const payload = QueryBuilder.toPayload(state);

	return ajax.post("/api/v1/stats", payload, true);
}

function syncUrlFromState() {
	const payload = QueryBuilder.toPayload(state);
	const url = QueryBuilder.toUrl(payload);

	window.history.replaceState({}, "", url);
}

function initFromUrl() {
	const payload = QueryBuilder.fromUrl();
	QueryBuilder.applyToState(payload, state);

	renderDimensions();
	renderFilters();
}

loader(init);
