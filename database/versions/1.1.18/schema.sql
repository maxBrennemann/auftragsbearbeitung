-- MySQL dump 10.13  Distrib 9.3.0, for macos15.4 (arm64)
--
-- Host: 127.0.0.1    Database: auftragsbearbeitung
-- ------------------------------------------------------
-- Server version	11.7.2-MariaDB-ubu2404

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `address`
--

DROP TABLE IF EXISTS `address`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `address` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_customer` int(11) NOT NULL,
  `ort` varchar(64) NOT NULL,
  `plz` int(6) NOT NULL,
  `strasse` varchar(64) NOT NULL,
  `hausnr` varchar(16) NOT NULL,
  `zusatz` varchar(64) NOT NULL,
  `country` varchar(32) NOT NULL,
  `art` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=202 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `angebot`
--

DROP TABLE IF EXISTS `angebot`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `angebot` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_customer` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `status` varchar(32) DEFAULT NULL,
  `creation_date` datetime NOT NULL,
  `update_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `angenommen`
--

DROP TABLE IF EXISTS `angenommen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `angenommen` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `Bezeichnung` varchar(32) NOT NULL,
  `istAllgemein` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ansprechpartner`
--

DROP TABLE IF EXISTS `ansprechpartner`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ansprechpartner` (
  `Nummer` int(11) NOT NULL AUTO_INCREMENT,
  `Kundennummer` int(11) NOT NULL,
  `Vorname` varchar(16) NOT NULL,
  `Nachname` varchar(16) NOT NULL,
  `Email` varchar(64) NOT NULL,
  `Durchwahl` varchar(16) NOT NULL,
  `Mobiltelefonnummer` varchar(16) DEFAULT NULL,
  PRIMARY KEY (`Nummer`)
) ENGINE=InnoDB AUTO_INCREMENT=181 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `attribute`
--

DROP TABLE IF EXISTS `attribute`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attribute` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `position` int(11) NOT NULL,
  `attribute_group_id` int(11) NOT NULL,
  `value` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `attribute_group`
--

DROP TABLE IF EXISTS `attribute_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attribute_group` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `position` int(11) NOT NULL,
  `attribute_group` varchar(16) NOT NULL,
  `descr` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auftrag`
--

DROP TABLE IF EXISTS `auftrag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auftrag` (
  `Auftragsnummer` int(11) NOT NULL AUTO_INCREMENT,
  `Kundennummer` int(11) NOT NULL,
  `Auftragsbezeichnung` tinytext NOT NULL,
  `Auftragsbeschreibung` text NOT NULL,
  `Auftragstyp` int(11) NOT NULL,
  `Datum` date NOT NULL,
  `Termin` date DEFAULT NULL,
  `Fertigstellung` date DEFAULT '0000-00-00',
  `AngenommenDurch` int(10) NOT NULL,
  `AngenommenPer` int(11) NOT NULL,
  `Ansprechpartner` int(11) NOT NULL,
  `Rechnungsnummer` int(11) NOT NULL DEFAULT 0,
  `Bezahlt` int(11) NOT NULL DEFAULT 0,
  `archiviert` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`Auftragsnummer`)
) ENGINE=InnoDB AUTO_INCREMENT=730 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary view structure for view `auftragssumme`
--

