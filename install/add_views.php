<?php

use MaxBrennemann\PhpUtilities\DBAccess;

$view_1 = "CREATE VIEW postendata AS SELECT posten.*, zeit.ZeitInMinuten, CONCAT(COALESCE(zeit.Beschreibung, ''), COALESCE(leistung_posten.Beschreibung, ''), COALESCE(produkt_posten.Produktnummer, '')) AS Beschreibung FROM posten LEFT JOIN zeit ON posten.Postennummer = zeit.Postennummer LEFT JOIN leistung_posten ON posten.Postennummer = leistung_posten.Postennummer LEFT JOIN produkt_posten ON posten.Postennummer = produkt_posten.Postennummer;";
$view_2 = "CREATE VIEW auftragssumme_view AS SELECT (zeit.ZeitInMinuten / 60) * zeit.Stundenlohn AS price, posten.Auftragsnummer as id FROM zeit, posten WHERE zeit.Postennummer = posten.Postennummer UNION ALL SELECT leistung_posten.SpeziefischerPreis AS price, posten.Auftragsnummer as id FROM leistung_posten, posten WHERE leistung_posten.Postennummer = posten.Postennummer;";
$view_3 = "CREATE VIEW auftragssumme AS SELECT ROUND(SUM(auftragssumme_view.price), 2) AS orderPrice, auftragssumme_view.id AS id, auftrag.Datum, auftrag.Fertigstellung FROM auftragssumme_view, auftrag WHERE auftrag.Auftragsnummer = id GROUP BY id;";

DBAccess::executeQuery($view_1);
DBAccess::executeQuery($view_2);
DBAccess::executeQuery($view_3);

?>