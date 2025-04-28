import { ajax } from "./classes/ajax.js";

class Diagram {

	constructor() {
		this.data = [];
		this.canvas = null;
		this.startDate = null;
		this.endDate = null;
		this.dimension = null;
		this.datatype = null;
		this.diagramType = null;
		this.chart = null;
	}

	addChart() {
		this.canvas = document.getElementById('diagram');
	}

	async getData() {
		this.data = await ajax.post({
			r: 'diagramme',
			startDate: this.startDate,
			endDate: this.endDate,
			dimension: this.dimension,
			datatype: this.datatype,
			diagramType: this.diagramType,
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
					label: this.diagramType,
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
	initListeners();

	const startDate = document.getElementById('startDate');
	const endDate = document.getElementById('endDate');

	const today = new Date();
	const dd = String(today.getDate()).padStart(2, '0');
	const mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
	const yyyy = today.getFullYear();

	startDate.value = (yyyy - 1) + '-' + mm + '-' + dd;
	endDate.value = yyyy + '-' + mm + '-' + dd;

	diagram.startDate = startDate.value;
	diagram.endDate = endDate.value;
	diagram.diagramType = document.getElementById('diagramType').value;

	diagram.updateChart();
}

if (document.readyState !== 'loading' ) {
    initCode();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        initCode();
    });
}

function initListeners() {
	document.getElementById('startDate').addEventListener('change', e => {
		const date = e.target.value;
		diagram.startDate = date;
		diagram.updateChart();
	});

	document.getElementById('endDate').addEventListener('change', e => {
		const date = e.target.value;
		diagram.endDate = date;
		diagram.updateChart();
	});

	document.getElementById('dimension').addEventListener('change', e => {
		const dimension = e.target.value;
		diagram.dimension = dimension;
		diagram.updateChart();
	});

	document.getElementById('datatype').addEventListener('change', e => {
		const datatype = e.target.value;
		diagram.datatype = datatype;
		diagram.updateChart();
	});

	document.getElementById('diagramType').addEventListener('change', e => {
		const diagramType = e.target.value;
		diagram.diagramType = diagramType;
		diagram.updateChart();
	});
}
