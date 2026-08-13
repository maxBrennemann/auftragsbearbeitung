import Chart from "chart.js/auto";
import { ajax } from "js-classes/ajax";
import { addBindings } from "js-classes/bindings";

import { loader } from "../classes/helpers";
import { FunctionMap } from "../types/types";

const fnNames = {} as FunctionMap;

/**
 * Matches the app's existing Tailwind accent colors (btn-primary green, btn-edit/active blue) rather than a
 * generic chart palette, so the dashboard reads as part of the app instead of a bolted-on chart library.
 */
const COLORS = {
	blue: "#2563eb",
	green: "#16a34a",
	amber: "#d97706",
	gray: "#9ca3af",
};

const STATUS_COLORS = {
	good: "#0ca30c",
	warning: "#fab219",
	serious: "#ec835a",
	critical: "#d03b3b",
};

const CHART_SURFACE = "#ffffff";
const GRID_COLOR = "#e5e7eb";
const MUTED_TEXT = "#6b7280";

/** Order chosen so adjacent bars stay colorblind-distinguishable (blue-amber-gray-green passes; amber next to green does not). */
const PIPELINE_COLORS: { [status: string]: string } = {
	default: COLORS.blue,
	finished: COLORS.amber,
	archived: COLORS.gray,
	invoiced: COLORS.green,
};

const AGING_COLORS: { [bucket: string]: string } = {
	"0-30": STATUS_COLORS.good,
	"31-60": STATUS_COLORS.warning,
	"61-90": STATUS_COLORS.serious,
	"90+": STATUS_COLORS.critical,
};

type DashboardPoint = { date: string; value: number | null };
type PipelineEntry = { status: string; label: string; value: number };
type AgingEntry = { bucket: string; label: string; count: number; amount: number };
type TopCustomer = { name: string; value: number; orderCount: number };

type Dashboard = {
	range: { startDate: string; endDate: string };
	legacyDataCutoff: string | null;
	kpis: {
		orderCount: number;
		revenue: number;
		avgPaymentDuration: number | null;
		avgPaymentDurationSampleSize: number;
		openInvoiceCount: number;
		openInvoiceAmount: number;
	};
	ordersOverTime: DashboardPoint[];
	revenueOverTime: DashboardPoint[];
	paymentDurationOverTime: DashboardPoint[];
	orderPipeline: PipelineEntry[];
	openInvoiceAging: AgingEntry[];
	topCustomers: TopCustomer[];
};

const charts: { [id: string]: Chart } = {};

