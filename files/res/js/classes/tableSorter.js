export var currentTableSorter;

export function setTableSorter(sorter) {
    currentTableSorter = sorter;
}

/* https://stackoverflow.com/questions/14267781/sorting-html-table-with-javascript */
export class TableSorter {

	constructor() {
		this.url = window.location.href;
		this.settings = this.getSortSettings();
	}

	saveSortSettings(sortDirection, sortedColumn, tableNumber) {
		if (this.settings == null) {
			this.settings = {
	
			}
		}
	
		this.settings[tableNumber] = {
			sortDirection: sortDirection,
			sortedColumn: sortedColumn,
		}
	
		localStorage.setItem(this.url, JSON.stringify(this.settings));
	}

	get(tableIndex) {
		if (this.settings[tableIndex]) {
			return this.settings[tableIndex];
		} else {
			this.saveSortSettings("asc", 0, tableIndex);
			return this.getSortSettings();
		}
	}

	getSortSettings() {
		this.settings = JSON.parse(localStorage.getItem(this.url));
		if (this.settings == null) {
			this.settings = {};
		}
		return this.settings;
	}

	readTableSorted() {
		const tables = document.querySelectorAll("table");
	
		if (this.settings == null)
			return;
	
		for (const [key, value] of Object.entries(this.settings)) {
			const table = tables[key];
	
			if (table != undefined) {
				const ths = table.querySelectorAll("th");
				const th = ths[value.sortedColumn];
				const sort = value.sortDirection == "asc";

				this.sortColumn(table, th, sort);
			}
		}
	}

	sortColumn(table, th, sort) {
		Array.from(table.querySelectorAll('tr:nth-child(n+2)'))
		.sort(this.comparer(Array.from(th.parentNode.children).indexOf(th), sort))
		.forEach(tr => table.appendChild(tr));
	
		let tr = th.closest('tr');
		Array.from(tr.children).forEach(element => {
			if (element != th) {
				element.style.backgroundColor = "";
			} else {
				element.style.backgroundColor = "#005999";
				let sortIcon = element.querySelector("span");
				
				if (sort) {
					sortIcon.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="inline" viewBox="0 0 24 24" style="width: 12px; height 12px"><title>Absteigend sortieren</title><path d="M19 7H22L18 3L14 7H17V21H19M2 17H12V19H2M6 5V7H2V5M2 11H9V13H2V11Z" fill="white" /></svg>`;
				} else {
					sortIcon.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="inline" viewBox="0 0 24 24" style="width: 12px; height 12px"><title>Aufsteigend sortieren</title><path d="M19 17H22L18 21L14 17H17V3H19M2 17H12V19H2M6 5V7H2V5M2 11H9V13H2V11Z" fill="white" /></svg>`;
				}
			}
		});
	
		const sortedColumn = Array.from(tr.children).indexOf(th);
		const tableNumber = Array.from(document.querySelectorAll("table")).indexOf(table);
		/* turn sorting direction on click */
		const sortDirection = sort ? "asc" : "desc";
		this.saveSortSettings(sortDirection, sortedColumn, tableNumber);
	}

	sort(e) {
		const th = e.target;
		const table = th.closest('table');

		const tableIndex = Array.from(document.querySelectorAll("table")).indexOf(table);
		const sort = this.get(tableIndex).sortDirection != "asc";

		this.sortColumn(table, th, sort);
	}

	getCellValue = (tr, idx) => tr.children[idx].innerText || tr.children[idx].textContent;

	comparer = (idx, asc) => (a, b) => ((v1, v2) =>
        v1 !== '' && v2 !== '' && !isNaN(v1) && !isNaN(v2) ? v1 - v2 : v1.toString().localeCompare(v2))(this.getCellValue(asc ? a : b, idx), this.getCellValue(asc ? b : a, idx));
}

function sortTableNew(e) {
	currentTableSorter.sort(e);
} 
