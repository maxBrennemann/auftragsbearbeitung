<div>
			<button class="addToTable" onclick="showPostenAdd();">+</button>
		</div>
		<div id="showPostenAdd" style="display: none;">
			<div class="tabcontainer">
				<button class="tablinks activetab" onclick="openTab(event, 0)">Zeiterfassung</button>
				<button class="tablinks" onclick="openTab(event, 1)">Kostenerfassung</button>
				<!--<button class="tablinks" onclick="openTab(event, 2)">Produkt</button>-->
				<button class="tablinks" onclick="openTab(event, 3)">Produkte</button>
			</div>
			<div class="tabcontent" id="tabZeit" style="display: block;">
				<div id="addPostenZeit">
					<div class="container">
						<span>Zeit in Minuten<br><input class="postenInput" id="time" type="number" min="0"></span><br>
						<span>Stundenlohn in €<br><input class="postenInput" id="wage" type="number" value="<?=$auftrag->getDefaultWage()?>"></span><br>
						<span>Beschreibung<br><textarea id="descr" oninput="this.style.height = '';this.style.height = this.scrollHeight + 'px'"></textarea></span><br>
						<button id="addTimeButton" onclick="addTime()">Hinzufügen</button>
					</div>
					<div class="container">
						<span>Erweiterte Zeiterfassung:</span>
						<br>
						<span>Arbeitszeit(en)</span>
						<p class="timeInputWrapper">von <input class="timeInput" type="time" min="05:00" max="23:00"> bis <input class="timeInput"  type="time" min="05:00" max="23:00"> am <input class="dateInput" type="date"></p>
						<button class="addToTable" onclick="addTimeInputs(event)">+</button>
						<p id="showTimeSummary"></p>
					</div>
				</div>
			</div>
			<div class="tabcontent" id="tabLeistung">
				<div id="addPostenLeistung">
					<select id="selectLeistung" onchange="selectLeistung(event);">
						<?php foreach ($leistungen as $leistung): ?>
							<option value="<?=$leistung['Nummer']?>" data-aufschlag="<?=$leistung['Aufschlag']?>"><?=$leistung['Bezeichnung']?></option>
						<?php endforeach; ?>
					</select>
					<br>
					<span>Menge:<br><input class="postenInput" id="anz" value="1"></span><br>
					<span>Mengeneinheit:<br>
						<input class="postenInput" id="meh">
						<span id="meh_dropdown">▼</span>
						<div class="selectReplacer" id="selectReplacerMEH">
							<p class="optionReplacer" onclick="document.getElementById('meh').value = this.innerHTML;">Stück</p>
							<p class="optionReplacer" onclick="document.getElementById('meh').value = this.innerHTML;">m²</p>
							<p class="optionReplacer" onclick="document.getElementById('meh').value = this.innerHTML;">Meter</p>
							<p class="optionReplacer" onclick="document.getElementById('meh').value = this.innerHTML;">Stunden</p>
						</div>
					</span><br>
					<span>Beschreibung:<br><textarea id="bes" oninput="this.style.height = '';this.style.height = this.scrollHeight + 'px'"></textarea></span><br>
					<span>Einkaufspreis:<br><input class="postenInput" id="ekp" value="0"></span><br>
					<span>Verkaufspreis:<br><input class="postenInput" id="pre" value="0"></span><br>
					<button onclick="addLeistung()" id="addLeistungButton">Hinzufügen</button>
				</div>		
			</div>
			<div class="tabcontent" id="tabProdukt">
				<div id="addPostenProdukt">
					<span>Menge: <input class="postenInput" id="posten_produkt_menge" type="number"></span>
					<span>Marke: <input class="postenInput" id="posten_produkt_marke" type="text"></span>
					<span>EK-Preis: <input class="postenInput" id="posten_produkt_ek" type="text"></span>
					<span>VK-Preis: <input class="postenInput" id="posten_produkt_vk" type="text"></span>
					<span>Name: <input class="postenInput" id="posten_produkt_name" type="text"></span>
					<span>Beschreibung: <textarea id="posten_produkt_besch" oninput="this.style.height = '';this.style.height = this.scrollHeight + 'px'"></textarea></span>
					<button onclick="addProductCompactOld()">Hinzufügen</button>
				</div>
			</div>
			<div class="tabcontent" id="tabProdukte">
				<div id="addPostenProdukt">
					<span>Produkt suchen:</span>
					<div>
						<input type="search" id="productSearch">
						<span class="lupeSpan searchProductEvent"><span class="lupe searchProductEvent">&#9906;</span></span>
					</div>
					<div id="resultContainer"></div>
					<span>Menge: <input class="postenInput" id="posten_produkt_menge" type="number"></span>
					<button onclick="addProductCompact()">Hinzufügen</button>
					<br>
					<a href="<?=Classes\Link::getPageLink("neues-produkt");?>">Neues Produkt hinzufügen</a>
				</div>
			</div>
			<div class="tabcontentEnd">
				<div>
					<span id="showOhneBerechnung">
						<input id="ohneBerechnung" type="checkbox">Ohne Berechnung
					</span>
					<br>
					<span id="showAddToInvoice">
						<input id="addToInvoice" type="checkbox">Der Rechnung hinzufügen
					</span>
					<br>
					<span id="showDiscount">
						<input type="range" min="0" max="100" value="0" name="discountInput" id="discountInput" oninput="showDiscountValue.value = discountInput.value + '%'">
						<output id="showDiscountValue" name="showDiscountValue" for="discountInput">0%</output> Rabatt
					</span>
					<div id="generalPosten"></div>
				</div>
			</div>
		</div>
	</div>
