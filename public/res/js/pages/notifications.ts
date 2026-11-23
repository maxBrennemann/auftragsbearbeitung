import { addBindings } from "js-classes/bindings";

import { loader } from "../classes/helpers";
import { markAllNotificationsRead, setNotificationAsRead } from "../classes/notificationUpdater";
import { FunctionMap } from "../types/types";

const fnNames = {} as FunctionMap;

const filters = {
	status: "all",
	category: "all",
};

const getRows = () => Array.from(document.querySelectorAll<HTMLElement>("[data-notification-row]"));

const applyFilters = () => {
	let visible = 0;

	getRows().forEach(row => {
		const statusMatches = filters.status === "all" || row.dataset.status === filters.status;
		const categoryMatches = filters.category === "all" || row.dataset.category === filters.category;
		const show = statusMatches && categoryMatches;
		row.classList.toggle("hidden", !show);
		if (show) {
			visible++;
		}
	});

	const visibleCount = document.getElementById("notificationVisibleCount");
	if (visibleCount) {
		visibleCount.textContent = String(visible);
	}

	const emptyHint = document.getElementById("notificationEmptyFilter");
	if (emptyHint) {
		emptyHint.classList.toggle("hidden", visible !== 0 || getRows().length === 0);
	}
};

const setActiveChip = (selector: string, activeValue: string, valueKey: string) => {
	document.querySelectorAll<HTMLButtonElement>(selector).forEach(button => {
		const active = button.dataset[valueKey] === activeValue;
		button.setAttribute("aria-pressed", active ? "true" : "false");
		button.classList.toggle("btn-primary", active);
		button.classList.toggle("btn-secondary", !active);
	});
};

fnNames.click_filterStatus = (event: Event) => {
	const button = event.currentTarget as HTMLElement;
	filters.status = button.dataset.filterStatus ?? "all";
	setActiveChip("[data-filter-status]", filters.status, "filterStatus");
	applyFilters();
};

fnNames.click_filterCategory = (event: Event) => {
	const button = event.currentTarget as HTMLElement;
	filters.category = button.dataset.filterCategory ?? "all";
	setActiveChip("[data-filter-category]", filters.category, "filterCategory");
	applyFilters();
};

const updateUnreadCount = (delta: number) => {
	const counter = document.getElementById("notificationUnreadCount");
	if (!counter) {
		return;
	}

	const next = Math.max(0, (parseInt(counter.textContent ?? "0", 10) || 0) + delta);
	counter.textContent = String(next);

	const markAll = document.querySelector<HTMLButtonElement>('[data-fun="markAllRead"]');
	if (markAll) {
		markAll.disabled = next === 0;
	}
};

fnNames.click_markRead = async (event: Event) => {
	const button = event.currentTarget as HTMLButtonElement;
	const id = button.dataset.id;
	if (!id) {
		return;
	}

	button.disabled = true;
	await setNotificationAsRead(id);

	const row = button.closest<HTMLElement>("[data-notification-row]");
	if (row) {
		row.dataset.status = "read";
		row.classList.add("opacity-60");
		row.querySelector("[data-unread-marker]")?.remove();
	}

	button.remove();
	updateUnreadCount(-1);
	applyFilters();
};

fnNames.click_markAllRead = async () => {
	await markAllNotificationsRead();
	window.location.reload();
};

loader(() => {
	addBindings(fnNames);
});
