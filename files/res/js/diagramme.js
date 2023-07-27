import { ajax } from "./classes/ajax.js";
import { initBindings } from "./classes/bindings.js";

class Diagram {

	constructor() {
		this.data = [];
		this.canvas = null;
		this.startDate = null;
		this.endDate = null;
		this.dimension = null;
		this.datatype = "getOrders";
		this.chart = null;
	}

	addChart() {
		this.canvas = document.getElementById('diagram');
	}

	setStartDate(date) {
		this.startDate = date;
	}

	setEndDate(date) {
		this.endDate = date;
	}

	setDimension(dimension) {
		this.dimension = dimension;
	}

	setDatatype(datatype) {
		this.datatype = datatype;
	}

	async getData() {
		this.data = await ajax.post({
			r: 'diagramme',
			function: this.datatype,
			startDate: this.startDate,
			endDate: this.endDate,
			dimension: this.dimension,
			datatype: this.datatype
		});
	}

	async updateChart() {
		await this.getData();
		if (this.chart) {
			this.chart.destroy();
		}

		this.chart = new Chart(this.canvas, {
			type: 'line',
			data: {
				labels: this.data.map((item) => item.date),
				datasets: [{
					label: this.dimension,
					data: this.data.map((item) => item.value),
					backgroundColor: 'rgba(0, 0, 0, 0)',
					borderColor: 'rgba(0, 0, 0, 1)',
					borderWidth: 1
				}]
			}
		});
	}

}

const diagram = new Diagram();

function initCode() {
	diagram.addChart();
	initBindings(fnNames);

	const startDate = document.getElementById('startDate');
	const endDate = document.getElementById('endDate');

	const today = new Date();
	const dd = String(today.getDate()).padStart(2, '0');
	const mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
	const yyyy = today.getFullYear();

	startDate.value = (yyyy - 1) + '-' + mm + '-' + dd;
	endDate.value = yyyy + '-' + mm + '-' + dd;

	diagram.setStartDate(startDate.value);
	diagram.setEndDate(endDate.value);
	diagram.updateChart();
}

function changeStartDate() {
	const value = document.getElementById('startDate').value;
	diagram.setStartDate(value);
	diagram.updateChart();
}

function changeEndDate() {
	const value = document.getElementById('endDate').value;
	diagram.setEndDate(value);
	diagram.updateChart();
}

function setDimension() {
	const value = document.getElementById('dimension').value;
	diagram.setDimension(value);
	diagram.updateChart();
}

function setDatatype() {
	const value = document.getElementById('datatype').value;
	diagram.setDatatype(value);
	diagram.updateChart();
}

const fnNames = {
	write_changeStartDate: changeStartDate,
	write_changeEndDate: changeEndDate,
	write_setDimension: setDimension,
	write_setDatatype: setDatatype
};

if (document.readyState !== 'loading' ) {
    initCode();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        initCode();
    });
}
