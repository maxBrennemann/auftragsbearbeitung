<div class="defCont grid grid-cols-2">
	<div class="w-96 border border-gray-200 rounded-lg p-2 bg-white">
		<canvas id="ctxDiagram"></canvas>
	</div>
	<div>
		<div>
			<h3>Dimensionen</h3>
			<div id="dimCont"></div>
			<div>
				<select class="input-primary" id="dimSelect">
					<option value="month">Monat</option>
					<option value="year">Jahr</option>
					<option value="ordertype">Auftragstyp</option>
					<option value="customer">Kunde</option>
					<option value="orderstate">Auftragsstatus</option>
				</select>
				<button class="btn-primary" data-fun="addDimension" data-binding="true">Hinzufügen</button>
			</div>
		</div>
		<div>
			<h3>Filter</h3>
			<div id="filterCont"></div>
			<div>
				<select class="input-primary" id="filterSelect">
					<option value="startdate">Startdatum</option>
					<option value="enddate">Enddatum</option>
					<option value="ordertype">Auftragstyp</option>
					<option value="orderstate">Auftragsstatus</option>
					<option value="customer">Kunde</option>
					<option value="volume">Umsatz</option>
					<option value="profit">Gewinn</option>
				</select>
				<button class="btn-primary" data-fun="addFilter" data-binding="true">Hinzufügen</button>
			</div>
		</div>
		<div class="mt-2">
			<button class="btn-primary">Diagramm generieren</button>
			<button class="btn-cancel">Zurücksetzen</button>
		</div>
	</div>
</div>
<div class="hidden">
	<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 283.46 283.46" xml:space="preserve" id="iconDiagram">
		<switch>
			<g>
				<g opacity=".98">
					<path d="M88.594 200.477h19.493c6.778 0 12.286-5.518 12.286-12.305V50.482c0-6.787-5.508-12.295-12.286-12.295H88.594c-6.797 0-12.305 5.508-12.305 12.295v137.689c0 6.788 5.508 12.306 12.305 12.306zM151.193 200.477h19.492c6.777 0 12.285-5.518 12.285-12.305v-85.598c0-6.788-5.508-12.296-12.285-12.296h-19.492c-6.777 0-12.305 5.508-12.305 12.296v85.598c0 6.787 5.528 12.305 12.305 12.305zM213.793 200.477h19.492c6.797 0 12.285-5.518 12.285-12.305v-24.815c0-6.787-5.488-12.314-12.285-12.314h-19.492c-6.797 0-12.305 5.527-12.305 12.314v24.815c0 6.787 5.508 12.305 12.305 12.305z" />
					<path d="M278.209 237.255H41.62V5.247a5.25 5.25 0 0 0-5.254-5.254c-2.891 0-5.234 2.354-5.234 5.254v232.008H5.252a5.24 5.24 0 0 0-5.254 5.244 5.243 5.243 0 0 0 5.254 5.254h25.879v30.46a5.24 5.24 0 0 0 5.234 5.254 5.243 5.243 0 0 0 5.254-5.254v-30.46h236.589a5.243 5.243 0 0 0 5.254-5.254 5.24 5.24 0 0 0-5.253-5.244z" />
				</g>
			</g>
		</switch>
	</svg>
</div>