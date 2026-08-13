<div class="defCont">
	<div class="flex flex-wrap items-end gap-2">
		<div>
			<label class="block text-xs text-gray-500 mb-0.5" for="startDate">Startdatum</label>
			<input type="date" id="startDate" class="input-primary">
		</div>
		<div>
			<label class="block text-xs text-gray-500 mb-0.5" for="endDate">Enddatum</label>
			<input type="date" id="endDate" class="input-primary">
		</div>
		<button class="btn-primary" data-fun="applyRange" data-binding="true">Anwenden</button>
		<div class="ml-2 flex flex-wrap gap-1">
			<button class="btn-cancel" data-fun="presetRange30" data-binding="true">30 Tage</button>
			<button class="btn-cancel" data-fun="presetRange90" data-binding="true">90 Tage</button>
			<button class="btn-cancel" data-fun="presetRange12Months" data-binding="true">12 Monate</button>
			<button class="btn-cancel" data-fun="presetRangeYear" data-binding="true">Dieses Jahr</button>
		</div>
	</div>
</div>

<div id="legacyCutoffNotice" class="hidden mx-2 mb-2 px-3 py-2 text-xs text-blue-800 bg-blue-50 border border-blue-200 rounded-lg"></div>

<div class="defCont">
	<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2">
		<div class="bg-white border border-gray-200 rounded-lg p-3">
			<div class="text-xs text-gray-500">Aufträge im Zeitraum</div>
			<div class="text-2xl font-semibold" id="kpiOrderCount">–</div>
		</div>
		<div class="bg-white border border-gray-200 rounded-lg p-3">
			<div class="text-xs text-gray-500">Umsatz im Zeitraum</div>
			<div class="text-2xl font-semibold" id="kpiRevenue">–</div>
		</div>
		<div class="bg-white border border-gray-200 rounded-lg p-3">
			<div class="text-xs text-gray-500">Ø Zahlungsdauer</div>
			<div class="text-2xl font-semibold" id="kpiPaymentDuration">–</div>
		</div>
		<div class="bg-white border border-gray-200 rounded-lg p-3">
			<div class="text-xs text-gray-500">Offene Rechnungen</div>
			<div class="text-2xl font-semibold" id="kpiOpenInvoices">–</div>
		</div>
		<div class="bg-white border border-gray-200 rounded-lg p-3">
			<div class="text-xs text-gray-500">Davon überfällig (&gt;60 Tage)</div>
			<div class="text-2xl font-semibold" id="kpiOverdueInvoices">–</div>
		</div>
	</div>
</div>

<div class="defCont">
	<div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
		<div>
			<h3>Auftragseingang</h3>
			<div class="bg-white border border-gray-200 rounded-lg p-2 h-64">
				<canvas id="chartOrders"></canvas>
			</div>
		</div>
		<div>
			<h3>Umsatz</h3>
			<div class="bg-white border border-gray-200 rounded-lg p-2 h-64">
				<canvas id="chartRevenue"></canvas>
			</div>
		</div>
		<div>
			<h3>Zahlungsdauer</h3>
			<div class="bg-white border border-gray-200 rounded-lg p-2 h-64">
				<canvas id="chartPaymentDuration"></canvas>
			</div>
		</div>
		<div>
			<h3>Offene Rechnungen nach Alter</h3>
			<div class="bg-white border border-gray-200 rounded-lg p-2 h-64">
				<canvas id="chartAging"></canvas>
			</div>
		</div>
		<div>
			<h3>Auftragsstatus</h3>
			<div class="bg-white border border-gray-200 rounded-lg p-2 h-64">
				<canvas id="chartPipeline"></canvas>
			</div>
		</div>
		<div>
			<h3>Top-Kunden <span class="text-xs text-gray-500 font-normal">(nach Umsatz im Zeitraum)</span></h3>
			<div class="bg-white border border-gray-200 rounded-lg p-2 h-64">
				<canvas id="chartTopCustomers"></canvas>
			</div>
		</div>
	</div>
</div>
