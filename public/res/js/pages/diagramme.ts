import Chart from "chart.js/auto";
import { ajax } from "js-classes/ajax";
import { addBindings } from "js-classes/bindings";
import { ArrowDown, ArrowUp } from "lucide";

import { loader } from "../classes/helpers";
import { iconHtml } from "../classes/icon";
import { FunctionMap } from "../types/types";

const fnNames = {} as FunctionMap;

/** Farben kommen aus den Design-Tokens (@theme in input.css), damit Diagramme und Oberfläche dieselbe Palette nutzen. */
const token = (name: string, fallback: string): string =>
	getComputedStyle(document.documentElement).getPropertyValue(name).trim() || fallback;

const COLORS = {
	ink: token("--color-ink", "#0a3350"),
	inkHover: token("--color-ink-hover", "#164a73"),
	inkLight: "#7d9cb8",
	ok: token("--color-ok", "#2e7d4f"),
	warn: "#c08a1e",
	serious: "#c4581c",
	danger: token("--color-danger", "#b3261e"),
	muted: token("--color-text-muted", "#5d6976"),
	text: token("--color-text", "#1d2833"),
	grid: "#cfd6dd",
	archived: "#b8c0c8",
	surface: "#ffffff",
};

const PIPELINE_COLORS: { [status: string]: string } = {
	default: COLORS.ink,
	finished: COLORS.inkLight,
	invoiced: COLORS.ok,
	archived: COLORS.archived,
};

/** Alter offener Rechnungen: von „in Ordnung“ bis „kritisch“ */
const AGING_COLORS: { [bucket: string]: string } = {
	"0-30": COLORS.ok,
	"31-60": COLORS.warn,
	"61-90": COLORS.serious,
	"90+": COLORS.danger,
};

type DashboardPoint = { date: string; value: number | null };
type PipelineEntry = { status: string; label: string; value: number };
type AgingEntry = { bucket: string; label: string; count: number; amount: number };
type TopCustomer = { name: string; value: number; orderCount: number };
type DateRange = { startDate: string; endDate: string };

