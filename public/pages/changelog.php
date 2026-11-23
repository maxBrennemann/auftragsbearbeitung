<div class="changelog defCont pl-5">
    <nav id="nav" class="bg-gray-200 p-3 rounded-lg">
        <p class="font-bold text-md text-lg">Versionen</p>
        <ul>
            <li>1.3</li>
            <ul>
                <li><a class="link-primary" href="#1.3.0">1.3.0</a></li>
            </ul>
            <li>1.2</li>
            <ul>
                <li><a class="link-primary" href="#1.2.3">1.2.3</a></li>
                <li><a class="link-primary" href="#1.2.2">1.2.2</a></li>
                <li><a class="link-primary" href="#1.2.1">1.2.1</a></li>
                <li><a class="link-primary" href="#1.2.0">1.2.0</a></li>
            </ul>
            <li>1.1</li>
            <ul>
                <li><a class="link-primary" href="#1.1.18">1.1.18</a></li>
                <li><a class="link-primary" href="#1.1.17">1.1.17</a></li>
                <li><a class="link-primary" href="#1.1.16">1.1.16</a></li>
                <li><a class="link-primary" href="#1.1.15">1.1.15</a></li>
                <li><a class="link-primary" href="#1.1.14">1.1.14</a></li>
                <li><a class="link-primary" href="#1.1.13">1.1.13</a></li>
                <li><a class="link-primary" href="#1.1.12">1.1.12</a></li>
                <li><a class="link-primary" href="#1.1.11">1.1.11</a></li>
                <li><a class="link-primary" href="#1.1.10">1.1.10</a></li>
                <li><a class="link-primary" href="#1.1.9">1.1.9</a></li>
                <li><a class="link-primary" href="#1.1.8">1.1.8</a></li>
                <li><a class="link-primary" href="#1.1.7">1.1.7</a></li>
            </ul>
        </ul>
    </nav>
    <h3 id="1.3.0" class="font-bold my-5 text-lg">1.3.0</h3>
    <ul class="list-disc list-inside">
        <li>Angebote grundlegend überarbeitet
            <ul class="ml-5 list-disc list-inside">
                <li>Neue Angebotsverwaltung mit Angebotsnummer, Gültigkeit und Status</li>
                <li>Angebote können per E-Mail versendet werden</li>
                <li>Abgelaufene Angebote laufen automatisch ab</li>
                <li>Adress- und Ansprechpartnerauswahl sowie kundenbezogene Kopfzeilen</li>
            </ul>
        </li>
        <li>Rechnungswesen erweitert
            <ul class="ml-5 list-disc list-inside">
                <li>Gutschriften können erstellt, verwaltet, als PDF erzeugt und per E-Mail versendet werden</li>
                <li>Rechnungen können storniert werden - mit Stornogrund, optionaler Benachrichtigung und zugehöriger Storno-Gutschrift</li>
                <li>Stornierte Rechnungen werden gesondert angezeigt und aus Auswertungen ausgeschlossen</li>
                <li>Leistungsdatum kann als Datum oder Kalenderwoche angegeben und ein- und ausgeblendet werden</li>
                <li>Rechnungsposten mit verbessertem Drag-and-Drop und überarbeiteter Verwaltung</li>
            </ul>
        </li>
        <li>Lieferscheine und Zahlungserinnerungen als PDF hinzugefügt, Zahlungserinnerungen können versendet werden</li>
        <li>Dokumenttexte und Layouts vereinheitlicht
            <ul class="ml-5 list-disc list-inside">
                <li>Gemeinsamer Baustein-Mechanismus für Rechnungen und Angebote</li>
                <li>Texte können hinzugefügt, bearbeitet und gelöscht werden</li>
                <li>Reihenfolge der Bausteine per Drag-and-Drop anpassbar</li>
            </ul>
        </li>
        <li>Statistik überarbeitet
            <ul class="ml-5 list-disc list-inside">
                <li>Neues Dashboard mit Umsatz- und Auftragsentwicklung, Zahlungsdauern, offenen Rechnungen, Auftrags-Pipeline und Top-Kunden</li>
                <li>Stichtag für historische Rechnungsdaten konfigurierbar</li>
            </ul>
        </li>
        <li>E-Mail-Versand über SMTP konfigurierbar (Host, Port, Benutzer, Passwort, Absender), Passwort verschlüsselt gespeichert</li>
        <li>Kontoinhaber in den Firmeneinstellungen und auf den PDFs ergänzt</li>
        <li>Dateien können gelöscht werden, Bildvorschau für Aufträge überarbeitet</li>
        <li>Zugriff auf generierte Dokumente und Backups nur noch für angemeldete Nutzer</li>
        <li>PrestaShop-Anbindung entfernt, da nicht mehr benötigt</li>
        <li>Sicherheitsupdates der Docker-Images (CVE-2026-109618, CVE-2026-109619)</li>
        <li>Diverse Fehlerbehebungen</li>
    </ul>
    <h3 id="1.2.3" class="font-bold my-5 text-lg">1.2.3</h3>
    <ul class="list-disc list-inside">
        <li>Einstellungen grundlegend überarbeitet
            <ul class="ml-5 list-disc list-inside">
                <li>Neue zentrale Einstellungsverwaltung, alte Einstellungen ersetzt</li>
                <li>Parameterverwaltung in die Tools ausgelagert</li>
            </ul>
        </li>
        <li>API-Zugriff über Bearer-Token
            <ul class="ml-5 list-disc list-inside">
                <li>Token-Verwaltung über die Konsole</li>
                <li>Rechnungen können über die API als bezahlt markiert werden</li>
            </ul>
        </li>
        <li>E-Mail-Versand für Rechnungen implementiert
            <ul class="ml-5 list-disc list-inside">
                <li>Rechnungen können direkt versendet werden</li>
                <li>Standard-Absenderadresse für den Sammelversand hinterlegbar</li>
            </ul>
        </li>
        <li>Bearbeitungsschritte können bearbeitet und gelöscht werden, Benachrichtigungen werden dabei bereinigt</li>
        <li>Neue Log-Tabelle zur Nachverfolgung</li>
        <li>Kunden können gelöscht werden</li>
        <li>Mehrwertsteuersatz kann konfiguriert werden, Rechnungsberechnung angepasst</li>
        <li>Favicon kann hochgeladen werden, Favicon-Prozess überarbeitet</li>
        <li>Standard-Bilder und Icons können gelöscht werden</li>
        <li>Diverse Fehlerbehebungen
            <ul class="ml-5 list-disc list-inside">
                <li>Benachrichtigungen und automatische Bezahlt-Einstellung korrigiert</li>
                <li>Fehler in Tabellen, Autologin und Zeiterfassung behoben</li>
                <li>SQL-Injection geschlossen</li>
                <li>CSP-Fehler bei offenen Rechnungen behoben</li>
            </ul>
        </li>
        <li>Abhängigkeiten und Docker-Images aktualisiert, PHPUnit auf Version 12 angehoben</li>
    </ul>
    <h3 id="1.2.2" class="font-bold my-5 text-lg">1.2.2</h3>
    <ul class="list-disc list-inside">
        <li>Interne Dateistruktur angepasst
            <ul class="ml-5 list-disc list-inside">
                <li>Vite Build wird nun statt Webpack verwendet</li>
                <li>Minify Status entfernt, in Production wird immer alles gebundelt</li>
                <li>Mehrere Konfigurationsdateien in <code class="bg-white rounded-xs px-0.5">.config</code> abgelegt</li>
                <li>Dateibaum verschlankt</li>
            </ul>
        </li>
        <li>Lokale Entwicklungsumgebung angepasst
            <ul class="ml-5 list-disc list-inside">
                <li>XDebug integriert</li>
                <li>Vite Hot Reloading</li>
                <li>Tailwind Hot Reloading</li>
                <li>Makefile hinzugefügt</li>
                <li>TypeScript in Entwicklung eingebunden und JavaScript ersetzt</li>
            </ul>
        </li>
        <li>Diverse Bugfixes
            <ul class="ml-5 list-disc list-inside">
                <li>Circle Dependencies entfernt, da es Probleme mit dem Hotreloading gab</li>
                <li>Termin kann wieder nachträglich gesetzt werden</li>
                <li>Fehler in Nummernvergabe für Rechnungen behoben</li>
                <li>Rechnungssumme neu implementiert, da fehlerhaft</li>
                <li>Bearbeitung von Rechnungsposten neu implementiert</li>
            </ul>
        </li>
        <li>Benachrichtigungen grundlegend überarbeitet und alte Alternativen ersetzt</li>
        <li>Aufträge können einem anderen Kunden zugewiesen werden</li>
    </ul>
    <h3 id="1.2.1" class="font-bold my-5 text-lg">1.2.1</h3>
    <ul class="list-disc list-inside">
        <li>Rechnungsstellung Fehlerbehebungen</li>
        <li>Bilder können jetzt immer in einer Vorschau angeschaut werden</li>
        <li>Neuer Upload Handler und neues Dateimanagementsystem</li>
        <li>Suche überarbeitet und neue Suche integriert</li>
        <li>Prestashop Anbindung weiter überarbeitet</li>
        <li>Prestashop Upload wird von einem CronTask übernommen</li>
        <li>Lokale Entwicklungsumgebung angepasst
            <ul class="ml-5 list-disc list-inside">
                <li>NGINX hat Apache ersetzt</li>
                <li>HTTPS Verschlüsselung mit Zertifikat für localhost</li>
                <li>h2 Protokoll</li>
                <li>Neue Konsolenfunktionen eingebaut</li>
            </ul>
        </li>
    </ul>
    <h3 id="1.2.0" class="font-bold my-5 text-lg">1.2.0</h3>
    <ul class="list-disc list-inside">
        <li>Rechnungsstellung überarbeitet</li>
        <li>Neuer Tabellengenerator</li>
        <li>Neuer Listengenerator - alte Listen wurden komplett entfernt</li>
        <li>JavaScript wird nun vollständig über Module eingebunden</li>
        <li>Integration von TailwindCSS</li>
        <li>Diverse Fehlerbehebungen</li>
        <li>Auslagerung von Klassen in eigene Projekte</li>
        <li>Neues API Design</li>
        <li>Neue Benachrichtigungen</li>
        <li>Tabellengenerator und Bulkactions für das Backend</li>
        <li>CronManager eingeführt</li>
        <li>Namespaces im Backend eingeführt</li>
        <li>Neuer TemplateController</li>
    </ul>
    <h3 id="1.1.18" class="font-bold my-5 text-lg">1.1.18</h3>
    <ul class="list-disc list-inside">
        <li>Auftragstypen können ergänzt werden</li>
        <li>Neue Nutzerübersicht mit Verlauf</li>
        <li>ChatGPT Integration zum Generieren von Artikelbeschreibungen</li>
        <li>Facebook Export der Produkte über eine CSV Datei möglich</li>
        <li>Aufbau von CI/CD Prozess</li>
        <li>Designanpassungen</li>
        <li>Integration von Webpack, damit JavaScript Module richtig gebundelt werden können</li>
        <li>JavaScript wurde modularisiert</li>
        <li>Archivierung von Aufträgen kann rückgängig gemacht werden</li>
        <li>Bildreihenfolge kann nun bearbeitet werden</li>
        <li>Kategorien können gespeichert werden und es werden Vorschläge von ChatGPT generiert</li>
        <li>Bezahldatum und Bezahlweise für Rechnungen können hinterlegt werden</li>
        <li>Diverse Fehlerbehebungen</li>
    </ul>
    <h3 id="1.1.17" class="font-bold my-5 text-lg">1.1.17</h3>
    <ul class="list-disc list-inside">
        <li>Produktexport für Prestashop wurde integriert
            <ul class="ml-5 list-disc list-inside">
                <li>Aufkleber, Wandtattoos und Textilien können automatisch generiert werden</li>
                <li>Es kann eine Liste der Produkte mit Motivnummer erstellt werden</li>
                <li>Facebook-Export vorhanden</li>
            </ul>
        </li>
        <li>Upgrade Seite wurde hinzugefügt, um Datenbankupdates einfacher zu gestalten (/upgrade)</li>
        <li>Tagmanager wurde eingeführt</li>
        <li>Neuer Image-Upload für Motive</li>
        <li>Listensortierung wird gespeichert und funktioniert zuverlässig</li>
        <li>Erstes System zur Zeiterfassung hinzugefügt</li>
        <li>CSS und JS Minifier hinzugefügt</li>
        <li>Diverse Fehlerbehebungen</li>
    </ul>
    <h3 id="1.1.16" class="font-bold my-5 text-lg">1.1.16</h3>
    <ul class="list-disc list-inside">
        <li>Fehlerbehebungen</li>
        <li>Einkaufspreis wird in Gesamtpreis und Einzelpreis angezeigt</li>
        <li>Postenbearbeitung wurde überarbeitet - funktioniert jetzt fehlerfrei</li>
        <li>Die Postenreihenfolge kann nun bearbeitet werden</li>
        <li>Auftragsseite: Daten bei Auftragsstellung und Termin nun in gängigem Format</li>
        <li>Produktsuche wurde verbessert</li>
        <li>Benachrichtigungsmenü ist übersichtlicher</li>
        <li>Mobile Ansicht wurde optimiert</li>
        <li>Link zum neuen Textilkonfigurator wurde hinzugefügt</li>
        <li>Verbesserungen aus den Vorschlägen</li>
        <li>Drag and Drop für Dateiupload hinzugefügt</li>
        <li>Autologin eingeführt</li>
    </ul>
    <h3 id="1.1.15" class="font-bold my-5 text-lg">1.1.15</h3>
    <ul class="list-disc list-inside">
        <li>Erweiterte Zeiterfassung nun um Datum ergänzt. Felder werden bereinigt, wenn die Zeiten an den Server geschickt werden.</li>
        <li>Wenn Posten oder Bearbeitungsschritte gelöscht werden, wird das im Auftragsverlauf vermerkt</li>
        <li>Fehlerbehebungen</li>
        <li>Rechnungen:
            <ul class="ml-5 list-disc list-inside">
                <li>Ohne Berechnung steht nun automatisch auf der Rechnung</li>
                <li>Zahlbar sofort ohne weitere Abzüge steht nun auf der Rechnung</li>
                <li>Auftragsnummer steht auf der Rechnung</li>
            </ul>
        </li>
        <li>SQL File kann nun heruntergeladen werden, dient als vorläufige Backup Lösung</li>
        <li>Globale Suchfunktion wurde hinzugefügt</li>
        <li>Attributeerstellung wurde vereinfacht</li>
        <li>Leistung und Produkte werden zusammengeführt - noch in Arbeit</li>
    </ul>
    <h3 id="1.1.14" class="font-bold my-5 text-lg">1.1.14</h3>
    <ul class="list-disc list-inside">
        <li>Diagramm "Umsatz pro Monat" wird nun aufgrund des Fertigstellungsdatums generiert</li>
        <li>Externe Javascript Dateien können nun via PHP eingebunden werden</li>
        <li>Posten können nun direkt der Rechnung hinzugefügt werden</li>
        <li>Textfelder werden nun automatisch größer, wenn der Text das Zeilenende erreicht</li>
        <li>Auftragsbeschreibung und Auftragsbezeichnung haben ein neues Interface bekommen</li>
        <li>Rechnungsformular wird nun vollständig erstellt</li>
        <li>Standardtexte können der Rechnung hinzugefügt werden</li>
        <li>Rechnungsposten werden nun auf der Auftragsseite so dargestellt wie auf der Rechnung</li>
        <li>Erweiterete Zeiterfassung wurde hinzugefügt</li>
        <li>Zeit wird in Stunden auf der Rechnung dargestellt</li>
        <li>Mengeneinheiten können direkt ausgewählt werden</li>
        <li>Diverse Fehlerbehebungen in Bezug auf Rechnungstellung und Posten</li>
    </ul>
    <h3 id="1.1.13" class="font-bold my-5 text-lg">1.1.13</h3>
    <ul class="list-disc list-inside">
        <li>Posten können nun in einem neuen Menü hinzugefügt werden</li>
        <li>Es können Posten mit Dateien verknüpft werden</li>
        <li>Hamburger Menü wurde hinzugefügt, aktuell noch ohne Inhalt</li>
        <li>Auftragsbezeichnung kann geändert werden</li>
        <li>Diagrammfarben werden nun zufällig generiert</li>
        <li>Bei langen Tabellen bleibt der Tabellenkopf beim Scrollen stehen</li>
        <li>Upload für Bilder bei Fahrzeugen funktioniert wieder</li>
        <li>Farben sind nun universell und das Menü funktioniert</li>
        <li>Überarbeitete Kundenadressen</li>
        <li>Ansprechpartner können nun gelöscht und bearbeitet werden</li>
    </ul>
    <h3 id="1.1.12" class="font-bold my-5 text-lg">1.1.12</h3>
    <ul class="list-disc list-inside">
        <li>Frontoffice wurde hinzugefügt</li>
        <li>Website nun mit Shopfunktion</li>
        <li>Anzeige eines Default-Images für Produkte</li>
        <li>Verbesserungen für Produkte</li>
        <li>Cache wurde implementiert, aktuell deaktiviert</li>
    </ul>
    <h3 id="1.1.11" class="font-bold my-5 text-lg">1.1.11</h3>
    <ul class="list-disc list-inside">
        <li>Verschiedene Fehlerbehebungen</li>
        <li>Leistungen wurde verbessert</li>
        <li>Vor dem Löschen wird nach einer Bestätigung gefragt</li>
        <li>Listen können nun mit Daten gefüllt werden</li>
        <li>Datum für Auftragsstellung und Termin kann nun bearbeitet werden</li>
        <li>Inputs für Postendaten werden nach dem Hinzufügen geleert</li>
        <li>Aufkleber Posten funktioniert jetzt richtig - Seite muss nicht mehr neu geladen werden</li>
        <li>Reihenfolge für Postendaten angepasst</li>
        <li>Posten können bearbeitet werden
            <ul class="ml-5 list-disc list-inside">
                <li>alte Postendaten werde noch nicht gespeichert</li>
                <li>kein Postenverlauf</li>
                <li>alle Daten einer Zeile müssen neu eingegeben werden</li>
            </ul>
        </li>
    </ul>
    <h3 id="1.1.10" class="font-bold my-5 text-lg">1.1.10</h3>
    <ul class="list-disc list-inside">
        <li>Aufträge können durchsucht werden</li>
        <li>Ansprechpartner wird nun beim Auftrag angezeigt und kann zukünftig geändert werden</li>
        <li>Neben dem Gesamtpreis wird nun der geschätzte Gewinn angezeigt</li>
        <li>Rabatte für Posten implementiert</li>
        <li>Mengeneinheiten hinzugefügt</li>
        <li>Bei der Priorität wird nun ein Text statt einer Zahl angezeigt</li>
        <li>Auftragstyp muss nun festgelegt werden. Auftragstypen können in den Einstellungen angezeigt werden</li>
        <li>Infobuttons laden nun Text aus der Datenbank</li>
    </ul>
    <h3 id="1.1.9" class="font-bold my-5 text-lg">1.1.9</h3>
    <ul class="list-disc list-inside">
        <li>Erste Überarbeitung des NotificationManagers - Benachrichtigungen werden nun auch wieder entfernt</li>
        <li>Auftragsbeschreibung kann nun ordentlich geändert werden</li>
        <li>Einführung von Info-Buttons</li>
        <li>Notizen können einem Auftrag hinzugefügt werden</li>
        <li>Weiterleitung zu neuem Kunden nun richtig</li>
        <li>Fehlerbehebungen</li>
    </ul>
    <h3 id="1.1.8" class="font-bold my-5 text-lg">1.1.8</h3>
    <ul class="list-disc list-inside">
        <li>Designfehler für Auftragskarten behoben</li>
        <li>Upload für mehrere Dateien funktioniert jetzt fehlerfrei - mit bebilderter Vorschau</li>
        <li>Farbeinstellungen wurden hinzugefügt</li>
        <li>Tabellenkopf wird bei Ansprechpartnern angezeigt - also Einfügen nun immer möglich</li>
    </ul>
    <h3 id="1.1.7" class="font-bold my-5 text-lg">1.1.7</h3>
    <ul class="list-disc list-inside">
        <li>Fahrzeuge werden nun über ein eigenes Menü hinzugefügt</li>
        <li>Ansprechpartner können nun über den neuen Tabellengenerator hinzugefügt werden</li>
        <li>Auf der Kundenseite werden die Aufträge nun als Karteikarten angezeigt</li>
        <li>Bearbeitungsschritte auf der Frontpage können angeklickt werden - man wird zum Auftrag weitergeleitet</li>
    </ul>
</div>