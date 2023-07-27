<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<div class="defCont">
	<p>Diagramme</p>
	<div>
		<label>
			<span>Startdatum</span>
			<input type="date" id="startDate" data-write="true" data-fun="changeStartDate">
		</label>
		<label>
			<span>Enddatum</span>
			<input type="date" id="endDate" data-write="true" data-fun="changeEndDate">
		</label>
	</div>
	<div>
		<label>
			<span>Dimension</span>
			<select id="dimension" data-write="true" data-fun="setDimension">
				<option value="0">Alle</option>
				<option value="1">Kunde</option>
				<option value="2">Auftragstyp</option>
			</select>
		</label>
		<label>
			<span>Datentyp</span>
			<select id="datatype" data-write="true" data-fun="setDatatype">
				<option value="0">Alle</option>
				<option value="getVolumeByMonth">Umsatz</option>
				<option value="getOrders">Auftragseingang</option>
				<option value="3">Auftragsabschluss</option>
				<option value="getOpenOrders">Offene Aufträge</option>
			</select>
		</label>
		<button>Zurücksetzen</button>
	</div>
	<div style="width: 800px;">
		<canvas id="diagram"></canvas>
	</div>
</div>