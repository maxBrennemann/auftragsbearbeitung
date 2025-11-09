//@ts-nocheck
const NotificationManager = (function () {
    let instance;

    class NotificationManager {
        #notificationKeys = new Set();
        #notificationElements = new Map();
        #twPrefix = "";
        #isPersistant = false;
        #storageKey = "notifications";

        constructor() {
            if (instance) return instance;
            instance = this;
        }

        setTwPrefix(twPrefix) {
            this.#twPrefix = twPrefix;
        }

        setPersistance(isPersistant) {
            this.#isPersistant = isPersistant;
            if (isPersistant) {
                this.#loadStoredNotifications();
            } else {
                localStorage.removeItem(this.#storageKey);
            }
        }

        notify(info, type, details, duration, onClose, persist, id) {
            const key = JSON.stringify({ info, type, details });
            if (!this.#notificationKeys.has(key)) {
                this.#notificationKeys.add(key);
            }

            const notification = this.notificationHTML(info, type);

            if (notification == null) {
                return;
            }

            const template = document.createElement("template");
            template.innerHTML = notification;

            const element = template.content.firstElementChild;
            const notificationContainer = this.#getNotificationContainer();
            notificationContainer.appendChild(element);

            const removeNotificationHandler = () => {
                this.#removeNotification(element, onClose, type, details);
            };

            const shouldAutoDismiss = type !== "loading" && type !== "failure" && !persist;
            if (shouldAutoDismiss) {
                const timeout = type === "failure" ? 20000 : duration;
                setTimeout(removeNotificationHandler, timeout);
            }

            const removeBtn = element.querySelector(".removeBtn");
            if (removeBtn) {
                removeBtn.addEventListener("click", removeNotificationHandler);
            }

            const copySpan = element.querySelector(".copyBtn");
            if (copySpan) {
                const btn = this.#getCopyBtn(details);
                if (btn) {
                    copySpan.appendChild(btn);
                }
            }

            if (id) {
                this.#notificationElements.set(id, element);
            }

            if (this.#isPersistant) {
                this.#storeNotification(info, type, details, duration, onClose, persist, id);
            }
        }

        replace = (id, info, type, details, duration, onClose, persist) => {
            const old = this.#notificationElements.get(id);
            if (!old) return;

            old.classList.add(`${this.#twPrefix}opacity-0`, `${this.#twPrefix}transition-opacity`);
            setTimeout(() => {
                old?.remove();
            }, 300);

            this.notify(info, type, details, duration, onClose, persist);
        }

        notificationHTML = (info, type) => {
            switch (type) {
                case "success":
                    return this.#notificationHTMLSuccess(info);
                case "warning":
                    return this.#notificationHTMLWarning(info);
                case "failure":
                    return this.#notificationHTMLFailure(info);
                case "loading":
                    return this.#notificationHTMLLoading(info);
                default:
                    return null;
            }
        }

        #storeNotification = (info, type, details, duration, onClose, persist, id) => {
            const newNotification = {
                id: id || crypto.randomUUID(),
                info: info,
                type: type,
                details: details,
                duration: duration,
                onClose: onClose,
                persist: persist,
                id: id,
            }

            const notifications = JSON.parse(localStorage.getItem(this.#storageKey) || "[]");

            const existingIndex = notifications.findIndex(n => n.id === newNotification.id);
            if (existingIndex >= 0) notifications[existingIndex] = newNotification;
            else notifications.push(newNotification);

            localStorage.setItem(this.#storageKey, JSON.stringify(notifications));
        }

        #removeNotification = (element, onClose, type, details) => {
            element?.classList.add(`${this.#twPrefix}opacity-0`, `${this.#twPrefix}transition-opacity`);
            setTimeout(() => {
                element?.remove();

                if (type == "failure") {
                    console.error(details);
                }

                if (type == "warning") {
                    console.warn(details);
                }

                if (typeof onClose === "function") {
                    onClose();
                }
            }, 300);

            const id = [...this.#notificationElements.entries()]
                .find(([_, el]) => el === element)?.[0];

            if (id) this.#removeStoredNotification(id);
        }

        #removeStoredNotification(id) {
            const notifications = JSON.parse(localStorage.getItem(this.#storageKey) || "[]");
            const filtered = notifications.filter(n => n.id !== id);
            localStorage.setItem(this.#storageKey, JSON.stringify(filtered));
        }

        #loadStoredNotifications() {
            const notifications = JSON.parse(localStorage.getItem(this.#storageKey) || "[]");
            const now = Date.now();

            notifications.forEach(n => {
                // skip expired auto-dismissable
                if (!n.persist && n.duration && now - n.timestamp > n.duration) return;

                this.notify(n.info, n.type, n.details, n.duration, () => {
                    this.#removeStoredNotification(n.id);
                }, n.persist, n.id);
            });
        }

        #getNotificationContainer = () => {
            let container = document.querySelector("#notificationContainer");
            if (!container) {
                container = document.createElement("div");
                container.id = "notificationContainer";
                container.className = `${this.#twPrefix}fixed ${this.#twPrefix}right-0 ${this.#twPrefix}bottom-3 ${this.#twPrefix}flex ${this.#twPrefix}flex-col-reverse ${this.#twPrefix}gap-2 ${this.#twPrefix}z-50 ${this.#twPrefix}h-3/6 ${this.#twPrefix}overflow-y-scroll ${this.#twPrefix}py-3 ${this.#twPrefix}pr-3 scrollbar-hide`;
                document.body.appendChild(container);
            }
            return container;
        }

        #notificationHTMLSuccess = (info) => {
            return `
            <div class="${this.#twPrefix}rounded-lg ${this.#twPrefix}flex ${this.#twPrefix}bg-neutral-50 ${this.#twPrefix}shadow-md" role="alert" aria-live="polite" tabindex="0">
                <div class="${this.#twPrefix}bg-green-500 ${this.#twPrefix}w-3 ${this.#twPrefix}rounded-l-lg"></div>
                <div class="${this.#twPrefix}p-2 ${this.#twPrefix}flex">
                    <div class="${this.#twPrefix}flex ${this.#twPrefix}flex-row ${this.#twPrefix}items-center ${this.#twPrefix}mr-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="${this.#twPrefix}fill-green-500 ${this.#twPrefix}h-5 ${this.#twPrefix}w-5" viewBox="0 0 24 24">
                            <path d="M12 2C6.5 2 2 6.5 2 12S6.5 22 12 22 22 17.5 22 12 17.5 2 12 2M10 17L5 12L6.41 10.59L10 14.17L17.59 6.58L19 8L10 17Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="${this.#twPrefix}font-sans ${this.#twPrefix}font-semibold ${this.#twPrefix}text-base">Gespeichert</p>
                        <p class="${this.#twPrefix}font-sans ${this.#twPrefix}text-xs ${this.#twPrefix}text-gray-600 ${this.#twPrefix}flex ${this.#twPrefix}items-center">${info}<span class="${this.#twPrefix}ml-2 copyBtn"></span></p>
                    </div>
                    <div class="${this.#twPrefix}inline-flex ${this.#twPrefix}items-center ${this.#twPrefix}pl-2">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="removeBtn ${this.#twPrefix}fill-gray-600 ${this.#twPrefix}h-3 ${this.#twPrefix}w-3 ${this.#twPrefix}cursor-pointer"  title="Schließen">
                            <title>Schließen</title>
                            <path d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z"  title="Schließen" />
                        </svg>
                    </div>
                </div>
            </div>`;
        }

        #notificationHTMLFailure = (info) => {
            return `
            <div class="${this.#twPrefix}rounded-lg ${this.#twPrefix}flex ${this.#twPrefix}bg-neutral-50 ${this.#twPrefix}shadow-md" role="alert" aria-live="polite" tabindex="0">
                <div class="${this.#twPrefix}bg-red-500 ${this.#twPrefix}w-3 ${this.#twPrefix}rounded-l-lg"></div>
                <div class="${this.#twPrefix}p-2 ${this.#twPrefix}flex">
                    <div class="${this.#twPrefix}flex ${this.#twPrefix}flex-row ${this.#twPrefix}items-center ${this.#twPrefix}mr-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="${this.#twPrefix}fill-red-500 ${this.#twPrefix}h-5 ${this.#twPrefix}w-5" viewBox="0 0 24 24">
                            <path d="M13,13H11V7H13M13,17H11V15H13M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="${this.#twPrefix}font-sans ${this.#twPrefix}font-semibold ${this.#twPrefix}text-base">Fehler</p>
                         <p class="${this.#twPrefix}font-sans ${this.#twPrefix}text-xs ${this.#twPrefix}text-gray-600 ${this.#twPrefix}flex ${this.#twPrefix}items-center">${info}<span class="${this.#twPrefix}ml-2 copyBtn"></span></p>
                    </div>
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="removeBtn ${this.#twPrefix}fill-gray-600 ${this.#twPrefix}h-3 ${this.#twPrefix}w-3 ${this.#twPrefix}cursor-pointer">
                            <path d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z" />
                        </svg>
                    </div>
                </div>
            </div>`;
        }

        #notificationHTMLWarning = (info) => {
            return `
            <div class="${this.#twPrefix}rounded-lg ${this.#twPrefix}flex ${this.#twPrefix}bg-neutral-50 ${this.#twPrefix}shadow-md" role="alert" aria-live="polite" tabindex="0">
                <div class="${this.#twPrefix}bg-orange-500 ${this.#twPrefix}w-3 ${this.#twPrefix}rounded-l-lg"></div>
                <div class="${this.#twPrefix}p-2 ${this.#twPrefix}flex">
                    <div class="${this.#twPrefix}flex ${this.#twPrefix}flex-row ${this.#twPrefix}items-center ${this.#twPrefix}mr-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="${this.#twPrefix}fill-orange-500 ${this.#twPrefix}h-5 ${this.#twPrefix}w-5" viewBox="0 0 24 24">
                            <path d="M13 14H11V9H13M13 18H11V16H13M1 21H23L12 2L1 21Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="${this.#twPrefix}font-sans ${this.#twPrefix}font-semibold ${this.#twPrefix}text-base">Fehler</p>
                         <p class="${this.#twPrefix}font-sans ${this.#twPrefix}text-xs ${this.#twPrefix}text-gray-600 ${this.#twPrefix}flex ${this.#twPrefix}items-center">${info}<span class="${this.#twPrefix}ml-2 copyBtn"></span></p>
                    </div>
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="removeBtn ${this.#twPrefix}fill-gray-600 ${this.#twPrefix}h-3 ${this.#twPrefix}w-3 ${this.#twPrefix}cursor-pointer">
                            <path d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z" />
                        </svg>
                    </div>
                </div>
            </div>`;
        }

        #notificationHTMLLoading = (info) => {
            return `
            <div class="${this.#twPrefix}rounded-lg ${this.#twPrefix}flex ${this.#twPrefix}bg-neutral-50 ${this.#twPrefix}shadow-md" role="alert" aria-live="polite" tabindex="0">
                <div class="${this.#twPrefix}bg-cyan-500 ${this.#twPrefix}w-3 ${this.#twPrefix}rounded-l-lg"></div>
                <div class="${this.#twPrefix}p-2 ${this.#twPrefix}flex">
                    <div class="${this.#twPrefix}flex ${this.#twPrefix}flex-row ${this.#twPrefix}items-center ${this.#twPrefix}mr-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="${this.#twPrefix}fill-cyan-500 ${this.#twPrefix}h-5 ${this.#twPrefix}w-5" viewBox="0 0 24 24">
                            <path d="M12,1A11,11,0,1,0,23,12,11,11,0,0,0,12,1Zm0,19a8,8,0,1,1,8-8A8,8,0,0,1,12,20Z" opacity=".25"/>
                            <path d="M10.14,1.16a11,11,0,0,0-9,8.92A1.59,1.59,0,0,0,2.46,12,1.52,1.52,0,0,0,4.11,10.7a8,8,0,0,1,6.66-6.61A1.42,1.42,0,0,0,12,2.69h0A1.57,1.57,0,0,0,10.14,1.16Z">
                                <animateTransform attributeName="transform" type="rotate" dur="0.75s" values="0 12 12;360 12 12" repeatCount="indefinite"/>
                            </path>
                        </svg>
                    </div>
                    <div>
                        <p class="${this.#twPrefix}font-sans ${this.#twPrefix}font-semibold ${this.#twPrefix}text-base">Lädt...</p>
                        <p class="${this.#twPrefix}font-sans ${this.#twPrefix}text-xs ${this.#twPrefix}text-gray-600 ${this.#twPrefix}flex ${this.#twPrefix}items-center">${info}</p>
                    </div>
                    <div class="${this.#twPrefix}inline-flex ${this.#twPrefix}items-center ${this.#twPrefix}pl-2">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="removeBtn ${this.#twPrefix}fill-gray-600 ${this.#twPrefix}h-3 ${this.#twPrefix}w-3 ${this.#twPrefix}cursor-pointer">
                            <path d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z" />
                        </svg>
                    </div>
                </div>
            </div>`;
        }

        #getCopyBtn = (details) => {
            if (details == "") {
                return null;
            }

            const copyContent = document.createElement("input");
            copyContent.value = details;
            copyContent.classList.add(`${this.#twPrefix}hidden`);

            const copyBtn = document.createElement("button");
            copyBtn.className = `${this.#twPrefix}border-none ${this.#twPrefix}flex ${this.#twPrefix}items-center`;
            copyBtn.addEventListener("click", () => {
                copyContent.select();
                copyContent.setSelectionRange(0, 99999);
                navigator.clipboard.writeText(copyContent.value);
            });
            copyBtn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="9px" height="9px" class="${this.#twPrefix}fill-gray-600"><title>content-copy</title><path d="M19,21H8V7H19M19,5H8A2,2 0 0,0 6,7V21A2,2 0 0,0 8,23H19A2,2 0 0,0 21,21V7A2,2 0 0,0 19,5M16,1H4A2,2 0 0,0 2,3V17H4V3H16V1Z" /></svg>`;

            return copyBtn;
        }
    }

    return new NotificationManager();
})();

export const setTwPrefix = (twPrefix) => {
    NotificationManager.setTwPrefix(twPrefix);
}

export const setNotificationPersistance = (isPersistant = true) => {
    NotificationManager.setPersistance(isPersistant);
}

export const notification = (info, type = "warning", details = "", duration = 5000, onClose = () => { }, persist = false) => {
    NotificationManager.notify(info, type, details, duration, onClose, persist, null);
}

export const notificationLoader = (id, info, details, onClose = () => { }) => {
    NotificationManager.notify(info, "loading", details, 0, onClose, true, id);
}

export const notificatinReplace = (id, info, type, details = "", duration = 5000, onClose = () => { }, persist = false) => {
    NotificationManager.replace(id, info, type, details, duration, onClose, persist);
}
