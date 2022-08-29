
if (document.readyState !== 'loading' ) {
    executediagram(labels, data);
} else {
    document.addEventListener('DOMContentLoaded', function () {
        executediagram(labels, data);
    });
}

var colors;
var borders;

function executediagram(labels, data) {
	temp_getColors(data);

	var ctx = document.getElementById("showGraph").getContext('2d');
	var myChart = new Chart(ctx, {
		type: 'bar',
		data: {
			labels: labels,
			datasets: [{
				label: 'Umsatz pro Monat (netto)',
				data: data,
				backgroundColor: colors,
				borderColor: borders,
				borderWidth: 1
			}]
		},
		options: {
			scales: {
				yAxes: [{
					ticks: {
						beginAtZero: true
					}
				}]
			}
		}
	});
}

function temp_getColors(data) {
    var data = data;
    colors = [];
    borders =  [];
    var minimum = 0;
    var maximum = 255;
    for (let i = 0; i < data.length; i++) {
        let r = Math.floor(Math.random() * (maximum - minimum + 1)) + minimum;
        let b = Math.floor(Math.random() * (maximum - minimum + 1)) + minimum;
        let g = Math.floor(Math.random() * (maximum - minimum + 1)) + minimum;

        colors.push(`rgba(${r}, ${g}, ${b}, 0.2)`);
        borders.push(`rgba(${r}, ${g}, ${b}, 1.0)`);
    }
}
