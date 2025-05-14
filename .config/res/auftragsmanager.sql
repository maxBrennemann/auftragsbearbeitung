-- phpMyAdmin SQL Dump
-- version 4.7.7
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 11. Okt 2019 um 16:23
-- Server-Version: 10.1.30-MariaDB
-- PHP-Version: 7.2.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `auftragsmanager`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `angenommen`
--

CREATE TABLE `angenommen` (
  `id` int(10) NOT NULL,
  `Bezeichnung` varchar(32) NOT NULL,
  `istAllgemein` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `angenommen`
--

INSERT INTO `angenommen` (`id`, `Bezeichnung`, `istAllgemein`) VALUES
(1, 'Email', 1),
(2, 'Telefon', 1),
(3, 'Persönlich', 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ansprechpartner`
--

CREATE TABLE `ansprechpartner` (
  `Nummer` int(11) NOT NULL,
  `Kundennummer` int(11) NOT NULL,
  `Vorname` varchar(16) NOT NULL,
  `Nachname` varchar(16) NOT NULL,
  `Email` varchar(32) NOT NULL,
  `Durchwahl` varchar(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `articles`
--

CREATE TABLE `articles` (
  `id` int(11) NOT NULL,
  `articleUrl` varchar(32) NOT NULL,
  `pageName` varchar(32) NOT NULL,
  `src` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `articles`
--

INSERT INTO `articles` (`id`, `articleUrl`, `pageName`, `src`) VALUES
(1, 'main.php', 'b-schriftung Auftragsstellung', ''),
(2, 'neuerKunde.php', 'Neuer Kunde', 'neuer-kunde'),
(3, 'neuerAuftrag.php', 'Neuer Auftrag', 'neuer-auftrag'),
(4, 'rechnung.php', 'Rechnung', 'rechnung'),
(5, 'angebot.php', 'Angebot', 'angebot'),
(6, 'neuesProdukt.php', 'Neues Produkt', 'neues-produkt'),
(7, '404.html', '404 - Not found', '404'),
(8, 'auftrag.php', 'Auftrag', 'auftrag'),
(9, 'produkt.php', 'Produktinformationen', 'produkt'),
(10, 'diagramme.php', 'Diagramme', 'diagramme'),
(11, 'kunde.php', 'Kundeninfo', 'kunde'),
(12, 'leistungen.php', 'Leistungen', 'leistungen');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `attachments`
--

CREATE TABLE `attachments` (
  `id` int(11) NOT NULL,
  `articleId` int(11) NOT NULL,
  `anchor` varchar(16) NOT NULL,
  `fileSrc` varchar(32) NOT NULL,
  `fileName` varchar(16) NOT NULL,
  `fileType` varchar(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `attachments`
--

INSERT INTO `attachments` (`id`, `articleId`, `anchor`, `fileSrc`, `fileName`, `fileType`) VALUES
(1, 2, 'head', 'helper.js', '0', 'js'),
(11, 2, 'head', 'tableeditor.js', '0', 'js'),
(12, 3, 'head', 'tableeditor.js', '0', 'js'),
(13, 6, 'head', 'tableeditor.js', '0', 'js'),
(14, 3, 'head', 'print.js', '0', 'js'),
(15, 8, 'head', 'auftrag.js', '0', 'js'),
(16, 4, 'head', 'print.js', '0', 'js'),
(18, 8, 'head', 'tableeditor.js', '0', 'js'),
(19, 11, 'footer', 'kunde.js', '0', 'js'),
(20, 3, 'footer', 'neuer-auftrag.js', '0', 'js'),
(21, 1, 'footer', 'main-helper.js', '0', 'js'),
(22, 2, 'footer', 'neuer-kunde.js', '0', 'js'),
(23, 12, 'footer', 'leistungen.js', '0', 'js'),
(24, 12, 'head', 'leistungen.css', '0', 'css'),
(25, 8, 'head', 'print.js', '0', 'js'),
(26, 8, 'head', 'auftrag.css', '0', 'css'),
(27, 8, 'head', 'colorpicker.js', '0', 'js');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `auftrag`
--

CREATE TABLE `auftrag` (
  `Auftragsnummer` int(11) NOT NULL,
  `Kundennummer` int(11) NOT NULL,
  `Auftragsbezeichnung` tinytext NOT NULL,
  `Auftragsbeschreibung` text NOT NULL,
  `Auftragstyp` varchar(32) NOT NULL,
  `Datum` date NOT NULL,
  `Termin` date NOT NULL,
  `Fertigstellung` date NOT NULL,
  `AngenommenDurch` int(10) NOT NULL,
  `AngenommenPer` int(11) NOT NULL,
  `Ansprechpartner` int(11) NOT NULL,
  `Rechnungsnummer` int(11) NOT NULL,
  `Bezahlt` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `auftragstyp`
--

CREATE TABLE `auftragstyp` (
  `id` int(11) NOT NULL,
  `Auftragstyp` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `auftragstyp`
--

INSERT INTO `auftragstyp` (`id`, `Auftragstyp`) VALUES
(1, 'Textil'),
(2, 'Schild');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fahrzeuge`
--

CREATE TABLE `fahrzeuge` (
  `Nummer` int(10) NOT NULL,
  `Kundennummer` int(11) NOT NULL,
  `Kennzeichen` varchar(32) NOT NULL,
  `Fahrzeug` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `farben`
--

CREATE TABLE `farben` (
  `Nummer` int(11) NOT NULL,
  `Kundennummer` int(11) NOT NULL,
  `Auftragsnummer` int(11) NOT NULL,
  `Farbe` varchar(16) NOT NULL,
  `Farbwert` varchar(6) NOT NULL,
  `Notiz` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `keywords`
--

CREATE TABLE `keywords` (
  `id` int(10) NOT NULL,
  `type` varchar(32) NOT NULL,
  `keyword` varchar(32) NOT NULL,
  `fieldname` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `keywords`
--

INSERT INTO `keywords` (`id`, `type`, `keyword`, `fieldname`) VALUES
(1, 'Auftrag', 'DATUM', 'Datum'),
(2, 'Auftrag', 'FIRMA', 'Firmenname'),
(3, 'Auftrag', 'POST', 'Postleitzahl'),
(4, 'Auftrag', 'ORT', 'Ort'),
(5, 'Auftrag', 'STR', 'Straße'),
(6, 'Auftrag', 'ANSPR', ''),
(7, 'Auftrag', 'TEL', 'TelefonFestnetz'),
(8, 'Auftrag', 'EMAIL', 'Email'),
(9, 'Auftrag', 'MOBIL', 'TelefonMobil'),
(10, 'Auftrag', 'AUTO', ''),
(11, 'Auftrag', 'KFZKENN', ''),
(12, 'Auftrag', 'TERMIN', 'Termin'),
(13, 'Auftrag', 'RECHN', ''),
(14, 'Auftrag', 'ANGENOMM', 'AngenommenDurch'),
(15, 'Auftrag', 'KDNR', 'Kundennummer'),
(16, 'Auftrag', 'ANR', 'Auftragsnummer'),
(17, 'Auftrag', 'DATEN', ''),
(18, 'Auftrag', 'FARB1', ''),
(19, 'Auftrag', 'FARB2', ''),
(20, 'Auftrag', 'FARB3', ''),
(21, 'Auftrag', 'ERTEILT', ''),
(22, 'Auftrag', 'UNTER', ''),
(23, 'Auftrag', 'FERTIG', 'Fertigstellung'),
(24, 'Auftrag', 'TIME1', ''),
(25, 'Auftrag', 'TIME2', ''),
(26, 'Auftrag', 'TIME3', ''),
(27, 'Auftrag', 'PFLEGE', ''),
(28, 'Auftrag', 'KLEBE', ''),
(29, 'Auftrag', 'REBAY', ''),
(30, 'Auftrag', 'RPOS', ''),
(31, 'Auftrag', 'RMAIL', ''),
(32, 'Auftrag', 'RMAIL', ''),
(33, 'Auftrag', 'RFUX', ''),
(34, 'Auftrag', 'ABGER', ''),
(35, 'Auftrag', 'FOTO', ''),
(36, 'Auftrag', 'LIEFER', ''),
(37, 'Rechnung', 'FIRMA', 'Firmenname'),
(38, 'Rechnung', 'VORNAME', 'Vorname'),
(39, 'Rechnung', 'NACHNAME', 'Nachname'),
(40, 'Rechnung', 'STRASSE', 'Straße'),
(41, 'Rechnung', 'HAUSNR', 'Hausnummer'),
(42, 'Rechnung', 'PLZ', 'Postleitzahl'),
(43, 'Rechnung', 'ORT', 'Ort'),
(44, 'Rechnung', 'RNR', 'Rechnungsnummer'),
(45, 'Rechnung', 'DATU', 'Datum'),
(46, 'Rechnung', 'KUND', 'Kundennummer'),
(47, 'Rechnung', 'PAG', ''),
(48, 'Rechnung', 'MENG', ''),
(49, 'Rechnung', 'STK', ''),
(50, 'Rechnung', 'BEZ', ''),
(51, 'Rechnung', 'EPR', ''),
(52, 'Rechnung', 'GPR', ''),
(53, 'Rechnung', 'LDAT', 'Fertigstellung'),
(54, 'Rechnung', 'GESNETTO', 'gesamtNetto'),
(55, 'Rechnung', 'MWST', 'gesamtMwSt'),
(56, 'Rechnung', 'GESBRUTTO', 'gesamtBrutto'),
(57, 'Auftrag', 'DESCR', 'Auftragsbeschreibung'),
(58, 'Auftrag', 'HNR', 'Hausnummer');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `kunde`
--

CREATE TABLE `kunde` (
  `Kundennummer` int(10) NOT NULL,
  `Firmenname` varchar(64) NOT NULL,
  `Anrede` int(1) NOT NULL,
  `Vorname` varchar(32) NOT NULL,
  `Nachname` varchar(32) NOT NULL,
  `Straße` varchar(32) NOT NULL,
  `Hausnummer` int(11) NOT NULL,
  `Postleitzahl` int(11) NOT NULL,
  `Ort` varchar(32) NOT NULL,
  `Email` varchar(32) NOT NULL,
  `TelefonFestnetz` varchar(16) NOT NULL,
  `TelefonMobil` varchar(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `kundenlogin`
--

CREATE TABLE `kundenlogin` (
  `Nummer` int(11) NOT NULL,
  `Kundennummer` int(11) NOT NULL,
  `Email` varchar(32) NOT NULL,
  `Passwort` varchar(64) NOT NULL,
  `LetzterLogin` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `leistung`
--

CREATE TABLE `leistung` (
  `Nummer` int(10) NOT NULL,
  `Bezeichnung` varchar(32) NOT NULL,
  `Beschreibung` text NOT NULL,
  `Quelle` varchar(64) NOT NULL,
  `Aufschlag` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `leistung`
--

INSERT INTO `leistung` (`Nummer`, `Bezeichnung`, `Beschreibung`, `Quelle`, `Aufschlag`) VALUES
(1, 'Aufkleber', 'Standardaufkleber bis maximal 20cm x 30cm', 'Marefloors', 100),
(2, 'Plane', '', 'digitaldruck-fabrik', 100),
(3, 'Digitaldruck', '', '', 100),
(4, 'Schild', '', '', 100),
(5, 'Fahrzeugbeschriftung', 'Fahrzeugbeschriftung mit Folie oder Digitaldruck', '', 0),
(6, 'Aufdruck', 'Aufdruck auf Textil mit Flock und Flex', '', 0),
(7, 'Aufdruck + Textil', 'Aufdruck auf Textil mit Flock und Flex inkl. Textil', '', 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `leistung_posten`
--

CREATE TABLE `leistung_posten` (
  `Nummer` int(11) NOT NULL,
  `Leistungsnummer` int(11) NOT NULL,
  `Postennummer` int(11) NOT NULL,
  `Beschreibung` varchar(64) NOT NULL,
  `Einkaufspreis` int(11) NOT NULL,
  `SpeziefischerPreis` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `mitarbeiter`
--

CREATE TABLE `mitarbeiter` (
  `id` int(10) NOT NULL,
  `Vorname` varchar(32) NOT NULL,
  `Nachname` varchar(32) NOT NULL,
  `Email` varchar(32) NOT NULL,
  `Rolle` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `mitarbeiter`
--

INSERT INTO `mitarbeiter` (`id`, `Vorname`, `Nachname`, `Email`, `Rolle`) VALUES
(1, 'defaultData', 'defaultData', 'defaultData', 'Chef'),
(2, 'defaultData', 'defaultData', 'defaultData', ''),
(3, 'defaultData', 'defaultData', '', ''),
(4, 'defaultData', 'defaultData', 'defaultData', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `notizen`
--

CREATE TABLE `notizen` (
  `Nummer` int(11) NOT NULL,
  `Auftragsnummer` int(11) NOT NULL,
  `Notiz` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `posten`
--

CREATE TABLE `posten` (
  `Nummer` int(11) NOT NULL,
  `Postennummer` int(11) NOT NULL,
  `Auftragsnummer` int(11) NOT NULL,
  `Posten` varchar(8) NOT NULL,
  `istStandard` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `produkt`
--

CREATE TABLE `produkt` (
  `Nummer` int(11) NOT NULL,
  `Marke` varchar(32) NOT NULL,
  `Preis` int(11) NOT NULL,
  `Einkaufspreis` int(11) NOT NULL,
  `Bezeichnung` varchar(32) NOT NULL,
  `Beschreibung` text NOT NULL,
  `Bild` varchar(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `produkt`
--

INSERT INTO `produkt` (`Nummer`, `Marke`, `Preis`, `Einkaufspreis`, `Bezeichnung`, `Beschreibung`, `Bild`) VALUES
(1, '', 37, 0, 'Cooles Produkt', 'Dies ist eine Produktbeschreibung.', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `produkt_posten`
--

CREATE TABLE `produkt_posten` (
  `Nummer` int(11) NOT NULL,
  `Produktnummer` int(11) NOT NULL,
  `Postennummer` int(11) NOT NULL,
  `Anzahl` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `produkt_varianten`
--

CREATE TABLE `produkt_varianten` (
  `id` int(11) NOT NULL,
  `Produktnummer` int(11) NOT NULL,
  `Groesse` varchar(16) NOT NULL,
  `Farbe` varchar(16) NOT NULL,
  `Bild` varchar(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `schritte`
--

CREATE TABLE `schritte` (
  `Schrittnummer` int(11) NOT NULL,
  `Auftragsnummer` int(11) NOT NULL,
  `istAllgemein` int(11) NOT NULL,
  `Bezeichnung` text NOT NULL,
  `Datum` date NOT NULL,
  `Priority` int(11) NOT NULL,
  `istErledigt` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `schritte_vordefiniert`
--

CREATE TABLE `schritte_vordefiniert` (
  `id` int(11) NOT NULL,
  `Bezeichnung` text NOT NULL,
  `Leistungsnummer` int(11) NOT NULL,
  `Auftragstyp` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `schritte_vordefiniert`
--

INSERT INTO `schritte_vordefiniert` (`id`, `Bezeichnung`, `Leistungsnummer`, `Auftragstyp`) VALUES
(1, 'Pflegehinweise hinzufügen', 0, 1),
(2, 'Klebeanleitung hinzulegen', 1, 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `zeit`
--

CREATE TABLE `zeit` (
  `Nummer` int(11) NOT NULL,
  `Postennummer` int(11) NOT NULL,
  `ZeitInMinuten` int(11) NOT NULL,
  `Stundenlohn` int(11) NOT NULL,
  `Beschreibung` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `angenommen`
--
ALTER TABLE `angenommen`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `ansprechpartner`
--
ALTER TABLE `ansprechpartner`
  ADD PRIMARY KEY (`Nummer`);

--
-- Indizes für die Tabelle `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`);

--
-- Indizes für die Tabelle `attachments`
--
ALTER TABLE `attachments`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `auftrag`
--
ALTER TABLE `auftrag`
  ADD PRIMARY KEY (`Auftragsnummer`);

--
-- Indizes für die Tabelle `auftragstyp`
--
ALTER TABLE `auftragstyp`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indizes für die Tabelle `fahrzeuge`
--
ALTER TABLE `fahrzeuge`
  ADD PRIMARY KEY (`Nummer`);

--
-- Indizes für die Tabelle `farben`
--
ALTER TABLE `farben`
  ADD PRIMARY KEY (`Nummer`);

--
-- Indizes für die Tabelle `keywords`
--
ALTER TABLE `keywords`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `kunde`
--
ALTER TABLE `kunde`
  ADD PRIMARY KEY (`Kundennummer`),
  ADD UNIQUE KEY `Kundennummer` (`Kundennummer`);

--
-- Indizes für die Tabelle `kundenlogin`
--
ALTER TABLE `kundenlogin`
  ADD PRIMARY KEY (`Nummer`);

--
-- Indizes für die Tabelle `leistung`
--
ALTER TABLE `leistung`
  ADD PRIMARY KEY (`Nummer`);

--
-- Indizes für die Tabelle `leistung_posten`
--
ALTER TABLE `leistung_posten`
  ADD PRIMARY KEY (`Nummer`);

--
-- Indizes für die Tabelle `mitarbeiter`
--
ALTER TABLE `mitarbeiter`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `posten`
--
ALTER TABLE `posten`
  ADD PRIMARY KEY (`Nummer`);

--
-- Indizes für die Tabelle `produkt`
--
ALTER TABLE `produkt`
  ADD PRIMARY KEY (`Nummer`);

--
-- Indizes für die Tabelle `produkt_posten`
--
ALTER TABLE `produkt_posten`
  ADD PRIMARY KEY (`Nummer`);

--
-- Indizes für die Tabelle `produkt_varianten`
--
ALTER TABLE `produkt_varianten`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `schritte`
--
ALTER TABLE `schritte`
  ADD PRIMARY KEY (`Schrittnummer`);

--
-- Indizes für die Tabelle `schritte_vordefiniert`
--
ALTER TABLE `schritte_vordefiniert`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indizes für die Tabelle `zeit`
--
ALTER TABLE `zeit`
  ADD PRIMARY KEY (`Nummer`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `angenommen`
--
ALTER TABLE `angenommen`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT für Tabelle `ansprechpartner`
--
ALTER TABLE `ansprechpartner`
  MODIFY `Nummer` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT für Tabelle `articles`
--
ALTER TABLE `articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT für Tabelle `attachments`
--
ALTER TABLE `attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT für Tabelle `auftrag`
--
ALTER TABLE `auftrag`
  MODIFY `Auftragsnummer` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT für Tabelle `fahrzeuge`
--
ALTER TABLE `fahrzeuge`
  MODIFY `Nummer` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT für Tabelle `keywords`
--
ALTER TABLE `keywords`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT für Tabelle `kunde`
--
ALTER TABLE `kunde`
  MODIFY `Kundennummer` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT für Tabelle `kundenlogin`
--
ALTER TABLE `kundenlogin`
  MODIFY `Nummer` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `leistung`
--
ALTER TABLE `leistung`
  MODIFY `Nummer` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT für Tabelle `leistung_posten`
--
ALTER TABLE `leistung_posten`
  MODIFY `Nummer` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT für Tabelle `mitarbeiter`
--
ALTER TABLE `mitarbeiter`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT für Tabelle `posten`
--
ALTER TABLE `posten`
  MODIFY `Nummer` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT für Tabelle `produkt`
--
ALTER TABLE `produkt`
  MODIFY `Nummer` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT für Tabelle `produkt_posten`
--
ALTER TABLE `produkt_posten`
  MODIFY `Nummer` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `produkt_varianten`
--
ALTER TABLE `produkt_varianten`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `schritte`
--
ALTER TABLE `schritte`
  MODIFY `Schrittnummer` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT für Tabelle `zeit`
--
ALTER TABLE `zeit`
  MODIFY `Nummer` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
