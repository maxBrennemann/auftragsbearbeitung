import { notification, setNotificationPersistance } from "../classes/notifications.js";

setNotificationPersistance();

notification("Test", "success", "Keine Details", 0, () => {}, true);

notification("Test", "success", "Keine Details", 0, () => {}, true);
