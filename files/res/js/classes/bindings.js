const BindingManager = (function () {
    let instance;

    class BindingManager {
        constructor() {
            if (instance) return instance;

            this.fnNames = {};
            this.boundElements = new WeakMap();
            this.variables = {};

            this._bindToggle();

            instance = this;
        }

        addBindings(fnNames) {
            this.fnNames = { ...this.fnNames, ...fnNames };

            document.querySelectorAll('[data-binding]').forEach(el => {
                this._addBinding(el);
            });

            document.querySelectorAll('[data-variable]').forEach(el => {
                if (el.id) {
                    this.variables[el.id] = el.value || el.innerHTML;
                }
            });

            document.querySelectorAll('[data-write]').forEach(el => {
                this._addWriteBinding(el);
            });

            document.querySelectorAll('[data-input]').forEach(el => {
                this._addInputBinding(el);
            });
        }

        _addBinding(el) {
            if (this._isAlreadyBound(el, "click")) return;

            const funName = el.dataset.fun
                ? `click_${el.dataset.fun}`
                : el.id
                    ? `click_${el.id}`
                    : null;

            if (!funName) return;

            el.addEventListener("click", e => {
                const fn = this.fnNames[funName];
                if (typeof fn === "function") {
                    fn(e);
                } else {
                    console.warn(`Click handler not defined for "${funName}"`);
                }
            });

            this._markAsBound(el, "click");
        }

        _addWriteBinding(el) {
            if (this._isAlreadyBound(el, "write")) return;

            const funName = el.dataset.fun
                ? `write_${el.dataset.fun}`
                : `write_${el.id}`;

            el.addEventListener("change", e => {
                const fn = this.fnNames[funName];
                if (typeof fn === "function") {
                    fn(e);
                } else {
                    console.warn(`Write handler not defined for "${funName}"`);
                }
            });

            this._markAsBound(el, "write");
        }

        _addInputBinding(el) {
            if (this._isAlreadyBound(el, "input")) return;

            const funName = el.dataset.fun
                ? `input_${el.dataset.fun}`
                : `input_${el.id}`;

            el.addEventListener("input", e => {
                const fn = this.fnNames[funName];
                if (typeof fn === "function") {
                    fn(e);
                } else {
                    console.warn(`Write handler not defined for "${funName}"`);
                }
            });

            this._markAsBound(el, "input");
        }

        _bindToggle() {
            document.querySelectorAll('[data-toggle]').forEach(el => {
                if (this.boundElements.has(el)) return;

                el.addEventListener("click", e => {
                    const target = el.dataset.target;
                    const elements = document.querySelectorAll(target);
                    elements.forEach(element => {
                        element.classList.toggle("hidden");
                    })
                });
            });
        }

        _isAlreadyBound(el, event) {
            const events = this.boundElements.get(el);
            return events?.has(event);
        }

        _markAsBound(el, event) {
            if (!this.boundElements.has(el)) {
                this.boundElements.set(el, new Set());
            }
            this.boundElements.get(el).add(event);
        }

        getVariable(id) {
            return this.variables[id];
        }
    }

    return new BindingManager();
})();

export const addBindings = fnNames => BindingManager.addBindings(fnNames);
export const getVariable = id => BindingManager.getVariable(id);
