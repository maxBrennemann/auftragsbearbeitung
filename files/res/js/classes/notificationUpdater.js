export const initEventSource = () => {
    const eventSource = new EventSource("/events");
    eventSource.onmessage = e => {
        const data = JSON.parse(e.data);
        console.log(data);
    }
}

export const manageNotificationsGlobally = () => {

}

const generateId = (length = 8) => {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    return Array.from({ length }, () => chars[Math.floor(Math.random() * chars.length)]).join('');
}

const saveNotification = (message, type = "info") => {
    const id = generateId();
    const key = `_notification_${id}`;
    const notification = { message, type, timeout };
    localStorage.setItem(key, JSON.stringify(notification));
}

const readNotifications = () => {
    const keys = Object.keys(localStorage).filter(k => k.startsWith("_notification_"));
    const notifications = [];

    for (const key of keys) {
        try {
            const data = JSON.parse(localStorage.getItem(key));
            notifications.push(data);
        } catch (e) {
            console.warn(`Failed to pare notification from key: ${key}`);
            localStorage.removeItem(key);
        }
    }

    return notifications;
}