type Dashboard = {
	range: DateRange;
	legacyDataCutoff: string | null;
	kpis: {
		orderCount: number;
		revenue: number;
		avgPaymentDuration: number | null;
		avgPaymentDurationSampleSize: number;
		openInvoiceCount: number;
		openInvoiceAmount: number;
	};
	comparison: {
		range: DateRange;
		orderCount: number;
		revenue: number | null;
		avgPaymentDuration: number | null;
		avgPaymentDurationSampleSize: number;
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
const percentFormat = new Intl.NumberFormat("de-DE", { maximumFractionDigits: 1 });
const MONTHS = ["Jan", "Feb", "Mär", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dez"];

/** Unter dieser Zahl an Zahlungen ist der Durchschnitt kaum aussagekräftig, siehe Statistics::getPaymentDurationOverTime(). */
const MIN_RELIABLE_PAYMENT_SAMPLE = 10;

/* ---------- Zeitraum ---------- */

type Preset = { key: string; label: string; range: () => DateRange };

const PRESETS: Preset[] = [
	{ key: "30d", label: "Letzte 30 Tage", range: () => ({ startDate: daysAgo(30), endDate: today() }) },
	{ key: "90d", label: "Letzte 90 Tage", range: () => ({ startDate: daysAgo(90), endDate: today() }) },
	{ key: "12m", label: "Letzte 12 Monate", range: () => ({ startDate: monthsAgo(12), endDate: today() }) },
	{ key: "year", label: `Dieses Jahr (${new Date().getFullYear()})`, range: () => ({ startDate: `${new Date().getFullYear()}-01-01`, endDate: today() }) },
];

const init = () => {
	Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
	Chart.defaults.font.size = 11;
	Chart.defaults.color = COLORS.muted;

	initFromUrl();
	loadDashboard();
	addBindings(fnNames);
};

fnNames.click_applyRange = () => {
	syncUrlFromInputs();
	loadDashboard();
};

fnNames.click_presetRange30 = () => applyPreset("30d");
fnNames.click_presetRange90 = () => applyPreset("90d");
fnNames.click_presetRange12Months = () => applyPreset("12m");
fnNames.click_presetRangeYear = () => applyPreset("year");

function applyPreset(key: string) {
	const preset = PRESETS.find(p => p.key === key);
	if (!preset) return;
	const { startDate, endDate } = preset.range();
	getInput("startDate").value = startDate;
	getInput("endDate").value = endDate;
	syncUrlFromInputs();
	loadDashboard();
}

const getInput = (id: string) => document.getElementById(id) as HTMLInputElement;

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

function formatDate(iso: string): string {
	const [year, month, day] = iso.split("-");
	return `${day}.${month}.${year}`;
}

function initFromUrl() {
	const params = new URLSearchParams(window.location.search);
	getInput("startDate").value = params.get("startDate") ?? monthsAgo(12);
	getInput("endDate").value = params.get("endDate") ?? today();
}

function syncUrlFromInputs() {
	const params = new URLSearchParams();
	if (getInput("startDate").value) params.set("startDate", getInput("startDate").value);
	if (getInput("endDate").value) params.set("endDate", getInput("endDate").value);

	window.history.replaceState({}, "", `${window.location.pathname}?${params.toString()}`);
}

function renderRange(dashboard: Dashboard) {
	const { startDate, endDate } = dashboard.range;
	const active = PRESETS.find(p => {
		const r = p.range();
		return r.startDate === startDate && r.endDate === endDate;
	});

	document.querySelectorAll<HTMLButtonElement>("[data-preset]").forEach(btn => {
		const isActive = btn.dataset.preset === active?.key;
		btn.classList.toggle("is-active", isActive);
		btn.setAttribute("aria-pressed", String(isActive));
	});

	if (!active) {
		document.getElementById("customRange")?.classList.remove("hidden");
	}

	const comparison = dashboard.comparison.range;
	setText("rangeTitle", active?.label ?? `${formatDate(startDate)} – ${formatDate(endDate)}`);
	setText("rangeSubtitle", `${formatDate(startDate)} – ${formatDate(endDate)} · verglichen mit ${formatDate(comparison.startDate)} – ${formatDate(comparison.endDate)}`);
}

/* ---------- Laden ---------- */

async function loadDashboard() {
	const startDate = getInput("startDate").value;
	const endDate = getInput("endDate").value;

	const response = await ajax.get<Dashboard>("/api/v1/stats/dashboard", { startDate, endDate });

	if (!response.success || !response.data) {
		return;
	}

	const data = response.data;
	renderRange(data);
	renderLegacyCutoffNotice(data.legacyDataCutoff);
	renderKpis(data);
	renderRevenueChart(fillMonths(data.revenueOverTime, data.range, 0), data.kpis.revenue);
	renderOrdersChart(fillMonths(data.ordersOverTime, data.range, 0), data.kpis.orderCount);
	renderAgingChart(data.openInvoiceAging, data.kpis);
	renderPaymentDurationChart(fillMonths(data.paymentDurationOverTime, data.range, null), data.kpis);
	renderPipelineChart(data.orderPipeline);
	renderTopCustomersChart(data.topCustomers);
}

function renderLegacyCutoffNotice(cutoff: string | null) {
	const el = document.getElementById("legacyCutoffNotice");
	if (!el) return;

	if (!cutoff) {
		el.classList.add("hidden");
		return;
	}

	setText("legacyCutoffText", `Umsatz, Zahlungsdauer, offene Rechnungen und Top-Kunden berücksichtigen wegen der Einstellung „Altdaten ausblenden“ nur Rechnungen ab ${formatDate(cutoff)}.`);
	el.classList.remove("hidden");
}

/* ---------- Kennzahlen ---------- */

/**
 * Veränderung gegenüber dem Vorjahr als HTML. goodWhenUp entscheidet über die Farbe (bei der Zahlungsdauer ist weniger besser).
 */
function deltaHtml(current: number | null, previous: number | null, goodWhenUp: boolean, format: (diff: number, pct: number) => string): string {
	if (current == null || previous == null || previous === 0) {
		return "<span>kein Vorjahreswert</span>";
	}

	const diff = current - previous;
	if (Math.abs(diff) < 0.05) {
		return "<b>unverändert</b> ggü. Vorjahr";
	}

	const isUp = diff > 0;
	const isGood = isUp === goodWhenUp;
	const arrow = iconHtml(isUp ? ArrowUp : ArrowDown, { size: 12 });
	return `<b class="${isGood ? "is-good" : "is-bad"}">${arrow}${format(Math.abs(diff), Math.abs(diff / previous) * 100)}</b> ggü. Vorjahr`;
}

function renderKpis(dashboard: Dashboard) {
	const { kpis, comparison, openInvoiceAging } = dashboard;

	setText("kpiRevenue", currencyFormat.format(kpis.revenue));
	setHtml("kpiRevenueDelta", deltaHtml(kpis.revenue, comparison.revenue, true, (_, pct) => `${percentFormat.format(pct)} %`));

	setText("kpiOrderCount", numberFormat.format(kpis.orderCount));
	setHtml("kpiOrderCountDelta", deltaHtml(kpis.orderCount, comparison.orderCount, true, (_, pct) => `${percentFormat.format(pct)} %`));

	const hasDuration = kpis.avgPaymentDuration != null && kpis.avgPaymentDurationSampleSize > 0;
	setText("kpiPaymentDuration", hasDuration ? `${numberFormat.format(kpis.avgPaymentDuration as number)} Tage` : "–");
	if (hasDuration && kpis.avgPaymentDurationSampleSize < MIN_RELIABLE_PAYMENT_SAMPLE) {
		setHtml("kpiPaymentDurationDelta", `<span>nur ${kpis.avgPaymentDurationSampleSize} Zahlungen, wenig aussagekräftig</span>`);
	} else {
		const previous = comparison.avgPaymentDurationSampleSize >= MIN_RELIABLE_PAYMENT_SAMPLE ? comparison.avgPaymentDuration : null;
		setHtml("kpiPaymentDurationDelta", deltaHtml(kpis.avgPaymentDuration, previous, false, diff => `${numberFormat.format(Math.round(diff * 10) / 10)} Tage`));
	}

	const overdue = openInvoiceAging.filter(b => b.bucket === "61-90" || b.bucket === "90+");
	const overdueCount = overdue.reduce((sum, b) => sum + b.count, 0);
	setText("kpiOpenInvoices", currencyFormat.format(kpis.openInvoiceAmount));
	setHtml(
		"kpiOpenInvoicesDetail",
		`<span>${plural(kpis.openInvoiceCount, "Rechnung", "Rechnungen")}</span>`
		+ (overdueCount > 0 ? ` <span class="stamp stamp-danger">${numberFormat.format(overdueCount)} über 60 Tage</span>` : ""),
	);
}

function setText(id: string, text: string) {
	const el = document.getElementById(id);
	if (el) el.textContent = text;
}

function setHtml(id: string, html: string) {
	const el = document.getElementById(id);
	if (el) el.innerHTML = html;
}

function setChartTotal(canvasId: string, total: string, sub: string) {
	setText(`${canvasId}Total`, total);
	setText(`${canvasId}TotalSub`, sub);
}

/* ---------- Diagramme ---------- */

/**
 * Die API liefert nur Monate mit Einträgen. Für eine ehrliche Zeitachse jeden Monat des Zeitraums auffüllen:
 * fehlender Umsatz/Auftragseingang ist 0, eine fehlende Zahlungsdauer bleibt null (kein Wert, nicht „0 Tage“).
 */
function fillMonths(points: DashboardPoint[], range: DateRange, fill: number | null): DashboardPoint[] {
	const byMonth = new Map(points.map(p => [p.date.slice(0, 7), p.value == null ? null : Number(p.value)]));
	const result: DashboardPoint[] = [];
	const [startYear, startMonth] = range.startDate.split("-").map(Number);
	const [endYear, endMonth] = range.endDate.split("-").map(Number);

	let year = startYear;
	let month = startMonth;
	while (year < endYear || (year === endYear && month <= endMonth)) {
		const key = `${year}-${String(month).padStart(2, "0")}`;
		result.push({ date: key, value: byMonth.has(key) ? byMonth.get(key) ?? null : fill });

		month++;
		if (month > 12) {
			month = 1;
			year++;
		}
	}
	return result;
}

const plural = (count: number, one: string, many: string) => `${numberFormat.format(count)} ${count === 1 ? one : many}`;

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
		msg.className = "h-full flex items-center justify-center text-sm text-text-muted";
		wrap.appendChild(msg);
	}
	msg.textContent = "Keine Daten im Zeitraum";
}

function hideEmptyState(canvasId: string) {
	const canvas = document.getElementById(canvasId) as HTMLCanvasElement | null;
	if (!canvas) return;

	canvas.style.display = "";
	canvas.parentElement?.querySelector(`[${EMPTY_STATE_ATTR}]`)?.remove();
}

/** Prepare a chart slot; returns the canvas or null when there is nothing to draw. */
function prepare(canvasId: string, hasData: boolean): HTMLCanvasElement | null {
	destroyChart(canvasId);
	hideEmptyState(canvasId);
	if (!hasData) {
		showEmptyState(canvasId);
		return null;
	}
	return document.getElementById(canvasId) as HTMLCanvasElement;
}

/** Gestricheltes Gitter wie auf Millimeterpapier, Grundlinie in Tinte */
const valueAxis = (format: (v: number) => string, extra: object = {}) => ({
	beginAtZero: true,
	grid: { color: COLORS.grid, tickBorderDash: [2, 4] },
	border: { display: false, dash: [2, 4] },
	ticks: { color: COLORS.muted, callback: (v: number | string) => format(Number(v)) },
	...extra,
});

const categoryAxis = { grid: { display: false }, border: { color: COLORS.ink, width: 1.5 }, ticks: { color: COLORS.muted } };

function lineChart(canvasId: string, points: DashboardPoint[], color: string, valueFormatter: (v: number) => string) {
	const canvas = prepare(canvasId, points.some(p => (p.value ?? 0) !== 0));
	if (!canvas) return;

	const last = points.length - 1;
	charts[canvasId] = new Chart(canvas, {
		type: "line",
		data: {
			labels: points.map(p => monthLabel(p.date)),
			datasets: [{
				data: points.map(p => p.value ?? 0),
				borderColor: color,
				backgroundColor: hexToRgba(color, 0.08),
				fill: true,
				tension: 0.25,
				borderWidth: 2.25,
				// Nur der letzte Monat bekommt einen Punkt, beim Überfahren alle
				pointRadius: points.map((_, i) => (i === last ? 4.5 : 0)),
				pointHoverRadius: 5,
				pointBackgroundColor: color,
				pointBorderColor: COLORS.surface,
				pointBorderWidth: 2,
			}],
		},
		options: {
			responsive: true,
			maintainAspectRatio: false,
			interaction: { mode: "index", intersect: false },
			plugins: {
				legend: { display: false },
				tooltip: { callbacks: { label: c => valueFormatter(c.parsed.y ?? 0) } },
			},
			scales: { x: categoryAxis, y: valueAxis(valueFormatter) },
		},
	});
}

function barChart(canvasId: string, labels: string[], values: number[], colors: string | string[], valueFormatter: (v: number) => string, tooltip: (index: number) => string, horizontal = false) {
	const canvas = prepare(canvasId, values.some(v => v > 0));
	if (!canvas) return;

	charts[canvasId] = new Chart(canvas, {
		type: "bar",
		data: {
			labels,
			datasets: [{
				data: values,
				backgroundColor: colors,
				borderRadius: 2,
				maxBarThickness: horizontal ? 18 : 44,
			}],
		},
		options: {
			indexAxis: horizontal ? "y" : "x",
			responsive: true,
			maintainAspectRatio: false,
			layout: { padding: horizontal ? { right: 76 } : { top: 20 } },
			plugins: {
				legend: { display: false },
				tooltip: { callbacks: { label: c => tooltip(c.dataIndex) } },
			},
			scales: horizontal
				? { x: valueAxis(valueFormatter), y: { grid: { display: false }, ticks: { color: COLORS.text } } }
				: { x: categoryAxis, y: valueAxis(valueFormatter, { ticks: { color: COLORS.muted, precision: 0, callback: (v: number | string) => valueFormatter(Number(v)) } }) },
		},
		// Werte über den Säulen nur, solange sie sich nicht überlagern; sonst bleibt der Tooltip
		plugins: values.length <= MAX_LABELED_BARS ? [valueLabelPlugin(valueFormatter, horizontal)] : [],
	});
}

const MAX_LABELED_BARS = 14;

const valueLabelPlugin = (formatter: (v: number) => string, horizontal = false) => ({
	id: "valueLabels",
	afterDatasetsDraw(chart: Chart) {
		const meta = chart.getDatasetMeta(0);
		const data = chart.data.datasets[0].data as number[];

		chart.ctx.save();
		chart.ctx.fillStyle = COLORS.text;
		chart.ctx.font = `700 11px ${Chart.defaults.font.family}`;
		chart.ctx.textAlign = horizontal ? "left" : "center";
		chart.ctx.textBaseline = "middle";

		meta.data.forEach((element, index) => {
			const value = data[index];
			if (value == null || value === 0) return;
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

function renderRevenueChart(points: DashboardPoint[], total: number) {
	setChartTotal("chartRevenue", currencyFormat.format(total), "Summe im Zeitraum");
	lineChart("chartRevenue", points, COLORS.ink, v => currencyFormat.format(v));
}

function renderOrdersChart(points: DashboardPoint[], total: number) {
	setChartTotal("chartOrders", numberFormat.format(total), "Aufträge im Zeitraum");
	barChart(
		"chartOrders",
		points.map(p => monthLabel(p.date)),
		points.map(p => p.value ?? 0),
		COLORS.inkLight,
		v => numberFormat.format(v),
		i => `${numberFormat.format(points[i].value ?? 0)} Aufträge`,
	);
}

function renderPaymentDurationChart(points: DashboardPoint[], kpis: Dashboard["kpis"]) {
	const hasDuration = kpis.avgPaymentDuration != null && kpis.avgPaymentDurationSampleSize > 0;
	setChartTotal(
		"chartPaymentDuration",
		hasDuration ? `Ø ${numberFormat.format(kpis.avgPaymentDuration as number)} Tage` : "–",
		`aus ${plural(kpis.avgPaymentDurationSampleSize, "Zahlung", "Zahlungen")}`,
	);
	// Säulen statt Linie: Monate ohne Zahlung haben keinen Wert und bleiben als Lücke sichtbar
	barChart(
		"chartPaymentDuration",
		points.map(p => monthLabel(p.date)),
		points.map(p => p.value ?? 0),
		COLORS.inkHover,
		v => `${numberFormat.format(v)} Tage`,
		i => (points[i].value == null ? "keine Zahlungen" : `Ø ${numberFormat.format(points[i].value as number)} Tage`),
	);
}

function renderAgingChart(buckets: AgingEntry[], kpis: Dashboard["kpis"]) {
	setChartTotal("chartAging", currencyFormat.format(kpis.openInvoiceAmount), plural(kpis.openInvoiceCount, "Rechnung", "Rechnungen"));
	barChart(
		"chartAging",
		buckets.map(b => b.label),
		buckets.map(b => b.amount),
		buckets.map(b => AGING_COLORS[b.bucket] ?? COLORS.ink),
		v => currencyFormat.format(v),
		i => `${currencyFormat.format(buckets[i].amount)} (${plural(buckets[i].count, "Rechnung", "Rechnungen")})`,
	);
}

function renderPipelineChart(pipeline: PipelineEntry[]) {
	const total = pipeline.reduce((sum, p) => sum + p.value, 0);
	setChartTotal("chartPipeline", numberFormat.format(total), "Aufträge im Zeitraum");
	barChart(
		"chartPipeline",
		pipeline.map(p => p.label),
		pipeline.map(p => p.value),
		pipeline.map(p => PIPELINE_COLORS[p.status] ?? COLORS.ink),
		v => numberFormat.format(v),
		i => `${numberFormat.format(pipeline[i].value)} Aufträge`,
	);
}

function renderTopCustomersChart(customers: TopCustomer[]) {
	const sorted = [...customers].sort((a, b) => b.value - a.value);
	const total = sorted.reduce((sum, c) => sum + Number(c.value), 0);
	setChartTotal("chartTopCustomers", currencyFormat.format(total), `Summe der Top ${sorted.length}`);
	barChart(
		"chartTopCustomers",
		sorted.map(c => c.name),
		sorted.map(c => Number(c.value)),
		COLORS.ink,
		v => currencyFormat.format(v),
		i => `${currencyFormat.format(Number(sorted[i].value))} (${plural(Number(sorted[i].orderCount), "Rechnung", "Rechnungen")})`,
		true,
	);
}

function hexToRgba(hex: string, alpha: number): string {
	const r = parseInt(hex.slice(1, 3), 16);
	const g = parseInt(hex.slice(3, 5), 16);
	const b = parseInt(hex.slice(5, 7), 16);
	return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

loader(init);
