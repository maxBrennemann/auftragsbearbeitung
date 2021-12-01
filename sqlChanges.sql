/* Änderungen 07.12.2019 */
ALTER TABLE ansprechpartner ADD Mobiltelefonnummer VARCHAR(16);
ALTER TABLE kunde ADD Website VARCHAR(64) NOT NULL;

CREATE TABLE kunde_aenderungen (
    id int(10) NOT NULL,
    gueltigBis DATE NOT NULL,
    Kundennummer int(10) NOT NULL,
    Firmenname varchar(64) NOT NULL,
    Anrede int(1) NOT NULL,
    Vorname varchar(32) NOT NULL,
    Nachname varchar(32) NOT NULL,
    Straße varchar(32) NOT NULL,
    Hausnummer int(11) NOT NULL,
    Postleitzahl int(11) NOT NULL,
    Ort varchar(32) NOT NULL,
    Email varchar(32) NOT NULL,
    TelefonFestnetz varchar(16) NOT NULL,
    TelefonMobil varchar(16) NOT NULL,
    Website varchar(64) NOT NULL,
    PRIMARY KEY (id)
);

/* Änderungen 26.12.2019 */
CREATE table kunde_extended (
	id INTEGER,
    notizen TEXT,
    PRIMARY KEY(id)
);

ALTER TABLE kunde_extended CHANGE id id INT(10) AUTO_INCREMENT;
ALTER TABLE kunde_extended ADD kundennummer INT(10) NOT NULL;
ALTER TABLE `kunde_extended` ADD FOREIGN KEY (`kundennummer`) REFERENCES `kunde`(`Kundennummer`) ON DELETE NO ACTION ON UPDATE NO ACTION;

/* Änderungen 28.12.2019 */
CREATE TRIGGER createRowForNewCustomer AFTER INSERT ON kunde FOR EACH ROW INSERT INTO kunde_extended SET kunde_extended.kundennummer = NEW.Kundennummer;

/* Änderungen 06.01.2020 */
ALTER TABLE farben CHANGE Nummer Nummer INT(10) AUTO_INCREMENT;
ALTER TABLE farben ADD Hersteller varchar(64) NOT NULL;
INSERT INTO articles (id, articleUrl, pageName, src) VALUES (16, 'offeneRechnungen.php', 'Offene Rechnungen', "offene-rechnungen");

/* Änderungen 08.01.2020 */
ALTER TABLE posten ADD ohneBerechnung INT(10) NOT NULL;

/* Änderungen 10.01.2020 */
ALTER TABLE dateien DROP FOREIGN KEY dateien_ibfk_1;
ALTER TABLE `dateien` DROP `auftragsnummer`;

/* Änderungen 11.01.2020 */
ALTER TABLE kunde_extended ADD Faxnummer VARCHAR(32) NOT NULL;
ALTER TABLE dateien ADD `date` DATE NOT NULL;
ALTER TABLE dateien ADD typ VARCHAR(16) NOT NULL;

/* Änderungen 12.01.2020 */
CREATE TABLE `auftragsmanager`.`fahrzeuge_auftraege` ( `id_fahrzeug` INT NOT NULL , `id_auftrag` INT NOT NULL );
ALTER TABLE `auftragsmanager`.`fahrzeuge_auftraege` ADD UNIQUE (`id_fahrzeug`, `id_auftrag`);
CREATE TABLE `auftragsmanager`.`dateien_fahrzeuge` ( `id_datei` INT NOT NULL , `id_fahrzeug` INT NOT NULL );
ALTER TABLE `auftragsmanager`.`dateien_fahrzeuge` ADD UNIQUE (`id_datei`, `id_fahrzeug`);
INSERT INTO `articles` (`id`, `articleUrl`, `pageName`, `src`) VALUES ('17', 'fahrzeug.php', 'Fahrzeuge', 'fahrzeug');

