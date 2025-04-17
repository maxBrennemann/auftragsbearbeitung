const BindingManager = (function () {
    let instance;

    class BindingManager {
        constructor() {
            if (instance) return instance;

            this.fnNames = {};
            this.boundElements = new WeakSet();
            this.variables = {};

            this._bindToggle();

            instance = this;
        }

        initBindings(fnNames) {
            this.fnNames = fnNames;

            document.querySelectorAll('[data-binding]').forEach(el => {
                this._addBinding(el);
            });

            document.querySelectorAll('[data-variable]').forEach(el => {
                if (el.id) {
                    this.variables[el.id] = el;
                }
            });

            document.querySelectorAll('[data-write]').forEach(el => {
                this._addWriteBinding(el);
            });
        }

        addBindings(fnNames) {
            this.fnNames = { ...this.fnNames, ...fnNames };

            document.querySelectorAll('[data-binding]').forEach(el => {
                this._addBinding(el);
            });
        }

        _addBinding(el) {
            if (this.boundElements.has(el)) return;

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

            this.boundElements.add(el);
        }

        _addWriteBinding(el) {
            if (this.boundElements.has(el)) return;

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

            this.boundElements.add(el);
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

        getVariable(id) {
            return this.variables[id];
        }
    }

    return new BindingManager();
})();

export const initBindings = fnNames => BindingManager.initBindings(fnNames);
export const addBindings = fnNames => BindingManager.addBindings(fnNames);
export const getVariable = id => BindingManager.getVariable(id);
