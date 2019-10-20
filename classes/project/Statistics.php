<?php

class Statistics {

	static function auftragEroeffnen($auftrag) {
		$d = new DateTime('first day of this month');
		$datum = DBAccess::selectQuery("SELECT id, anzahl, gesamtsumme, einkaufssumme FROM statistik_auftraege_pro_monat WHERE datum = {$d->format("Y-m-d")} AND istOffen = 0")[0];
		$id = $datum["id"];

		if ($id == null) {
			DBAccess::insertQuery("INSERT INTO statistik_auftraege_pro_monat (datum, anzahl, gesamtsumme, einkaufssumme, istOffen) VALUES ('$d', 1, {$auftrag->preisBerechnen()}, 0, 0)")
		} else {
			$neueAnzahl = ((int) $datum["anzahl"]) + 1;
			$neueGesamtsumme = ((int) $datum["gesamtsumme"]) + $auftrag->preisBerechnen();
			$neueEinkaufssumme = ((int) $datum["einkaufssumme"]) + 1;
			DBAccess::updateQuery("UPDATE statistik_auftraege_pro_monat SET anzahl = $neueAnzahl, gesamtsumme = $neueGesamtsumme, einkaufssumme = $neueEinkaufssumme WHERE id = $id");
		}
	}

	static function auftragAbschliessen() {
	
	}

}

?>