/* Änderungen 14.01.2020 */
CREATE TABLE `auftragsmanager`.`history` ( `id` INT NOT NULL , `number` INT NOT NULL , `type` INT NOT NULL , `insertstamp` TIMESTAMP NOT NULL ) ENGINE = InnoDB;
ALTER TABLE `history` ADD PRIMARY KEY(`id`);
ALTER TABLE history CHANGE id id INT(10) AUTO_INCREMENT;
ALTER TABLE `history` ADD `orderid` INT NOT NULL AFTER `id`;
CREATE TABLE `auftragsmanager`.`history_type` ( `type_id` INT NOT NULL AUTO_INCREMENT , `name` VARCHAR(32) NOT NULL , PRIMARY KEY (`type_id`)) ENGINE = InnoDB;
INSERT INTO `history_type` (`type_id`, `name`) VALUES (NULL, 'posten'), (NULL, 'schritte'), (NULL, 'fahrzeuge'), (NULL, 'dateien'), (NULL, 'auftrag'), (NULL, 'angebot');
ALTER TABLE `history` ADD `state` VARCHAR(16) NOT NULL AFTER `type`;
alter table history change insertstamp insertstamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

/* Änderungen 20.2.2020 */
INSERT INTO `auftragstyp` (`id`, `Auftragstyp`) VALUES (0, 'Fahrzeugbeschriftung');
CREATE VIEW postendata AS
  SELECT posten.*, zeit.ZeitInMinuten, CONCAT(COALESCE(zeit.Beschreibung, ''), COALESCE(leistung_posten.Beschreibung, ''), COALESCE(produkt_posten.Produktnummer, '')) AS Beschreibung
  FROM posten
  LEFT JOIN zeit ON posten.Postennummer = zeit.Postennummer
  LEFT JOIN leistung_posten ON posten.Postennummer = leistung_posten.Postennummer
  LEFT JOIN produkt_posten ON posten.Postennummer = produkt_posten.Postennummer;
update history_type set name = 'Posten' where type_id = 1;
update history_type set name = 'Schritt' where type_id = 2;
ALTER TABLE `auftrag` ADD `archiviert` INT NOT NULL AFTER `Bezahlt`;
ALTER TABLE `auftrag` CHANGE `archiviert` `archiviert` INT(11) NOT NULL DEFAULT '1';

/* Änderungen 2.03.2020 */
ALTER TABLE `auftrag` CHANGE `Auftragstyp` `Auftragstyp` INT(11) NOT NULL;
INSERT INTO `auftragstyp` (`id`, `Auftragstyp`) VALUES (3, 'Digitaldruck');

/* Änderungen 4.03.2020 */
INSERT INTO `attachments` (`id`, `articleId`, `anchor`, `fileSrc`, `fileName`, `fileType`) VALUES (NULL, '14', 'head', 'attribute.js', '0', 'js');

