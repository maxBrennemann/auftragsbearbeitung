-- phpMyAdmin SQL Dump
-- version 4.8.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Erstellungszeit: 24. Apr 2020 um 11:21
-- Server-Version: 5.5.62-0ubuntu0.14.04.1
-- PHP-Version: 7.1.14

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
-- Tabellenstruktur für Tabelle `angebot`
--

CREATE TABLE `angebot` (
  `id` int(11) NOT NULL,
  `kdnr` int(11) NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `angenommen`
--

CREATE TABLE `angenommen` (
  `id` int(10) NOT NULL,
  `Bezeichnung` varchar(32) NOT NULL,
  `istAllgemein` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  `Durchwahl` varchar(16) NOT NULL,
  `Mobiltelefonnummer` varchar(16) DEFAULT NULL
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
(12, 'leistungen.php', 'Leistungen', 'leistungen'),
(13, 'login.php', 'Login', 'login'),
(14, 'attributes.php', 'Attribute', 'attributes'),
(15, 'toDo.php', 'Verbesserungen', 'verbesserungen'),
(16, 'offeneRechnungen.php', 'Offene Rechnungen', 'offene-rechnungen'),
(17, 'fahrzeug.php', 'Fahrzeuge', 'fahrzeug'),
(18, 'addSticker.php', 'Motive', 'sticker'),
(19, 'listmaker.php', 'Listen', 'listmaker');

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
(27, 8, 'head', 'colorpicker.js', '0', 'js'),
(28, 1, 'head', 'main.css', '0', 'css'),
(29, 6, 'footer', 'neues-produkt.js', '0', 'js'),
(30, 6, 'head', 'neuesProdukt.css', '0', 'css'),
(31, 11, 'head', 'kunde.css', '0', 'css'),
(32, 14, 'head', 'attribute.js', '0', 'js'),
(33, 19, 'head', 'listmaker.js', '0', 'js'),
(34, 8, 'head', 'list.js', '0', 'js');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `attribute`
--

CREATE TABLE `attribute` (
  `id` int(11) UNSIGNED NOT NULL,
  `attribute_group_id` int(11) NOT NULL,
  `value` varchar(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `attribute_group`
--

CREATE TABLE `attribute_group` (
  `id` int(11) UNSIGNED NOT NULL,
  `attribute_group` varchar(16) NOT NULL,
  `descr` varchar(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `attribute_to_product`
--

CREATE TABLE `attribute_to_product` (
  `attribute_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `auftrag`
--

CREATE TABLE `auftrag` (
  `Auftragsnummer` int(11) NOT NULL,
  `Kundennummer` int(11) NOT NULL,
  `Auftragsbezeichnung` tinytext NOT NULL,
  `Auftragsbeschreibung` text NOT NULL,
  `Auftragstyp` int(11) NOT NULL,
  `Datum` date NOT NULL,
  `Termin` date NOT NULL,
  `Fertigstellung` date NOT NULL,
  `AngenommenDurch` int(10) NOT NULL,
  `AngenommenPer` int(11) NOT NULL,
  `Ansprechpartner` int(11) NOT NULL,
  `Rechnungsnummer` int(11) NOT NULL,
  `Bezahlt` int(11) NOT NULL,
  `archiviert` int(11) NOT NULL DEFAULT '1'
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
(0, 'Fahrzeugbeschriftung'),
(1, 'Textil'),
(2, 'Schild'),
(3, 'Digitaldruck');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `dateien`
--

CREATE TABLE `dateien` (
  `id` int(11) NOT NULL,
  `dateiname` varchar(64) DEFAULT NULL,
  `originalname` varchar(128) NOT NULL,
  `date` date NOT NULL,
  `typ` varchar(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `dateien_auftraege`
--

CREATE TABLE `dateien_auftraege` (
  `id_datei` int(11) NOT NULL,
  `id_auftrag` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `dateien_fahrzeuge`
--

CREATE TABLE `dateien_fahrzeuge` (
  `id_datei` int(11) NOT NULL,
  `id_fahrzeug` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `dateien_motive`
--

CREATE TABLE `dateien_motive` (
  `id_datei` int(11) NOT NULL,
  `id_motive` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `dateien_produkte`
--

CREATE TABLE `dateien_produkte` (
  `id_datei` int(11) NOT NULL,
  `id_produkt` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `einkauf`
--

CREATE TABLE `einkauf` (
  `id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `einkauf`
--

INSERT INTO `einkauf` (`id`, `name`, `description`) VALUES
(1, 'CottonClassic', ''),
(2, 'L-Shop', ''),
(3, 'Maprom', 'günstigste Preise');

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
-- Tabellenstruktur für Tabelle `fahrzeuge_auftraege`
--

CREATE TABLE `fahrzeuge_auftraege` (
  `id_fahrzeug` int(11) NOT NULL,
  `id_auftrag` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `farben`
--

CREATE TABLE `farben` (
  `Nummer` int(10) NOT NULL,
  `Kundennummer` int(11) NOT NULL,
  `Auftragsnummer` int(11) NOT NULL,
  `Farbe` varchar(16) NOT NULL,
  `Farbwert` varchar(6) NOT NULL,
  `Notiz` text NOT NULL,
  `Hersteller` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `header`
--

CREATE TABLE `header` (
  `id` int(11) NOT NULL,
  `name` varchar(32) NOT NULL,
  `src` varchar(32) NOT NULL,
  `active` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `history`
--

CREATE TABLE `history` (
  `id` int(10) NOT NULL,
  `orderid` int(11) NOT NULL,
  `number` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `state` varchar(16) NOT NULL,
  `insertstamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `history_type`
--

CREATE TABLE `history_type` (
  `type_id` int(11) NOT NULL,
  `name` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `history_type`
--

INSERT INTO `history_type` (`type_id`, `name`) VALUES
(1, 'Posten'),
(2, 'Schritt'),
(3, 'fahrzeuge'),
(4, 'dateien'),
(5, 'auftrag'),
(6, 'angebot');

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
  `TelefonMobil` varchar(16) NOT NULL,
  `Website` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Trigger `kunde`
--
DELIMITER $$
CREATE TRIGGER `createRowForNewCustomer` AFTER INSERT ON `kunde` FOR EACH ROW INSERT INTO kunde_extended SET kunde_extended.kundennummer = NEW.Kundennummer
$$
DELIMITER ;

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
-- Tabellenstruktur für Tabelle `kunde_aenderungen`
--

CREATE TABLE `kunde_aenderungen` (
  `id` int(10) NOT NULL,
  `gueltigBis` date NOT NULL,
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
  `TelefonMobil` varchar(16) NOT NULL,
  `Website` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `kunde_extended`
--

CREATE TABLE `kunde_extended` (
  `id` int(10) NOT NULL,
  `notizen` text,
  `kundennummer` int(10) NOT NULL,
  `Faxnummer` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `last_login`
--

CREATE TABLE `last_login` (
  `id` int(11) NOT NULL,
  `id_member` int(11) NOT NULL,
  `loginstamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
-- Tabellenstruktur für Tabelle `liste`
--

CREATE TABLE `liste` (
  `id` int(11) NOT NULL,
  `name` varchar(32) NOT NULL,
  `zugehoerigkeit` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `listenauswahl`
--

CREATE TABLE `listenauswahl` (
  `id` int(11) NOT NULL,
  `listenpunktid` int(11) NOT NULL,
  `bezeichnung` varchar(64) CHARACTER SET utf8mb4 NOT NULL,
  `ordnung` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `listenpunkt`
--

CREATE TABLE `listenpunkt` (
  `id` int(11) NOT NULL,
  `listenid` int(11) NOT NULL,
  `text` varchar(64) NOT NULL,
  `art` int(11) NOT NULL,
  `ordnung` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `members`
--

CREATE TABLE `members` (
  `id` int(11) NOT NULL,
  `email` varchar(64) NOT NULL,
  `username` varchar(64) NOT NULL,
  `password` varchar(128) NOT NULL,
  `specialRole` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `members`
--

INSERT INTO `members` (`id`, `email`, `username`, `password`, `specialRole`) VALUES
(1, 'max@b-schriftung.de', 'maxBrennemann', '$2y$10$4dsOHUtKhrar23WdOWgOpO3DBJVhD18lxZnDet7H1vFc.mn6FuWAK', 'none'),
(2, 'info@b-schriftung.de', 'dietmarBrennemann', '$2y$10$zkUjHMaLlCLJ0CEkC4ckrO1BJReuoP/Ugl1MhBqRxqpX8LLhbFmHq', 'none'),
(3, 'engemann@b-schriftung.de', 'veronikaEngemann', '$2y$10$hGKJTKLnrVo9jyaUWRpcveDq63uMlv9O1QlzBaF31Ms9z0nxCwbmq', 'none');

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
(1, 'Dietmar', 'Brennemann', 'info@b-schriftung.de', 'Chef'),
(2, 'Max', 'Brennemann', 'max.brennemann@b-schriftung.de', ''),
(3, 'Petra', 'Brennemannn', '', ''),
(4, 'Veronika', 'Engemann', 'engemann@b-schriftung.de', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `motive`
--

CREATE TABLE `motive` (
  `id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  `angebotsNr` int(11) NOT NULL,
  `Posten` varchar(8) NOT NULL,
  `istStandard` int(11) NOT NULL,
  `ohneBerechnung` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Stellvertreter-Struktur des Views `postendata`
-- (Siehe unten für die tatsächliche Ansicht)
--
CREATE TABLE `postendata` (
`Nummer` int(11)
,`Postennummer` int(11)
,`Auftragsnummer` int(11)
,`Posten` varchar(8)
,`istStandard` int(11)
,`ohneBerechnung` int(10)
,`ZeitInMinuten` int(11)
,`Beschreibung` mediumtext
);

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
  `product_id` int(11) NOT NULL,
  `groesse` int(16) NOT NULL,
  `farb_id` int(16) NOT NULL,
  `bild` int(16) NOT NULL,
  `menge` int(11) NOT NULL,
  `preis_ek` float NOT NULL,
  `preis` float NOT NULL
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
  `finishingDate` date NOT NULL,
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

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `statistik_auftraege_pro_monat`
--

CREATE TABLE `statistik_auftraege_pro_monat` (
  `id` int(11) NOT NULL,
  `datum` date NOT NULL,
  `anzahl` int(11) NOT NULL,
  `gesamtsumme` int(11) NOT NULL,
  `einkaufssumme` int(11) NOT NULL,
  `istOffen` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `verbesserungen`
--

CREATE TABLE `verbesserungen` (
  `id` int(10) NOT NULL,
  `verbesserungen` text NOT NULL,
  `erledigt` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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

-- --------------------------------------------------------

--
-- Struktur des Views `postendata`
--
DROP TABLE IF EXISTS `postendata`;

CREATE ALGORITHM=UNDEFINED DEFINER=`auftragsmanager`@`%` SQL SECURITY DEFINER VIEW `postendata`  AS  select `posten`.`Nummer` AS `Nummer`,`posten`.`Postennummer` AS `Postennummer`,`posten`.`Auftragsnummer` AS `Auftragsnummer`,`posten`.`Posten` AS `Posten`,`posten`.`istStandard` AS `istStandard`,`posten`.`ohneBerechnung` AS `ohneBerechnung`,`zeit`.`ZeitInMinuten` AS `ZeitInMinuten`,concat(coalesce(`zeit`.`Beschreibung`,''),coalesce(`leistung_posten`.`Beschreibung`,''),convert(coalesce(`produkt_posten`.`Produktnummer`,'') using latin1)) AS `Beschreibung` from (((`posten` left join `zeit` on((`posten`.`Postennummer` = `zeit`.`Postennummer`))) left join `leistung_posten` on((`posten`.`Postennummer` = `leistung_posten`.`Postennummer`))) left join `produkt_posten` on((`posten`.`Postennummer` = `produkt_posten`.`Postennummer`))) ;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `angebot`
--
ALTER TABLE `angebot`
  ADD PRIMARY KEY (`id`);

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
-- Indizes für die Tabelle `attribute`
--
ALTER TABLE `attribute`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `attribute_group`
--
ALTER TABLE `attribute_group`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `attribute_to_product`
--
ALTER TABLE `attribute_to_product`
  ADD PRIMARY KEY (`product_id`,`attribute_id`);

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
-- Indizes für die Tabelle `dateien`
--
ALTER TABLE `dateien`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `dateien_auftraege`
--
ALTER TABLE `dateien_auftraege`
  ADD PRIMARY KEY (`id_datei`,`id_auftrag`);

--
-- Indizes für die Tabelle `dateien_fahrzeuge`
--
ALTER TABLE `dateien_fahrzeuge`
  ADD UNIQUE KEY `id_datei` (`id_datei`,`id_fahrzeug`);

--
-- Indizes für die Tabelle `dateien_motive`
--
ALTER TABLE `dateien_motive`
  ADD UNIQUE KEY `id_datei` (`id_datei`,`id_motive`);

--
-- Indizes für die Tabelle `dateien_produkte`
--
ALTER TABLE `dateien_produkte`
  ADD PRIMARY KEY (`id_datei`,`id_produkt`);

--
-- Indizes für die Tabelle `einkauf`
--
ALTER TABLE `einkauf`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `fahrzeuge`
--
ALTER TABLE `fahrzeuge`
  ADD PRIMARY KEY (`Nummer`);

--
-- Indizes für die Tabelle `fahrzeuge_auftraege`
--
ALTER TABLE `fahrzeuge_auftraege`
  ADD UNIQUE KEY `id_fahrzeug` (`id_fahrzeug`,`id_auftrag`);

--
-- Indizes für die Tabelle `farben`
--
ALTER TABLE `farben`
  ADD PRIMARY KEY (`Nummer`);

--
-- Indizes für die Tabelle `header`
--
ALTER TABLE `header`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `history`
--
ALTER TABLE `history`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `history_type`
--
ALTER TABLE `history_type`
  ADD PRIMARY KEY (`type_id`);

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
-- Indizes für die Tabelle `kunde_aenderungen`
--
ALTER TABLE `kunde_aenderungen`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `kunde_extended`
--
ALTER TABLE `kunde_extended`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kundennummer` (`kundennummer`);

--
-- Indizes für die Tabelle `last_login`
--
ALTER TABLE `last_login`
  ADD PRIMARY KEY (`id`);

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
-- Indizes für die Tabelle `liste`
--
ALTER TABLE `liste`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `listenauswahl`
--
ALTER TABLE `listenauswahl`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `listenpunkt`
--
ALTER TABLE `listenpunkt`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `mitarbeiter`
--
ALTER TABLE `mitarbeiter`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `motive`
--
ALTER TABLE `motive`
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
-- Indizes für die Tabelle `statistik_auftraege_pro_monat`
--
ALTER TABLE `statistik_auftraege_pro_monat`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `verbesserungen`
--
ALTER TABLE `verbesserungen`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `zeit`
--
ALTER TABLE `zeit`
  ADD PRIMARY KEY (`Nummer`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `angebot`
--
ALTER TABLE `angebot`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `angenommen`
--
ALTER TABLE `angenommen`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `ansprechpartner`
--
ALTER TABLE `ansprechpartner`
  MODIFY `Nummer` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `articles`
--
ALTER TABLE `articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT für Tabelle `attachments`
--
ALTER TABLE `attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT für Tabelle `attribute`
--
ALTER TABLE `attribute`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `attribute_group`
--
ALTER TABLE `attribute_group`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `auftrag`
--
ALTER TABLE `auftrag`
  MODIFY `Auftragsnummer` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `dateien`
--
ALTER TABLE `dateien`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `einkauf`
--
ALTER TABLE `einkauf`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT für Tabelle `fahrzeuge`
--
ALTER TABLE `fahrzeuge`
  MODIFY `Nummer` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `farben`
--
ALTER TABLE `farben`
  MODIFY `Nummer` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `history`
--
ALTER TABLE `history`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `history_type`
--
ALTER TABLE `history_type`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT für Tabelle `keywords`
--
ALTER TABLE `keywords`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT für Tabelle `kunde`
--
ALTER TABLE `kunde`
  MODIFY `Kundennummer` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `kundenlogin`
--
ALTER TABLE `kundenlogin`
  MODIFY `Nummer` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `kunde_extended`
--
ALTER TABLE `kunde_extended`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `last_login`
--
ALTER TABLE `last_login`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `leistung`
--
ALTER TABLE `leistung`
  MODIFY `Nummer` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `leistung_posten`
--
ALTER TABLE `leistung_posten`
  MODIFY `Nummer` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `liste`
--
ALTER TABLE `liste`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `listenauswahl`
--
ALTER TABLE `listenauswahl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `listenpunkt`
--
ALTER TABLE `listenpunkt`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT für Tabelle `mitarbeiter`
--
ALTER TABLE `mitarbeiter`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT für Tabelle `motive`
--
ALTER TABLE `motive`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `posten`
--
ALTER TABLE `posten`
  MODIFY `Nummer` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `produkt`
--
ALTER TABLE `produkt`
  MODIFY `Nummer` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `Schrittnummer` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `statistik_auftraege_pro_monat`
--
ALTER TABLE `statistik_auftraege_pro_monat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `verbesserungen`
--
ALTER TABLE `verbesserungen`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `zeit`
--
ALTER TABLE `zeit`
  MODIFY `Nummer` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `kunde_extended`
--
ALTER TABLE `kunde_extended`
  ADD CONSTRAINT `kunde_extended_ibfk_1` FOREIGN KEY (`kundennummer`) REFERENCES `kunde` (`Kundennummer`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

/* Änderungen 16.04.2020 */
CREATE TABLE `farben_auftrag` ( `id_farbe` INT NOT NULL , `id_auftrag` INT NOT NULL ) ENGINE = InnoDB;
CREATE TABLE `adress` ( `id` INT NOT NULL AUTO_INCREMENT , `id_customer` INT NOT NULL , `ort` VARCHAR(64) NOT NULL , `plz` INT(6) NOT NULL , `strasse` VARCHAR(64) NOT NULL , `hausnr` VARCHAR(16) NOT NULL , `zusatz` VARCHAR(64) NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
ALTER TABLE `adress` ADD FOREIGN KEY (`id_customer`) REFERENCES `kunde`(`Kundennummer`) ON DELETE CASCADE ON UPDATE NO ACTION;
ALTER TABLE `adress` ADD `art` INT NOT NULL AFTER `zusatz`;