const currencyFormat = new Intl.NumberFormat("de-DE", { style: "currency", currency: "EUR", maximumFractionDigits: 0 });
const numberFormat = new Intl.NumberFormat("de-DE");
const MONTHS = ["Jan", "Feb", "Mär", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dez"];

const init = () => {
	initFromUrl();
	loadDashboard();
	addBindings(fnNames);
};

fnNames.click_applyRange = () => {
	syncUrlFromInputs();
	loadDashboard();
};

fnNames.click_presetRange30 = () => applyPreset(daysAgo(30), today());
fnNames.click_presetRange90 = () => applyPreset(daysAgo(90), today());
fnNames.click_presetRange12Months = () => applyPreset(monthsAgo(12), today());
fnNames.click_presetRangeYear = () => applyPreset(`${new Date().getFullYear()}-01-01`, today());

function applyPreset(startDate: string, endDate: string) {
	(document.getElementById("startDate") as HTMLInputElement).value = startDate;
	(document.getElementById("endDate") as HTMLInputElement).value = endDate;
	syncUrlFromInputs();
	loadDashboard();
}

function today(): string {
	return new Date().toISOString().slice(0, 10);
}

function daysAgo(days: number): string {
	const d = new Date();
	d.setDate(d.getDate() - days);
	return d.toISOString().slice(0, 10);
}

function monthsAgo(months: number): string {
	const d = new Date();
	d.setMonth(d.getMonth() - months);
	return d.toISOString().slice(0, 10);
}

function initFromUrl() {
	const params = new URLSearchParams(window.location.search);
	const startDate = params.get("startDate") ?? monthsAgo(12);
	const endDate = params.get("endDate") ?? today();

	(document.getElementById("startDate") as HTMLInputElement).value = startDate;
	(document.getElementById("endDate") as HTMLInputElement).value = endDate;
}

function syncUrlFromInputs() {
	const startDate = (document.getElementById("startDate") as HTMLInputElement).value;
	const endDate = (document.getElementById("endDate") as HTMLInputElement).value;

	const params = new URLSearchParams();
	if (startDate) params.set("startDate", startDate);
	if (endDate) params.set("endDate", endDate);

	window.history.replaceState({}, "", `${window.location.pathname}?${params.toString()}`);
}

async function loadDashboard() {
	const startDate = (document.getElementById("startDate") as HTMLInputElement).value;
	const endDate = (document.getElementById("endDate") as HTMLInputElement).value;

	const response = await ajax.get<Dashboard>("/api/v1/stats/dashboard", { startDate, endDate });

	if (!response.success || !response.data) {
		return;
	}

	renderLegacyCutoffNotice(response.data.legacyDataCutoff);
	renderKpis(response.data);
	renderOrdersChart(response.data.ordersOverTime);
	renderRevenueChart(response.data.revenueOverTime);
	renderPaymentDurationChart(response.data.paymentDurationOverTime);
	renderAgingChart(response.data.openInvoiceAging);
	renderPipelineChart(response.data.orderPipeline);
	renderTopCustomersChart(response.data.topCustomers);
}

function renderLegacyCutoffNotice(cutoff: string | null) {
	const el = document.getElementById("legacyCutoffNotice");
	if (!el) return;

	if (!cutoff) {
		el.classList.add("hidden");
		return;
	}

	const [year, month, day] = cutoff.split("-");
	el.innerText = `Hinweis: Umsatz, Zahlungsdauer, offene Rechnungen und Top-Kunden berücksichtigen aufgrund der Einstellung "Altdaten ausblenden" nur Rechnungen ab ${day}.${month}.${year}.`;
	el.classList.remove("hidden");
}

/** Below this, payment_date is too sparsely recorded historically for the average to mean anything - see Statistics::getPaymentDurationOverTime(). */
const MIN_RELIABLE_PAYMENT_SAMPLE = 10;

function renderKpis(dashboard: Dashboard) {
	const { kpis, openInvoiceAging } = dashboard;

	setText("kpiOrderCount", numberFormat.format(kpis.orderCount));
	setText("kpiRevenue", currencyFormat.format(kpis.revenue));
	setText("kpiPaymentDuration", paymentDurationLabel(kpis.avgPaymentDuration, kpis.avgPaymentDurationSampleSize));
	setText("kpiOpenInvoices", `${currencyFormat.format(kpis.openInvoiceAmount)} (${numberFormat.format(kpis.openInvoiceCount)})`);

	const overdue = openInvoiceAging.filter(b => b.bucket === "61-90" || b.bucket === "90+");
	const overdueCount = overdue.reduce((sum, b) => sum + b.count, 0);
	const overdueAmount = overdue.reduce((sum, b) => sum + b.amount, 0);
	setText("kpiOverdueInvoices", `${currencyFormat.format(overdueAmount)} (${numberFormat.format(overdueCount)})`);
}

function paymentDurationLabel(avgDays: number | null, sampleSize: number): string {
	if (avgDays == null || sampleSize === 0) return "–";
	if (sampleSize < MIN_RELIABLE_PAYMENT_SAMPLE) return `${numberFormat.format(avgDays)} Tage (n=${sampleSize}, wenig Daten)`;
	return `${numberFormat.format(avgDays)} Tage (n=${sampleSize})`;
}

function setText(id: string, text: string) {
	const el = document.getElementById(id);
	if (el) el.innerText = text;
}

function monthLabel(dateStr: string): string {
	const [year, month] = dateStr.split("-");
	const monthIndex = parseInt(month, 10) - 1;
	return `${MONTHS[monthIndex] ?? month} ${year.slice(2)}`;
}

function destroyChart(canvasId: string) {
	charts[canvasId]?.destroy();
	delete charts[canvasId];
}

const EMPTY_STATE_ATTR = "data-empty-state";

function showEmptyState(canvasId: string) {
	const canvas = document.getElementById(canvasId) as HTMLCanvasElement | null;
	if (!canvas) return;

	canvas.style.display = "none";

	const wrap = canvas.parentElement as HTMLElement;
	let msg = wrap.querySelector<HTMLElement>(`[${EMPTY_STATE_ATTR}]`);
	if (!msg) {
		msg = document.createElement("div");
		msg.setAttribute(EMPTY_STATE_ATTR, "true");
		msg.className = "h-full flex items-center justify-center text-sm text-gray-400";
		wrap.appendChild(msg);
	}
	msg.innerText = "Keine Daten im Zeitraum";
}

function hideEmptyState(canvasId: string) {
	const canvas = document.getElementById(canvasId) as HTMLCanvasElement | null;
	if (!canvas) return;

	canvas.style.display = "";
	canvas.parentElement?.querySelector(`[${EMPTY_STATE_ATTR}]`)?.remove();
}

function lineChart(canvasId: string, points: DashboardPoint[], color: string, valueFormatter: (v: number) => string) {
	destroyChart(canvasId);
	hideEmptyState(canvasId);

	if (points.length === 0) {
		showEmptyState(canvasId);
		return;
	}

	const ctx = document.getElementById(canvasId) as HTMLCanvasElement;
	charts[canvasId] = new Chart(ctx, {
		type: "line",
		data: {
			labels: points.map(p => monthLabel(p.date)),
			datasets: [{
				data: points.map(p => p.value ?? 0),
				borderColor: color,
				backgroundColor: hexToRgba(color, 0.1),
				fill: true,
				tension: 0.3,
				borderWidth: 2,
				pointRadius: 4,
				pointHoverRadius: 6,
				pointBackgroundColor: color,
				pointBorderColor: CHART_SURFACE,
				pointBorderWidth: 2,
			}],
		},
		options: {
			responsive: true,
			maintainAspectRatio: false,
			plugins: {
				legend: { display: false },
				tooltip: { callbacks: { label: c => valueFormatter(c.parsed.y ?? 0) } },
			},
			scales: {
				x: { grid: { display: false }, ticks: { color: MUTED_TEXT } },
				y: {
					beginAtZero: true,
					grid: { color: GRID_COLOR },
					border: { display: false },
					ticks: { color: MUTED_TEXT, callback: v => valueFormatter(v as number) },
				},
			},
		},
	});
}

function renderOrdersChart(points: DashboardPoint[]) {
	lineChart("chartOrders", points, COLORS.blue, v => numberFormat.format(v));
}

function renderRevenueChart(points: DashboardPoint[]) {
	lineChart("chartRevenue", points, COLORS.green, v => currencyFormat.format(v));
}

function renderPaymentDurationChart(points: DashboardPoint[]) {
	lineChart("chartPaymentDuration", points, COLORS.amber, v => `${numberFormat.format(v)} Tage`);
}

const valueLabelPlugin = (formatter: (v: number) => string, horizontal = false) => ({
	id: "valueLabels",
	afterDatasetsDraw(chart: Chart) {
		const meta = chart.getDatasetMeta(0);
		const data = chart.data.datasets[0].data as number[];

		chart.ctx.save();
		chart.ctx.fillStyle = "#374151";
		chart.ctx.font = "600 11px sans-serif";
		chart.ctx.textAlign = horizontal ? "left" : "center";
		chart.ctx.textBaseline = "middle";

		meta.data.forEach((element, index) => {
			const value = data[index];
			if (value == null) return;
			const pos = element.tooltipPosition(true);
			if (pos.x == null || pos.y == null) return;
			const label = formatter(value);

			if (horizontal) {
				chart.ctx.fillText(label, pos.x + 6, pos.y);
			} else {
				chart.ctx.fillText(label, pos.x, pos.y - 10);
			}
		});

		chart.ctx.restore();
	},
});

function renderAgingChart(buckets: AgingEntry[]) {
	destroyChart("chartAging");
	hideEmptyState("chartAging");

	const canvas = document.getElementById("chartAging") as HTMLCanvasElement;
	const hasData = buckets.some(b => b.count > 0);
	if (!hasData) {
		showEmptyState("chartAging");
		return;
	}

	charts.chartAging = new Chart(canvas, {
		type: "bar",
		data: {
			labels: buckets.map(b => b.label),
			datasets: [{
				data: buckets.map(b => b.amount),
				backgroundColor: buckets.map(b => AGING_COLORS[b.bucket] ?? COLORS.blue),
				borderRadius: 4,
				maxBarThickness: 48,
			}],
		},
		options: {
			responsive: true,
			maintainAspectRatio: false,
			layout: { padding: { top: 20 } },
			plugins: {
				legend: { display: false },
				tooltip: {
					callbacks: {
						label: c => {
							const bucket = buckets[c.dataIndex];
							return `${currencyFormat.format(bucket.amount)} (${bucket.count} Rechnungen)`;
						},
					},
				},
			},
			scales: {
				x: { grid: { display: false }, ticks: { color: MUTED_TEXT } },
				y: {
					beginAtZero: true,
					grid: { color: GRID_COLOR },
					border: { display: false },
					ticks: { color: MUTED_TEXT, callback: v => currencyFormat.format(v as number) },
				},
			},
		},
		plugins: [valueLabelPlugin(v => currencyFormat.format(v))],
	});
}

function renderPipelineChart(pipeline: PipelineEntry[]) {
	destroyChart("chartPipeline");
	hideEmptyState("chartPipeline");

	const canvas = document.getElementById("chartPipeline") as HTMLCanvasElement;
	const hasData = pipeline.some(p => p.value > 0);
	if (!hasData) {
		showEmptyState("chartPipeline");
		return;
	}

	charts.chartPipeline = new Chart(canvas, {
		type: "bar",
		data: {
			labels: pipeline.map(p => p.label),
			datasets: [{
				data: pipeline.map(p => p.value),
				backgroundColor: pipeline.map(p => PIPELINE_COLORS[p.status] ?? COLORS.blue),
				borderRadius: 4,
				maxBarThickness: 48,
			}],
		},
		options: {
			responsive: true,
			maintainAspectRatio: false,
			layout: { padding: { top: 20 } },
			plugins: {
				legend: { display: false },
				tooltip: { callbacks: { label: c => `${numberFormat.format(c.parsed.y ?? 0)} Aufträge` } },
			},
			scales: {
				x: { grid: { display: false }, ticks: { color: MUTED_TEXT } },
				y: {
					beginAtZero: true,
					grid: { color: GRID_COLOR },
					border: { display: false },
					ticks: { color: MUTED_TEXT, stepSize: 1 },
				},
			},
		},
		plugins: [valueLabelPlugin(v => numberFormat.format(v))],
	});
}

function renderTopCustomersChart(customers: TopCustomer[]) {
	destroyChart("chartTopCustomers");
	hideEmptyState("chartTopCustomers");

	if (customers.length === 0) {
		showEmptyState("chartTopCustomers");
		return;
	}

	const sorted = [...customers].sort((a, b) => a.value - b.value);
	const canvas = document.getElementById("chartTopCustomers") as HTMLCanvasElement;

	charts.chartTopCustomers = new Chart(canvas, {
		type: "bar",
		data: {
			labels: sorted.map(c => c.name),
			datasets: [{
				data: sorted.map(c => c.value),
				backgroundColor: COLORS.blue,
				borderRadius: 4,
				maxBarThickness: 22,
			}],
		},
		options: {
			indexAxis: "y",
			responsive: true,
			maintainAspectRatio: false,
			layout: { padding: { right: 70 } },
			plugins: {
				legend: { display: false },
				tooltip: {
					callbacks: {
						label: c => {
							const customer = sorted[c.dataIndex];
							return `${currencyFormat.format(customer.value)} (${customer.orderCount} Rechnungen)`;
						},
					},
				},
			},
			scales: {
				x: {
					beginAtZero: true,
					grid: { color: GRID_COLOR },
					border: { display: false },
					ticks: { color: MUTED_TEXT, callback: v => currencyFormat.format(v as number) },
				},
				y: { grid: { display: false }, ticks: { color: MUTED_TEXT } },
			},
		},
		plugins: [valueLabelPlugin(v => currencyFormat.format(v), true)],
	});
}

function hexToRgba(hex: string, alpha: number): string {
	const r = parseInt(hex.slice(1, 3), 16);
	const g = parseInt(hex.slice(3, 5), 16);
	const b = parseInt(hex.slice(5, 7), 16);
	return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

loader(init);