DROP TABLE IF EXISTS `auftragssumme`;
/*!50001 DROP VIEW IF EXISTS `auftragssumme`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `auftragssumme` AS SELECT 
 1 AS `orderPrice`,
 1 AS `id`,
 1 AS `Datum`,
 1 AS `Fertigstellung`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `auftragssumme_view`
--

DROP TABLE IF EXISTS `auftragssumme_view`;
/*!50001 DROP VIEW IF EXISTS `auftragssumme_view`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `auftragssumme_view` AS SELECT 
 1 AS `price`,
 1 AS `id`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `auftragstyp`
--

DROP TABLE IF EXISTS `auftragstyp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auftragstyp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Auftragstyp` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `category`
--

DROP TABLE IF EXISTS `category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `parent` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `color`
--

DROP TABLE IF EXISTS `color`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `color` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `color_name` varchar(64) NOT NULL,
  `hex_value` varchar(6) NOT NULL,
  `short_name` varchar(64) NOT NULL,
  `producer` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=149 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `color_auftrag`
--

DROP TABLE IF EXISTS `color_auftrag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `color_auftrag` (
  `id_color` int(11) NOT NULL,
  `id_auftrag` int(11) NOT NULL,
  PRIMARY KEY (`id_color`,`id_auftrag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `color_settings`
--

DROP TABLE IF EXISTS `color_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `color_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `color` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `customer_changelog`
--

DROP TABLE IF EXISTS `customer_changelog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_changelog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `changed_at` datetime DEFAULT current_timestamp(),
  `valid_until` datetime DEFAULT NULL,
  `field_changed` varchar(64) NOT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `changed_by_user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `customer_changelog_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `kunde` (`Kundennummer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dateien`
--

DROP TABLE IF EXISTS `dateien`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dateien` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dateiname` varchar(80) DEFAULT NULL,
  `originalname` varchar(200) NOT NULL,
  `date` datetime NOT NULL,
  `typ` varchar(16) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2573 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dateien_auftraege`
--

DROP TABLE IF EXISTS `dateien_auftraege`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dateien_auftraege` (
  `id_datei` int(11) NOT NULL,
  `id_auftrag` int(11) NOT NULL,
  PRIMARY KEY (`id_datei`,`id_auftrag`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dateien_fahrzeuge`
--

DROP TABLE IF EXISTS `dateien_fahrzeuge`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dateien_fahrzeuge` (
  `id_datei` int(11) NOT NULL,
  `id_fahrzeug` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dateien_posten`
--

DROP TABLE IF EXISTS `dateien_posten`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dateien_posten` (
  `id_file` int(11) NOT NULL,
  `id_posten` int(11) NOT NULL,
  PRIMARY KEY (`id_file`,`id_posten`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dateien_produkte`
--

DROP TABLE IF EXISTS `dateien_produkte`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dateien_produkte` (
  `id_datei` int(11) NOT NULL,
  `id_produkt` int(11) NOT NULL,
  PRIMARY KEY (`id_datei`,`id_produkt`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `einkauf`
--

DROP TABLE IF EXISTS `einkauf`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `einkauf` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fahrzeuge`
--

DROP TABLE IF EXISTS `fahrzeuge`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fahrzeuge` (
  `Nummer` int(10) NOT NULL AUTO_INCREMENT,
  `Kundennummer` int(11) NOT NULL,
  `Kennzeichen` varchar(32) NOT NULL,
  `Fahrzeug` varchar(64) NOT NULL,
  PRIMARY KEY (`Nummer`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fahrzeuge_auftraege`
--

DROP TABLE IF EXISTS `fahrzeuge_auftraege`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fahrzeuge_auftraege` (
  `id_fahrzeug` int(11) NOT NULL,
  `id_auftrag` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `footer_links`
--

DROP TABLE IF EXISTS `footer_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `footer_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `link` varchar(64) NOT NULL,
  `title` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `history`
--

DROP TABLE IF EXISTS `history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `history` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `orderid` int(11) NOT NULL,
  `number` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `state` varchar(16) NOT NULL,
  `insertstamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `member_id` int(11) NOT NULL,
  `alternative_text` tinytext DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3546 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `history_type`
--

DROP TABLE IF EXISTS `history_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `history_type` (
  `type_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  PRIMARY KEY (`type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `info_texte`
--

DROP TABLE IF EXISTS `info_texte`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `info_texte` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `info` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `invoice`
--

DROP TABLE IF EXISTS `invoice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_number` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `contact_id` int(11) DEFAULT NULL,
  `address_id` int(11) DEFAULT NULL,
  `status` varchar(16) NOT NULL DEFAULT 'draft',
  `creation_date` date NOT NULL,
  `performance_date` date NOT NULL,
  `payment_date` date DEFAULT NULL,
  `finalized_date` date DEFAULT NULL,
  `amount` int(11) NOT NULL,
  `payment_type` varchar(64) NOT NULL DEFAULT 'unbezahlt',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1382 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `invoice_alt_names`
--

DROP TABLE IF EXISTS `invoice_alt_names`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoice_alt_names` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_invoice` int(11) NOT NULL,
  `text` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `invoice_number_tracker`
--

DROP TABLE IF EXISTS `invoice_number_tracker`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoice_number_tracker` (
  `id` int(11) NOT NULL,
  `last_used_number` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `invoice_text`
--

DROP TABLE IF EXISTS `invoice_text`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoice_text` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_invoice` int(11) NOT NULL,
  `text` text NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kunde`
--

DROP TABLE IF EXISTS `kunde`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kunde` (
  `Kundennummer` int(10) NOT NULL AUTO_INCREMENT,
  `Firmenname` varchar(256) NOT NULL,
  `Anrede` int(1) NOT NULL,
  `Vorname` varchar(32) NOT NULL,
  `Nachname` varchar(32) NOT NULL,
  `Email` varchar(64) NOT NULL,
  `TelefonFestnetz` varchar(16) NOT NULL,
  `TelefonMobil` varchar(16) NOT NULL,
  `Website` varchar(64) DEFAULT NULL,
  `fax` varchar(32) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `id_address_primary` int(11) DEFAULT NULL,
  PRIMARY KEY (`Kundennummer`)
) ENGINE=InnoDB AUTO_INCREMENT=191 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `leistung`
--

DROP TABLE IF EXISTS `leistung`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leistung` (
  `Nummer` int(11) NOT NULL AUTO_INCREMENT,
  `Bezeichnung` varchar(32) NOT NULL,
  `Beschreibung` text NOT NULL,
  `Quelle` varchar(64) NOT NULL,
  `Aufschlag` int(11) NOT NULL,
  PRIMARY KEY (`Nummer`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `leistung_posten`
--

DROP TABLE IF EXISTS `leistung_posten`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leistung_posten` (
  `Nummer` int(11) NOT NULL AUTO_INCREMENT,
  `Leistungsnummer` int(11) NOT NULL,
  `Postennummer` int(11) NOT NULL,
  `Beschreibung` text NOT NULL,
  `Einkaufspreis` float NOT NULL,
  `SpeziefischerPreis` float NOT NULL,
  `meh` varchar(64) NOT NULL,
  `qty` varchar(64) NOT NULL,
  PRIMARY KEY (`Nummer`)
) ENGINE=InnoDB AUTO_INCREMENT=1861 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `login_history`
--

DROP TABLE IF EXISTS `login_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `login_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `loginstamp` datetime NOT NULL,
  `user_login_key_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3242 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `manual`
--

DROP TABLE IF EXISTS `manual`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `manual` (
  `id` int(11) NOT NULL,
  `page` varchar(64) NOT NULL,
  `intent` varchar(64) NOT NULL,
  `info` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `migration_tracker`
--

DROP TABLE IF EXISTS `migration_tracker`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migration_tracker` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `migration_name` varchar(255) NOT NULL,
  `migration_date` date DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `module_sticker_accessoires`
--

DROP TABLE IF EXISTS `module_sticker_accessoires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `module_sticker_accessoires` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_sticker` int(11) NOT NULL,
  `type` enum('aufkleber','wandtattoo','textil','') DEFAULT NULL,
  `title` varchar(256) DEFAULT NULL,
  `id_product_reference` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=416 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `module_sticker_categories`
--

DROP TABLE IF EXISTS `module_sticker_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `module_sticker_categories` (
  `stickerId` int(11) NOT NULL,
  `categoryId` int(11) NOT NULL,
  PRIMARY KEY (`stickerId`,`categoryId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `module_sticker_changelog`
--

DROP TABLE IF EXISTS `module_sticker_changelog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `module_sticker_changelog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_sticker` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `rowId` int(11) NOT NULL,
  `table` varchar(64) NOT NULL,
  `column` varchar(32) NOT NULL,
  `newValue` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6500 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `module_sticker_chatgpt`
--

DROP TABLE IF EXISTS `module_sticker_chatgpt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `module_sticker_chatgpt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idSticker` int(11) NOT NULL,
  `creationDate` date NOT NULL,
  `chatgptResponse` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `jsonResponse` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `stickerType` enum('aufkleber','wandtattoo','textil') NOT NULL,
  `textType` enum('short','long') NOT NULL,
  `additionalQuery` text NOT NULL,
  `textStyle` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `module_sticker_exports`
--

DROP TABLE IF EXISTS `module_sticker_exports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `module_sticker_exports` (
  `idSticker` int(11) NOT NULL,
  `facebook` int(11) DEFAULT NULL,
  `google` int(11) DEFAULT NULL,
  `amazon` int(11) DEFAULT NULL,
  `etsy` int(11) DEFAULT NULL,
  `ebay` int(11) DEFAULT NULL,
  `pinterest` int(11) DEFAULT NULL,
  PRIMARY KEY (`idSticker`),
  CONSTRAINT `module_sticker_exports_ibfk_1` FOREIGN KEY (`idSticker`) REFERENCES `module_sticker_sticker_data` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `module_sticker_image`
--

DROP TABLE IF EXISTS `module_sticker_image`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `module_sticker_image` (
  `id_datei` int(11) NOT NULL,
  `id_motiv` int(11) NOT NULL,
  `image_sort` enum('general','aufkleber','wandtattoo','textil','textilsvg') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `id_product` int(11) DEFAULT NULL,
  `id_image_shop` int(11) DEFAULT NULL,
  `description` varchar(125) DEFAULT NULL,
  `image_order` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_datei`),
  CONSTRAINT `ref_dateien` FOREIGN KEY (`id_datei`) REFERENCES `dateien` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `module_sticker_log_id`
--

DROP TABLE IF EXISTS `module_sticker_log_id`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `module_sticker_log_id` (
  `id_changelog` int(11) NOT NULL,
  `id_sticker` int(11) NOT NULL,
  UNIQUE KEY `id_changelog` (`id_changelog`,`id_sticker`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `module_sticker_search_data`
--

DROP TABLE IF EXISTS `module_sticker_search_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `module_sticker_search_data` (
  `id` int(11) NOT NULL,
  `site` varchar(128) NOT NULL,
  `date` date NOT NULL,
  `clicks` int(11) NOT NULL,
  `impressions` int(11) NOT NULL,
  `ctr` float NOT NULL,
  `position` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `module_sticker_sizes`
--

DROP TABLE IF EXISTS `module_sticker_sizes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `module_sticker_sizes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_sticker` int(11) NOT NULL,
  `width` int(11) NOT NULL,
  `height` int(11) DEFAULT NULL,
  `price` int(11) DEFAULT NULL,
  `price_default` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3488 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `module_sticker_sticker_data`
--

DROP TABLE IF EXISTS `module_sticker_sticker_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `module_sticker_sticker_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `name` varchar(256) NOT NULL,
  `is_plotted` tinyint(1) NOT NULL DEFAULT 0,
  `is_short_time` tinyint(1) NOT NULL DEFAULT 0,
  `is_long_time` tinyint(1) NOT NULL DEFAULT 0,
  `is_walldecal` tinyint(1) NOT NULL DEFAULT 0,
  `is_multipart` tinyint(1) NOT NULL DEFAULT 0,
  `is_shirtcollection` tinyint(1) NOT NULL DEFAULT 0,
  `is_colorable` tinyint(1) NOT NULL DEFAULT 0,
  `is_customizable` tinyint(1) NOT NULL DEFAULT 0,
  `is_for_configurator` tinyint(1) NOT NULL DEFAULT 0,
  `price_class` int(11) NOT NULL DEFAULT 0,
  `size_summary` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `creation_date` date DEFAULT current_timestamp(),
  `directory_name` varchar(256) DEFAULT NULL,
  `is_revised` int(11) DEFAULT 0,
  `is_marked` tinyint(1) NOT NULL DEFAULT 0,
  `additional_info` text DEFAULT NULL,
  `additional_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=898 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb3 */ ;
