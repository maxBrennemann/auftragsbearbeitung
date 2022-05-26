<?php

class Statistics {

	static function auftragEroeffnen($auftrag) {
		$d = new DateTime('first day of this month');
		$datum = DBAccess::selectQuery("SELECT id, anzahl, gesamtsumme, einkaufssumme FROM statistik_auftraege_pro_monat WHERE datum = {$d->format("Y-m-d")} AND istOffen = 0")[0];
		$id = $datum["id"];

		if ($id == null) {
			DBAccess::insertQuery("INSERT INTO statistik_auftraege_pro_monat (datum, anzahl, gesamtsumme, einkaufssumme, istOffen) VALUES ('$d', 1, {$auftrag->preisBerechnen()}, 0, 0)");
		} else {
			$neueAnzahl = ((int) $datum["anzahl"]) + 1;
			$neueGesamtsumme = ((int) $datum["gesamtsumme"]) + $auftrag->preisBerechnen();
			$neueEinkaufssumme = ((int) $datum["einkaufssumme"]) + 1;
			DBAccess::updateQuery("UPDATE statistik_auftraege_pro_monat SET anzahl = $neueAnzahl, gesamtsumme = $neueGesamtsumme, einkaufssumme = $neueEinkaufssumme WHERE id = $id");
		}
	}

	static function auftragAbschliessen() {
	
	}

	static function getOrderSum($orderId) {
		$query = "
			SELECT ROUND(SUM(all_posten.price), 2) AS orderPrice 
				FROM (
					SELECT (zeit.ZeitInMinuten / 60) * zeit.Stundenlohn AS price 
					FROM zeit, posten 
					WHERE zeit.Postennummer = posten.Postennummer 
						AND posten.Auftragsnummer = $orderId
					UNION ALL
					SELECT leistung_posten.SpeziefischerPreis AS price 
					FROM leistung_posten, posten 
					WHERE leistung_posten.Postennummer = posten.Postennummer 
						AND posten.Auftragsnummer = $orderId) all_posten
		";
	}

	static function getAllOrdersSum() {
		$query = "
			SELECT ROUND(SUM(all_posten.price), 2) AS orderPrice, all_posten.id AS id 
				FROM (
					SELECT (zeit.ZeitInMinuten / 60) * zeit.Stundenlohn AS price, posten.Auftragsnummer as id 
					FROM zeit, posten 
					WHERE zeit.Postennummer = posten.Postennummer 
					UNION ALL 
					SELECT leistung_posten.SpeziefischerPreis AS price, posten.Auftragsnummer as id 
					FROM leistung_posten, posten 
					WHERE leistung_posten.Postennummer = posten.Postennummer) 
					all_posten GROUP BY id
		";
	}

	static function getVolumeByMonth() {
		$query = "
			SELECT CONCAT(MONTHNAME(Fertigstellung), ' ', YEAR(Fertigstellung)) AS Monat, SUM(orderPrice) AS Volume
			FROM auftragssumme
			GROUP BY YEAR(Fertigstellung), MONTH(Fertigstellung)
		";

		return DBAccess::selectQuery($query);
	}

}

?>