/* Änderungen 16.03.2020 */
CREATE TABLE `angebot` ( `id` INT NOT NULL AUTO_INCREMENT , `kdnr` INT NOT NULL , `status` INT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
ALTER TABLE `posten` ADD `angebotsNr` INT NOT NULL AFTER `Auftragsnummer`;

/* Änderungen 17.03.2020 */
CREATE TABLE `last_login` ( `id` INT NOT NULL AUTO_INCREMENT , `id_member` INT NOT NULL , `loginstamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , PRIMARY KEY (`id`)) ENGINE = InnoDB;
INSERT INTO `articles` (`id`, `articleUrl`, `pageName`, `src`) VALUES (NULL, 'addSticker.php', 'Motive', 'sticker');
INSERT INTO `articles` (`id`, `articleUrl`, `pageName`, `src`) VALUES (NULL, 'listmaker.php', 'Listen', 'listmaker');
INSERT INTO `attachments` (`id`, `articleId`, `anchor`, `fileSrc`, `fileName`, `fileType`) VALUES (NULL, '19', 'head', 'listmaker.js', '0', 'js');

/* Änderugen 19.03.2020 */
CREATE TABLE `liste` ( `id` INT NOT NULL AUTO_INCREMENT , `name` VARCHAR(32) NOT NULL , `zugehoerigkeit` INT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
CREATE TABLE `listenpunkt` ( `id` INT NOT NULL AUTO_INCREMENT , `listenid` INT NOT NULL , `text` VARCHAR(64) NOT NULL , `art` INT NOT NULL , `ordnung` INT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
CREATE TABLE `listenauswahl` ( `id` INT NOT NULL AUTO_INCREMENT , `listenpunktid` INT NOT NULL , `text` VARCHAR(64) NOT NULL , `ordnung` INT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
ALTER TABLE `listenauswahl` CHANGE `text` `bezeichnung` VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;

/* Änderugen 28.03.2020 */
CREATE TABLE `dateien_motive` ( `id_datei` INT NOT NULL , `id_motive` INT NOT NULL );
ALTER TABLE `dateien_motive` ADD UNIQUE (`id_datei`, `id_motive`);
CREATE TABLE `motive` ( `id` INT NOT NULL AUTO_INCREMENT , `name` VARCHAR(64) NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;

/* Änderugen 06.04.2020 */
INSERT INTO attachments (`articleId`, `anchor`, `fileSrc`, `fileName`, `fileType`) VALUES (8, 'head', 'list.js', 0, 'js');

/* Änderungen 08.04.2020 */
ALTER TABLE `produkt_varianten` CHANGE `Produktnummer` `varianten_nummer` INT(11) NOT NULL;
ALTER TABLE `produkt_varianten` CHANGE `Groesse` `groesse` INT(16) NOT NULL;
ALTER TABLE `produkt_varianten` CHANGE `Farbe` `farb_id` INT(16) NOT NULL;
ALTER TABLE `produkt_varianten` CHANGE `Bild` `bild` INT(16) NOT NULL;
ALTER TABLE `produkt_varianten` ADD `menge` INT NOT NULL AFTER `bild`, ADD `preis` FLOAT NOT NULL AFTER `menge`;
ALTER TABLE `produkt_varianten` ADD `preis_ek` FLOAT NOT NULL AFTER `menge`;

/* Änderungen 13.04.2020 */
ALTER TABLE `produkt_varianten` CHANGE `varianten_nummer` `product_id` INT(11) NOT NULL;

/* Änderungen 14.04.2020 */
ALTER TABLE `schritte` ADD `finishingDate` DATE NOT NULL AFTER `Priority`;

/* Änderungen 16.04.2020 */
CREATE TABLE `auftragsbearbeitung`.`farben_auftrag` ( `id_farbe` INT NOT NULL , `id_auftrag` INT NOT NULL ) ENGINE = InnoDB;
CREATE TABLE `auftragsbearbeitung`.`adress` ( `id` INT NOT NULL AUTO_INCREMENT , `id_customer` INT NOT NULL , `ort` VARCHAR(64) NOT NULL , `plz` INT(6) NOT NULL , `strasse` VARCHAR(64) NOT NULL , `hausnr` VARCHAR(16) NOT NULL , `zusatz` VARCHAR(64) NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
ALTER TABLE `adress` ADD FOREIGN KEY (`id_customer`) REFERENCES `kunde`(`Kundennummer`) ON DELETE CASCADE ON UPDATE NO ACTION;
ALTER TABLE `adress` ADD `art` INT NOT NULL AFTER `zusatz`;

/* Änderungen 14.08.2020 */
ALTER TABLE `kunde` CHANGE `Hausnummer` `Hausnummer` VARCHAR(10) NOT NULL;
ALTER TABLE `ansprechpartner` CHANGE `Email` `Email` VARCHAR(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
CREATE TABLE `auftragsmanager`.`product_compact` ( `id` INT NOT NULL AUTO_INCREMENT , `marke` VARCHAR(32) NOT NULL , `price` FLOAT NOT NULL , `purchasing_price` FLOAT NOT NULL , `description` TEXT NOT NULL , `name` VARCHAR(64) NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
ALTER TABLE `produkt` CHANGE `Preis` `Preis` FLOAT(11) NOT NULL;
ALTER TABLE `produkt` CHANGE `Einkaufspreis` `Einkaufspreis` FLOAT(11) NOT NULL;
ALTER TABLE `produkt` ADD `einkaufs_id` INT NOT NULL AFTER `Bild`;

/* Änderungen 17.09.2020 */
INSERT INTO attachments (`articleId`, `anchor`, `fileSrc`, `fileName`, `fileType`) VALUES (5, 'head', 'angebot.js', 0, 'js');
INSERT INTO `articles` (`id`, `articleUrl`, `pageName`, `src`) VALUES (NULL, 'pdf', 'pdf', 'pdf');

/* Änderungen 23.09.2020 */
INSERT INTO `attachments` (`id`, `articleId`, `anchor`, `fileSrc`, `fileName`, `fileType`) VALUES (NULL, '16', 'head', 'offene-rechnungen.js', '0', 'js');

/* Änderungen 24.09.2020 */
ALTER TABLE `farben_auftrag` ADD PRIMARY KEY( `id_farbe`, `id_auftrag`);

/* Änderungen 06.10.2020 */
ALTER TABLE `leistung_posten` CHANGE `Einkaufspreis` `Einkaufspreis` FLOAT(11) NOT NULL;
ALTER TABLE `leistung_posten` CHANGE `SpeziefischerPreis` `SpeziefischerPreis` FLOAT(11) NOT NULL;

/* Änderungen 13.10.2020 */
ALTER TABLE `product_compact` ADD `postennummer` INT NOT NULL AFTER `id`;
ALTER TABLE `product_compact` ADD `amount` INT NOT NULL AFTER `postennummer`;
ALTER TABLE `posten` DROP `Postennummer`;
ALTER TABLE `posten` CHANGE `Nummer` `Postennummer` INT(11) NOT NULL AUTO_INCREMENT;
DROP VIEW postendata;
CREATE VIEW postendata AS
  SELECT posten.*, zeit.ZeitInMinuten, CONCAT(COALESCE(zeit.Beschreibung, ''), COALESCE(leistung_posten.Beschreibung, ''), COALESCE(produkt_posten.Produktnummer, '')) AS Beschreibung
  FROM posten
  LEFT JOIN zeit ON posten.Postennummer = zeit.Postennummer
  LEFT JOIN leistung_posten ON posten.Postennummer = leistung_posten.Postennummer
  LEFT JOIN produkt_posten ON posten.Postennummer = produkt_posten.Postennummer;

/* Änderungen 22.11.2020 */
ALTER TABLE `kunde_extended` DROP FOREIGN KEY `kunde_extended_ibfk_1`;
ALTER TABLE `kunde_extended` ADD CONSTRAINT `kunde_extended_ibfk_1` FOREIGN KEY (`kundennummer`) REFERENCES `kunde`(`Kundennummer`) ON DELETE CASCADE ON UPDATE NO ACTION;

/* Änderungen 23.11.2020 */
ALTER TABLE `posten` ADD `rechnungsNr` INT NOT NULL AFTER `angebotsNr`;

/* Änderungen 19.12.2020 */
CREATE TABLE `auftragsmanager`.`user_notifications` ( `id` INT NOT NULL AUTO_INCREMENT , `user_id` INT NOT NULL , `notification_id` INT NOT NULL , `type` INT NOT NULL , `content` VARCHAR(128) NOT NULL , `ischecked` BOOLEAN NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;

/* Änderungen 04.01.2021 */
ALTER TABLE `user_notifications` CHANGE `type` `type` VARCHAR(32) NOT NULL;

/* Änderungen 05.01.2021 */
CREATE TABLE `auftrag_liste` ( `auftrags_id` INT NOT NULL , `listen_id` INT NOT NULL ) ENGINE = InnoDB;
ALTER TABLE `auftrag_liste` ADD PRIMARY KEY( `auftrags_id`, `listen_id`);

/* Änderungen 07.01.2021 */
ALTER TABLE `history` ADD `member_id` INT NOT NULL AFTER `insertstamp`;
INSERT INTO `articles` (`id`, `articleUrl`, `pageName`, `src`) VALUES (NULL, 'einstellungen.php', 'Einstellungen', 'einstellungen');

/* Änderungen 16.01.2021 */
INSERT INTO `attachments` (`id`, `articleId`, `anchor`, `fileSrc`, `fileName`, `fileType`) VALUES (NULL, '4', 'head', 'rechnung.js', '0', 'js');

/* Änderungen 23.01.2021 */
ALTER TABLE `user_notifications` CHANGE `type` `type` INT(32) NOT NULL;
ALTER TABLE `user_notifications` CHANGE `notification_id` `specific_id` INT(11) NOT NULL;
CREATE VIEW auftragssumme AS
  SELECT ROUND(SUM(all_posten.price), 2) AS orderPrice, all_posten.id AS id, auftrag.Datum, auftrag.Fertigstellung FROM ( SELECT (zeit.ZeitInMinuten / 60) * zeit.Stundenlohn AS price, posten.Auftragsnummer as id FROM zeit, posten WHERE zeit.Postennummer = posten.Postennummer UNION ALL SELECT leistung_posten.SpeziefischerPreis AS price, posten.Auftragsnummer as id FROM leistung_posten, posten WHERE leistung_posten.Postennummer = posten.Postennummer) all_posten, auftrag WHERE auftrag.Auftragsnummer = id GROUP BY id;
ALTER TABLE `leistung_posten` CHANGE `Beschreibung` `Beschreibung` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;

/* Änderungen 24.01.2021 */
DROP VIEW auftragssumme;
CREATE VIEW auftragssumme_view AS 
  SELECT (zeit.ZeitInMinuten / 60) * zeit.Stundenlohn AS price, posten.Auftragsnummer as id FROM zeit, posten WHERE zeit.Postennummer = posten.Postennummer UNION ALL SELECT leistung_posten.SpeziefischerPreis AS price, posten.Auftragsnummer as id FROM leistung_posten, posten WHERE leistung_posten.Postennummer = posten.Postennummer;
CREATE VIEW auftragssumme AS
 SELECT ROUND(SUM(auftragssumme_view.price), 2) AS auftragssumme_view, auftragssumme_view.id AS id, auftrag.Datum, auftrag.Fertigstellung FROM auftragssumme_view, auftrag WHERE auftrag.Auftragsnummer = id GROUP BY id;

/* Änderungen 01.02.2021 */
ALTER TABLE `adress` ADD `country` VARCHAR(32) NOT NULL AFTER `zusatz`;

/* Änderungen 09.03.2021 */
INSERT INTO `attachments` (`id`, `articleId`, `anchor`, `fileSrc`, `fileName`, `fileType`) VALUES (NULL, '11', 'head', 'tableeditor.js', '0', 'js');

/* Änderungen 11.03.2021 */
ALTER TABLE `adress` DROP INDEX `id_customer`;

/* Änderungen 17.03.2021 */
UPDATE `history_type` SET `name` = 'Fahrzeug' WHERE `history_type`.`type_id` = 3;
INSERT INTO `articles` (`id`, `articleUrl`, `pageName`, `src`) VALUES (NULL, 'changelog.php', 'Changelog', 'changelog'), (NULL, 'help', 'Hilfe', 'help.php');

/* Änderungen 09.04.2021 */
CREATE TABLE `auftragsmanager`.`color_settings` ( `id` INT NOT NULL AUTO_INCREMENT , `userid` INT NOT NULL , `type` INT NOT NULL , `color` VARCHAR(32) NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;

/* Änderungen 13.04.2021 */
INSERT INTO `attachments` (`id`, `articleId`, `anchor`, `fileSrc`, `fileName`, `fileType`) VALUES (NULL, '21', 'head', 'colorpicker.js', '0', 'js');
DROP VIEW auftragssumme;
CREATE VIEW auftragssumme AS
 SELECT ROUND(SUM(auftragssumme_view.price), 2) AS orderPrice, auftragssumme_view.id AS id, auftrag.Datum, auftrag.Fertigstellung FROM auftragssumme_view, auftrag WHERE auftrag.Auftragsnummer = id GROUP BY id;

/* Änderungen 19.04.2021 */
ALTER TABLE `user_notifications` ADD `initiator` INT NOT NULL AFTER `user_id`;

/* Änderungen 22.05.2021 */
ALTER TABLE `notizen` CHANGE `Nummer` `Nummer` INT(11) NOT NULL AUTO_INCREMENT, add PRIMARY KEY (`Nummer`);

/* Änderungen 25.05.2021 */
ALTER TABLE `leistung_posten` CHANGE `Beschreibung` `Beschreibung` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE auftragsmanager.adress DROP FOREIGN KEY `adress_ibfk_1`;
INSERT INTO adress (strasse, hausnr, plz, ort, id_customer, country, art) SELECT Straße, Hausnummer, Postleitzahl, Ort, Kundennummer, "DE", 1 FROM kunde;
ALTER TABLE `kunde` ADD `id_adress_primary` INT NOT NULL AFTER `Website`;
ALTER TABLE auftragsmanager.kunde_extended DROP FOREIGN KEY `kunde_extended_ibfk_1`;
ALTER TABLE kunde 
  DROP COLUMN Straße, 
  DROP COLUMN Hausnummer, 
  DROP COLUMN Postleitzahl, 
  DROP COLUMN Ort;
UPDATE kunde, adress
  SET id_adress_primary = adress.id
  WHERE kunde.Kundennummer = adress.id_customer;

/* Änderungen 27.05.2021 */
ALTER TABLE `posten` ADD `discount` INT NOT NULL AFTER `ohneBerechnung`;
ALTER TABLE `leistung_posten` ADD `meh` VARCHAR(64) NOT NULL AFTER `SpeziefischerPreis`, ADD `qty` INT NOT NULL AFTER `meh`;
ALTER TABLE `leistung_posten` CHANGE `qty` `qty` VARCHAR(64) NOT NULL;

/* Änderungen 14.06.2021 */
CREATE TABLE `auftragsmanager`.`info_texte` ( `id` INT NOT NULL AUTO_INCREMENT , `info` TEXT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;

/* Änderungen 30.07.2021 */
CREATE TABLE `members_mitarbeiter` ( `id_member` INT NOT NULL , `id_mitarbeiter` INT NOT NULL ) ENGINE = InnoDB;
INSERT INTO `members_mitarbeiter` (`id_member`, `id_mitarbeiter`) VALUES ('2', '1'), ('1', '2'), ('3', '4');
INSERT INTO `articles` (`id`, `articleUrl`, `pageName`, `src`) VALUES (NULL, 'mitarbeiter.php', 'Mitarbeiter', 'mitarbeiter');

/* Änderungen 01.08.2021 */
CREATE TABLE `listendata` ( `id` INT NOT NULL , `lnr` INT NOT NULL , `lid` INT NOT NULL , `art` INT NOT NULL , `info` VARCHAR(128) NOT NULL ) ENGINE = InnoDB;
ALTER TABLE `listendata` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT, add PRIMARY KEY (`id`);

/* Änderungen 02.08.2021 */
ALTER TABLE `listendata` ADD `orderid` INT NOT NULL AFTER `id`;
ALTER TABLE `listendata` DROP `id`;
ALTER TABLE `listendata` CHANGE `lid` `lid` INT(11) NOT NULL AUTO_INCREMENT, add PRIMARY KEY (`lid`);
ALTER TABLE `listendata` CHANGE `lid` `lid` INT(11) NOT NULL auto_increment FIRST

/* Änderungen 03.08.2021 */
INSERT INTO `history_type` (`type_id`, `name`) VALUES ('7', 'Notiz');


/* Änderungen 10.08.2021 */
ALTER TABLE `farben` CHANGE `Farbe` `Farbe` VARCHAR(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;

/* Änderungen 12.10.2021 */
UPDATE `auftragstyp` SET `id` = '4' WHERE `auftragstyp`.`id` = 0;
ALTER TABLE `auftragstyp` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT;
INSERT INTO `auftragstyp` (`id`, `Auftragstyp`) VALUES (NULL, 'Folienplott'), (NULL, 'Drucksachen'), (NULL, 'Satzarbeiten');

/* Änderungen 19.11.2021 */
CREATE TABLE `auftragsmanager`.`navigation` ( `id` INT NOT NULL , `link` INT NOT NULL , `type` INT NOT NULL , `parent` INT NOT NULL , `name` VARCHAR(64) NOT NULL ) ENGINE = InnoDB;
ALTER TABLE `navigation` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT, add PRIMARY KEY (`id`);
CREATE TABLE `auftragsmanager`.`category` ( `id` INT NOT NULL AUTO_INCREMENT , `name` VARCHAR(64) NOT NULL , `parent` INT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
INSERT INTO `attachments` (`id`, `articleId`, `anchor`, `fileSrc`, `fileName`, `fileType`) VALUES (NULL, '9', 'head', 'produkt.js', '0', 'js');

/* Änderungen 20.11.2021 */
INSERT INTO `category` (`id`, `name`, `parent`) VALUES ('0', 'Startkategorie', '0');

/* Änderungen 23.11.2021 */
CREATE TABLE `auftragsmanager`.`frontPage` ( `id` INT NOT NULL AUTO_INCREMENT , `articleUrl` VARCHAR(32) NOT NULL , `pageName` VARCHAR(32) NOT NULL , `src` VARCHAR(32) NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
INSERT INTO `frontpage` (`id`, `articleUrl`, `pageName`, `src`) VALUES (NULL, 'mainPage.php', 'Startseite', '');
INSERT INTO `frontpage` (`id`, `articleUrl`, `pageName`, `src`) VALUES (NULL, 'productPage.php', 'Produkt', 'produkt');

/* Änderungen 01.12.2021 */
INSERT INTO `frontpage` (`id`, `articleUrl`, `pageName`, `src`) VALUES (NULL, 'cartPage.php', 'Einkaufswagen', 'cart');