/*!50003 SET character_set_results = utf8mb3 */ ;
/*!50003 SET collation_connection  = utf8mb3_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`auftragsmanager`@`%`*/ /*!50003 TRIGGER create_sizes
        AFTER INSERT ON module_sticker_sticker_data
        FOR EACH ROW
        BEGIN
            INSERT INTO module_sticker_sizes (id_sticker, width, height, price) VALUES (NEW.id, 20, 0, 0);
            INSERT INTO module_sticker_sizes (id_sticker, width, height, price) VALUES (NEW.id, 50, 0, 0);
            INSERT INTO module_sticker_sizes (id_sticker, width, height, price) VALUES (NEW.id, 100, 0, 0);
            INSERT INTO module_sticker_sizes (id_sticker, width, height, price) VALUES (NEW.id, 150, 0, 0);
            INSERT INTO module_sticker_sizes (id_sticker, width, height, price) VALUES (NEW.id, 200, 0, 0);
            INSERT INTO module_sticker_sizes (id_sticker, width, height, price) VALUES (NEW.id, 250, 0, 0);
            INSERT INTO module_sticker_sizes (id_sticker, width, height, price) VALUES (NEW.id, 300, 0, 0);
            INSERT INTO module_sticker_sizes (id_sticker, width, height, price) VALUES (NEW.id, 400, 0, 0);
            INSERT INTO module_sticker_sizes (id_sticker, width, height, price) VALUES (NEW.id, 500, 0, 0);
            INSERT INTO module_sticker_sizes (id_sticker, width, height, price) VALUES (NEW.id, 600, 0, 0);
            INSERT INTO module_sticker_sizes (id_sticker, width, height, price) VALUES (NEW.id, 700, 0, 0);
            INSERT INTO module_sticker_sizes (id_sticker, width, height, price) VALUES (NEW.id, 800, 0, 0);
            INSERT INTO module_sticker_sizes (id_sticker, width, height, price) VALUES (NEW.id, 900, 0, 0);
            INSERT INTO module_sticker_sizes (id_sticker, width, height, price) VALUES (NEW.id, 1000, 0, 0);
            INSERT INTO module_sticker_sizes (id_sticker, width, height, price) VALUES (NEW.id, 1100, 0, 0);
            INSERT INTO module_sticker_sizes (id_sticker, width, height, price) VALUES (NEW.id, 1200, 0, 0);
        END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `module_sticker_sticker_tag`
--

DROP TABLE IF EXISTS `module_sticker_sticker_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `module_sticker_sticker_tag` (
  `id_tag` int(11) NOT NULL,
  `id_sticker` int(11) NOT NULL,
  PRIMARY KEY (`id_tag`,`id_sticker`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `module_sticker_sticker_tag_group`
--

DROP TABLE IF EXISTS `module_sticker_sticker_tag_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `module_sticker_sticker_tag_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `module_sticker_sticker_tag_group_match`
--

DROP TABLE IF EXISTS `module_sticker_sticker_tag_group_match`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `module_sticker_sticker_tag_group_match` (
  `idGroup` int(11) NOT NULL,
  `idTag` int(11) NOT NULL,
  PRIMARY KEY (`idGroup`,`idTag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `module_sticker_tags`
--

DROP TABLE IF EXISTS `module_sticker_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `module_sticker_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_tag_shop` int(11) DEFAULT NULL,
  `content` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `content` (`content`)
) ENGINE=InnoDB AUTO_INCREMENT=1807 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `module_sticker_textiles`
--

DROP TABLE IF EXISTS `module_sticker_textiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `module_sticker_textiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_module_textile` int(11) NOT NULL,
  `id_product` int(11) NOT NULL,
  `activated` tinyint(1) NOT NULL DEFAULT 0,
  `price` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `module_sticker_texts`
--

DROP TABLE IF EXISTS `module_sticker_texts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `module_sticker_texts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_sticker` int(11) NOT NULL,
  `type` varchar(16) DEFAULT NULL,
  `target` enum('aufkleber','wandtattoo','textil') NOT NULL DEFAULT 'aufkleber',
  `content` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_sticker` (`id_sticker`,`type`,`target`)
) ENGINE=InnoDB AUTO_INCREMENT=667 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `navigation`
--

DROP TABLE IF EXISTS `navigation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `navigation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `link` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `parent` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `notes`
--

DROP TABLE IF EXISTS `notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderId` int(11) NOT NULL,
  `title` varchar(128) NOT NULL,
  `note` text DEFAULT NULL,
  `creation_date` date NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=492 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `offer`
--

DROP TABLE IF EXISTS `offer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `offer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `creation_date` datetime DEFAULT NULL,
  `state` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(11) NOT NULL,
  `description` text NOT NULL,
  `paymentDate` date NOT NULL,
  `creationDate` date NOT NULL,
  `amount` float NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `posten`
--

DROP TABLE IF EXISTS `posten`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `posten` (
  `Postennummer` int(11) NOT NULL AUTO_INCREMENT,
  `Auftragsnummer` int(11) DEFAULT NULL,
  `position` int(11) DEFAULT NULL,
  `angebotsNr` int(11) NOT NULL DEFAULT 0,
  `rechnungsNr` int(11) NOT NULL DEFAULT 0,
  `Posten` varchar(8) NOT NULL,
  `istStandard` int(11) NOT NULL DEFAULT 0,
  `ohneBerechnung` int(10) NOT NULL,
  `discount` int(11) NOT NULL,
  `isInvoice` tinyint(1) NOT NULL,
  PRIMARY KEY (`Postennummer`)
) ENGINE=InnoDB AUTO_INCREMENT=2121 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary view structure for view `postendata`
--

DROP TABLE IF EXISTS `postendata`;
/*!50001 DROP VIEW IF EXISTS `postendata`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `postendata` AS SELECT 
 1 AS `Postennummer`,
 1 AS `Auftragsnummer`,
 1 AS `position`,
 1 AS `angebotsNr`,
 1 AS `rechnungsNr`,
 1 AS `Posten`,
 1 AS `istStandard`,
 1 AS `ohneBerechnung`,
 1 AS `discount`,
 1 AS `isInvoice`,
 1 AS `ZeitInMinuten`,
 1 AS `Beschreibung`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `product_attribute_combination`
--

DROP TABLE IF EXISTS `product_attribute_combination`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_attribute_combination` (
  `id_produkt_attribute` int(11) NOT NULL,
  `attribute_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id_produkt_attribute`,`attribute_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `product_combination`
--

DROP TABLE IF EXISTS `product_combination`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_combination` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_produkt` int(11) NOT NULL,
  `amount` int(11) DEFAULT NULL,
  `purchasing_price` int(11) DEFAULT NULL,
  `price` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `product_compact`
--

DROP TABLE IF EXISTS `product_compact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_compact` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `postennummer` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `marke` varchar(32) NOT NULL,
  `price` float NOT NULL,
  `purchasing_price` float NOT NULL,
  `description` text NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=202 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `product_image`
--

DROP TABLE IF EXISTS `product_image`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_image` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_product` int(11) NOT NULL,
  `id_file` int(11) NOT NULL,
  `position` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `produkt`
--

DROP TABLE IF EXISTS `produkt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `produkt` (
  `Nummer` int(11) NOT NULL AUTO_INCREMENT,
  `Marke` varchar(32) NOT NULL,
  `Preis` float NOT NULL,
  `Einkaufspreis` float NOT NULL,
  `Bezeichnung` varchar(64) NOT NULL,
  `Beschreibung` text NOT NULL,
  `Bild` varchar(16) DEFAULT NULL,
  `einkaufs_id` int(11) NOT NULL,
  `id_category` int(11) DEFAULT NULL,
  PRIMARY KEY (`Nummer`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `produkt_posten`
--

DROP TABLE IF EXISTS `produkt_posten`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `produkt_posten` (
  `Nummer` int(11) NOT NULL AUTO_INCREMENT,
  `Produktnummer` int(11) NOT NULL,
  `Postennummer` int(11) NOT NULL,
  `Anzahl` int(11) NOT NULL,
  PRIMARY KEY (`Nummer`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `recurring_payments`
--

DROP TABLE IF EXISTS `recurring_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recurring_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `short_description` varchar(128) NOT NULL,
  `description` text NOT NULL,
  `date` date NOT NULL,
  `recurring` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `schritte`
--

DROP TABLE IF EXISTS `schritte`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `schritte` (
  `Schrittnummer` int(11) NOT NULL AUTO_INCREMENT,
  `Auftragsnummer` int(11) NOT NULL,
  `istAllgemein` int(11) NOT NULL,
  `Bezeichnung` text NOT NULL,
  `Datum` date NOT NULL,
  `Priority` int(11) NOT NULL,
  `finishingDate` date DEFAULT '0000-00-00',
  `istErledigt` int(11) NOT NULL,
  PRIMARY KEY (`Schrittnummer`)
) ENGINE=InnoDB AUTO_INCREMENT=312 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `schritte_vordefiniert`
--

DROP TABLE IF EXISTS `schritte_vordefiniert`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `schritte_vordefiniert` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Bezeichnung` text NOT NULL,
  `Leistungsnummer` int(11) NOT NULL,
  `Auftragstyp` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(32) NOT NULL,
  `content` varchar(128) NOT NULL,
  `defaultValue` varchar(64) DEFAULT NULL,
  `isBool` tinyint(1) NOT NULL DEFAULT 0,
  `isNullable` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `statistik_auftraege_pro_monat`
--

DROP TABLE IF EXISTS `statistik_auftraege_pro_monat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `statistik_auftraege_pro_monat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` date NOT NULL,
  `anzahl` int(11) NOT NULL,
  `gesamtsumme` int(11) NOT NULL,
  `einkaufssumme` int(11) NOT NULL,
  `istOffen` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `task_executions`
--

DROP TABLE IF EXISTS `task_executions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_executions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_name` varchar(100) NOT NULL,
  `status` varchar(32) NOT NULL,
  `result` text DEFAULT NULL,
  `started_at` datetime NOT NULL,
  `finished_at` datetime DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lastname` varchar(32) NOT NULL,
  `prename` varchar(32) NOT NULL,
  `username` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(64) NOT NULL,
  `password` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `validated` tinyint(1) NOT NULL DEFAULT 0,
  `role` int(11) NOT NULL,
  `max_working_hours` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_devices`
--

DROP TABLE IF EXISTS `user_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `md_hash` varchar(64) NOT NULL,
  `ip_address` varchar(64) NOT NULL,
  `browser_agent` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `last_usage` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_device_name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `os` varchar(32) NOT NULL,
  `browser` varchar(32) NOT NULL,
  `device_type` enum('mobile','tablet','desktop','unrecognized') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_login_key`
--

DROP TABLE IF EXISTS `user_login_key`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_login_key` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `login_key` char(12) NOT NULL,
  `expiration_date` date NOT NULL,
  `user_device_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2135 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_notifications`
--

DROP TABLE IF EXISTS `user_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `initiator` int(11) NOT NULL,
  `specific_id` int(11) NOT NULL,
  `type` int(32) NOT NULL,
  `content` varchar(128) NOT NULL,
  `ischecked` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=734 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_roles`
--

DROP TABLE IF EXISTS `user_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(64) NOT NULL,
  `role_description` tinytext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_timetracking`
--

DROP TABLE IF EXISTS `user_timetracking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_timetracking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `started_at` datetime NOT NULL,
  `stopped_at` datetime NOT NULL,
  `is_pending` tinyint(1) DEFAULT NULL,
  `duration_ms` int(11) NOT NULL,
  `task` varchar(128) NOT NULL,
  `edit_log` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=112 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_validate_mail`
--

DROP TABLE IF EXISTS `user_validate_mail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_validate_mail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `mail_key` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `wiki_articles`
--

DROP TABLE IF EXISTS `wiki_articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wiki_articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` text NOT NULL,
  `title` varchar(128) NOT NULL,
  `keywords` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `zeit`
--

DROP TABLE IF EXISTS `zeit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `zeit` (
  `Nummer` int(11) NOT NULL AUTO_INCREMENT,
  `Postennummer` int(11) NOT NULL,
  `ZeitInMinuten` int(11) NOT NULL,
  `Stundenlohn` float NOT NULL,
  `Beschreibung` text NOT NULL,
  PRIMARY KEY (`Nummer`)
) ENGINE=InnoDB AUTO_INCREMENT=311 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `zeiterfassung`
--

DROP TABLE IF EXISTS `zeiterfassung`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `zeiterfassung` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_zeit` int(11) NOT NULL,
  `from_time` int(11) NOT NULL,
  `to_time` int(11) NOT NULL,
  `date` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=179 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping routines for database 'auftragsbearbeitung'
--
