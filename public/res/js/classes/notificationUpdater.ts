import { ajax } from "js-classes/ajax";

const BADGE_ID = "notificationBadge";
const EVENTS_URL = "/events";
const FALLBACK_POLL_INTERVAL = 60000;

let eventSource: EventSource | null = null;
let fallbackTimer: number | null = null;

const getBadge = () => document.getElementById(BADGE_ID);

/**
 * Aktualisiert die Zahl am Glocken-Icon. Bei 0 wird das Badge ausgeblendet.
 */
export const updateNotificationBadge = (count: number) => {
	const badge = getBadge();
	if (!badge) {
		return;
	}

	badge.textContent = count > 0 ? String(count) : "";
	badge.style.display = count > 0 ? "" : "none";
};

export const refreshNotificationBadge = async () => {
	const response = await ajax.get("/api/v1/notification/count");
	if (response.success && response.data) {
		updateNotificationBadge(Number(response.data.count) || 0);
	}
};

/**
 * Markiert eine einzelne Benachrichtigung als gelesen und aktualisiert das Badge.
 */
export const setNotificationAsRead = async (id: number | string) => {
	const response = await ajax.put(`/api/v1/notification/${id}/set-read`);
	if (response.success && response.data) {
		updateNotificationBadge(Number(response.data.count) || 0);
	}
};

export const markAllNotificationsRead = async () => {
	const response = await ajax.put("/api/v1/notification/read-all");
	if (response.success && response.data) {
		updateNotificationBadge(Number(response.data.count) || 0);
	}
};

const startFallbackPolling = () => {
	if (fallbackTimer !== null) {
		return;
	}

	fallbackTimer = window.setInterval(refreshNotificationBadge, FALLBACK_POLL_INTERVAL);
};

/**
 * Öffnet den Server-Sent-Events-Stream, der neue Benachrichtigungen pusht.
 * Der Browser verbindet nach dem Schließen durch den Server automatisch neu.
 */
export const manageNotificationsGlobally = () => {
	if (typeof EventSource === "undefined") {
		startFallbackPolling();
		return;
	}

	if (eventSource !== null) {
		return;
	}

	eventSource = new EventSource(EVENTS_URL);

	eventSource.addEventListener("notifications", (event) => {
		try {
			const data = JSON.parse((event as MessageEvent).data);
			updateNotificationBadge(Number(data.count) || 0);
		} catch (error) {
			console.warn("Ungültiges Notification-Event", error);
		}
	});

	eventSource.onerror = () => {
		// EventSource verbindet selbst neu; nur wenn der Browser aufgibt, auf Polling wechseln.
		if (eventSource?.readyState === EventSource.CLOSED) {
			eventSource = null;
			startFallbackPolling();
		}
	};
};

export const initNotificationService = () => {
	// ohne Anmeldung (z. B. Login-Seite) gibt es keine Benachrichtigungen; die API würde nur 401 liefern
	if (document.body.dataset.loggedIn !== "1") {
		return;
	}

	refreshNotificationBadge();

	// Den Stream erst nach dem load-Ereignis öffnen: eine offene EventSource während des Ladens
	// verhindert sonst, dass die Seite je als „geladen“ gilt (Ladesymbol im Tab bleibt stehen).
	if (document.readyState === "complete") {
		manageNotificationsGlobally();
	} else {
		window.addEventListener("load", manageNotificationsGlobally, { once: true });
	}
};
