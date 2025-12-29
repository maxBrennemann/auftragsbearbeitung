import { ajax } from "js-classes/ajax.js";
import Chart from "chart.js/auto";
import { loader } from "../classes/helpers";
import { addBindings } from "js-classes/bindings.js";
import { FunctionMap } from "../types/types";

const refs = {} as { [key: string]: HTMLElement };
const fnNames = {} as FunctionMap;
const state = {
	dimensions: new Set<string>(),
	filters: [],
}

const LABELS = {
	month: "Monat",
	year: "Jahr",
	ordertype: "Auftragstyp",
	customer: "Kunde",
	orderstate: "Auftragsstatus",
	volume: "Umsatz",
	profit: "Gewinn",
} as const;

type LabelKey = keyof typeof LABELS;

const label = (key: string) => {
	return LABELS[key as LabelKey] || key;
}

const init = () => {
	refs.ctxDiagram = document.getElementById("ctxDiagram") as HTMLElement;
	loadDiagram();
	addBindings(fnNames);
}

fnNames.click_addDimension = () => {
	const dimSelect = document.getElementById("dimSelect") as HTMLSelectElement;
	const selectedDim = dimSelect.value;
	if (!state.dimensions.has(selectedDim)) {
		state.dimensions.add(selectedDim);
		renderDimensions();
	}
};

fnNames.click_addFilter = () => {};

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
		div.className = "p-2 bg-gray-200 rounded mr-2 mb-2 inline-block text-gray-700";
		div.innerText = label(dim);
		cont.appendChild(div);

		div.addEventListener("click", () => {
			state.dimensions.delete(dim);
			renderDimensions();
		});
	});
}

loader(init);
