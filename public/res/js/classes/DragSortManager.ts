interface DragSortOptions {
    itemSelector?: string;
    handleSelector?: string;
    ignoreSelector?: string;
    dataFields?: string[];
    onOrderChange?: (
        positions: Array<Record<string, string | number>>,
        group: HTMLElement,
    ) => void;
}

export class DragSortManager {
    private group: HTMLElement;
    private options: DragSortOptions;
    private currentDraggedElement: HTMLElement | null = null;

    constructor(group: HTMLElement, options: DragSortOptions = {}) {
        this.group = group;
        this.options = options;

        this.init();
    }

    private items(): HTMLElement[] {
        const selector = this.options.itemSelector || "li";
        const ignore = this.options.ignoreSelector;

        const nodes = Array.from(this.group.querySelectorAll<HTMLElement>(selector));
        if (!ignore) return nodes;

        return nodes.filter(node => !node.matches(ignore));
    }

    private init() {
        const items = this.items().forEach(item => this.bindDragEvents(item));
    }

    private bindDragEvents(element: HTMLElement) {
        element.setAttribute("draggable", "true");

        const handleSelector = this.options.handleSelector;
        if (handleSelector) {
            element.addEventListener("dragstart", (e) => {
                const target = e.target as HTMLElement | null;
                if (!target || !target.closest(handleSelector)) {
                    e.preventDefault();
                    return;
                }
                this.handleDragStart(e);
            });
        } else {
            element.addEventListener("dragstart", (e) => this.handleDragStart(e));
        }

        element.addEventListener("dragover", (e) => this.handleDragOver(e));
        element.addEventListener("dragenter", (e) => this.handleDragEnter(e));
        element.addEventListener("dragleave", (e) => this.handleDragLeave(e));
        element.addEventListener("drop", (e) => this.handleDrop(e));
        element.addEventListener("dragend", (e) => this.handleDragEnd(e));
    }

    private handleDragStart(e: DragEvent) {
        const target = e.currentTarget as HTMLElement;
        this.currentDraggedElement = target;
        target.classList.add("opacity-50");
    }

    private handleDragOver(e: DragEvent) {
        e.preventDefault();
        if (!this.currentDraggedElement) return;

        const target = e.currentTarget as HTMLElement;
        const bounding = target.getBoundingClientRect();
        const offset = e.clientY - bounding.top;

        if (target.parentElement !== this.group || target === this.currentDraggedElement) return;

        if (offset < bounding.height / 2 && target.previousElementSibling === this.currentDraggedElement) return;
        if (offset >= bounding.height / 2 && target.nextElementSibling === this.currentDraggedElement) return;

        if (offset < bounding.height / 2) {
            target.before(this.currentDraggedElement);
        } else {
            target.after(this.currentDraggedElement);
        }
    }

    private handleDragEnter(e: DragEvent) {
        e.preventDefault();
        (e.currentTarget as HTMLElement).classList.add("ring-2", "ring-blue-400", "bg-blue-50");
    }

    private handleDragLeave(e: DragEvent) {
        (e.currentTarget as HTMLElement).classList.remove("ring-2", "ring-blue-400", "bg-blue-50");
    }

    private handleDrop(e: DragEvent) {
        e.preventDefault();
        if (!this.currentDraggedElement) return;

        this.handleDragLeave(e);
        this.currentDraggedElement.classList.remove("opacity-50");
        this.updatePositions();
    }

    private handleDragEnd(e: DragEvent) {
        const target = e.currentTarget as HTMLElement;
        target.classList.remove("opacity-50");

        const items = this.group.querySelectorAll<HTMLElement>(this.options.itemSelector || "li");
        items.forEach(item => item.classList.remove("ring-2", "ring-blue-400", "bg-blue-50"));
    }

    private updatePositions() {
        const items = this.items();
        const fields = this.options.dataFields;

        const positions = items.map((el, idx) => {
            const data: Record<string, string | number> = {
                id: el.dataset.id ?? "",
                position: idx + 1,
            };

            if (Array.isArray(fields)) {
                fields.forEach(field => {
                    data[field] = el.dataset[field] ?? "";
                });
            }

            return data;
        });

        if (typeof this.options.onOrderChange === "function") {
            this.options.onOrderChange(positions, this.group);
        }
    }

    public bindNewItem(element: HTMLElement) {
        this.bindDragEvents(element);
    }
}
