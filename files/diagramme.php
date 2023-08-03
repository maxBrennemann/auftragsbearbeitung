<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<div class="defCont">
	<p>Diagramme</p>
	<div class="grid grid-cols-2">
		<div class="inline">
			<label>
				<span>Startdatum</span>
				<input type="date" id="startDate" class="input-primary">
			</label>
			<label>
				<span>Enddatum</span>
				<input type="date" id="endDate" class="input-primary">
			</label>
		</div>
		<div class="inline">
			<label>
				<span>Dimension</span>
				<select id="dimension" class="input-primary">
					<option value="0">Alle</option>
					<option value="1">Kunde</option>
					<option value="2">Auftragstyp</option>
				</select>
			</label>
			<label>
				<span>Datentyp</span>
				<select id="datatype" class="input-primary">
					<option value="0">Alle</option>
					<option value="1">Umsatz</option>
					<option value="2">Auftragseingang</option>
					<option value="3">Auftragsabschluss</option>
					<option value="4">Offene Aufträge</option>
				</select>
			</label>
			<label>
				<span>Diagramm</span>
				<select id="diagramType" class="input-primary">
					<option value="getVolumeByMonth" selected>Umsatz</option>
					<option value="getOrders">Auftragseingang</option>
					<option value="getOrdersByCustomer">Aufträge je Kunde</option>
					<option value="getVolumeByOrderType">Umsatz je Auftragstyp</option>
				</select>
			</label>
			<button class="btn-primary">Zurücksetzen</button>
		</div>
	</div>
	<div style="width: 800px;">
		<canvas id="diagram"></canvas>
	</div>
</div>