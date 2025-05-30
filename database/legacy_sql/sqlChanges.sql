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
CREATE TABLE `footer_links` ( `id` INT NOT NULL AUTO_INCREMENT , `link` INT NOT NULL , `title` INT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
ALTER TABLE `footer_links` CHANGE `link` `link` VARCHAR(64) NOT NULL;
ALTER TABLE `footer_links` CHANGE `title` `title` VARCHAR(64) NOT NULL;

/* Änderungen 19.12.2021 */
INSERT INTO `articles` (`id`, `articleUrl`, `pageName`, `src`) VALUES (NULL, 'funktionen.php', 'Funktionsübersicht', 'functionalities');

/* Änderungen 31.12.2021 */
CREATE TABLE `payments` ( `id` INT NOT NULL AUTO_INCREMENT , `type` INT NOT NULL , `description` TEXT NOT NULL , `paymentDate` DATE NOT NULL , `creationDate` DATE NOT NULL , `amount` FLOAT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
INSERT INTO `articles` (`id`, `articleUrl`, `pageName`, `src`) VALUES (NULL, 'zahlungen.php', 'Zahlungen und Rechnungen', 'payments');

/* Änderungen 05.01.2022 */
CREATE TABLE `recurring_payments` ( `id` INT NOT NULL AUTO_INCREMENT , `type` INT NOT NULL , `amount` FLOAT NOT NULL , `short_description` VARCHAR(128) NOT NULL , `description` TEXT NOT NULL , `date` DATE NOT NULL , `recurring` INT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
INSERT INTO `attachments` (`id`, `articleId`, `anchor`, `fileSrc`, `fileName`, `fileType`) VALUES (NULL, '27', 'head', 'tableeditor.js', '0', 'js');

/* Änderungen 07.01.2022 */
INSERT INTO `articles` (`id`, `articleUrl`, `pageName`, `src`) VALUES (NULL, 'wiki.php', 'Firmenwiki', 'wiki');
CREATE TABLE `wiki_articles` ( `id` INT NOT NULL AUTO_INCREMENT , `content` TEXT NOT NULL , `title` VARCHAR(128) NOT NULL , `keywords` VARCHAR(128) NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;

/* Änderungen 09.01.2022 */
INSERT INTO `attachments` (`id`, `articleId`, `anchor`, `fileSrc`, `fileName`, `fileType`) VALUES (NULL, '28', 'head', 'wiki.js', '0', 'js');

/* Änderungen 09.03.2022 */
ALTER TABLE `zeit` CHANGE `Stundenlohn` `Stundenlohn` FLOAT(11) NOT NULL;

/* Änderungen 12.03.2022 */
UPDATE `articles` SET `articleUrl` = 'help.php', `src` = 'help' WHERE `articles`.`id` = 24;
UPDATE `info_texte` SET `info` = 'Hier kannst Du ein Fahrzeug hinzufügen, das mit dem Auftrag verknüpft ist. Dazu einfach unter \"Bitte auswählen\" auf \"Neues Fahrzeug hinzufügen\" und den Anweisungen folgen oder aus einem vorhanden Fahrzeug wählen. Mit dem Fahrzeug lassen sich Bilder verknüpfen.' WHERE `info_texte`.`id` = 1;

/* Änderungen 13.03.2022 */
CREATE TABLE `dateien_posten` ( `id_file` INT NOT NULL , `id_posten` INT NOT NULL ) ENGINE = InnoDB;
ALTER TABLE `dateien_posten` ADD PRIMARY KEY(`id_file`, `id_posten`);
ALTER TABLE `verbesserungen` ADD `erstelldatum` DATE NULL AFTER `erledigt`;

/* Änderungen 15.03.2022 */
INSERT INTO `angenommen` (`id`, `Bezeichnung`, `istAllgemein`) VALUES (NULL, 'Whatsapp', '1');
CREATE TABLE `color` ( `id` INT NULL , `Farbe` INT NOT NULL , `Farbwert` INT NOT NULL , `Bezeichnung` INT NOT NULL , `Hersteller` INT NOT NULL ) ENGINE = InnoDB;
CREATE TABLE `color_auftrag` ( `id_color` INT NOT NULL , `id_auftrag` INT NOT NULL ) ENGINE = InnoDB;
ALTER TABLE `color_auftrag` ADD PRIMARY KEY(`id_color`, `id_auftrag`);
ALTER TABLE `color` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT, add PRIMARY KEY (`id`);
ALTER TABLE `color` CHANGE `Farbe` `Farbe` VARCHAR(64) NOT NULL;
ALTER TABLE `color` CHANGE `Farbwert` `Farbwert` VARCHAR(6) NOT NULL;
ALTER TABLE `color` CHANGE `Bezeichnung` `Bezeichnung` VARCHAR(64) NOT NULL;
ALTER TABLE `color` CHANGE `Hersteller` `Hersteller` VARCHAR(64) NOT NULL;
RENAME TABLE `adress` TO `address`;
ALTER TABLE `kunde` CHANGE `id_adress_primary` `id_address_primary` INT(11) NOT NULL;

/* Änderungen 03.05.2022 */
ALTER TABLE `verbesserungen` CHANGE `erstelldatum` `erstelldatum` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP;

/* Änderungen 18.05.2022 */
ALTER TABLE `posten` ADD `isInvoice` BOOLEAN NOT NULL AFTER `discount`;

/* Änderungen 19.05.2022 */
ALTER TABLE `posten` DROP `isInvoice`;
CREATE TABLE `invoice_items` ( `item_id` INT NOT NULL AUTO_INCREMENT , `order_id` INT NOT NULL , `offer_id` INT NOT NULL , `invoice_id` INT NOT NULL , `type` VARCHAR(16) NOT NULL , `default_item` BOOLEAN NOT NULL , `no_charge` BOOLEAN NOT NULL , `discount_percentage` INT NOT NULL , PRIMARY KEY (`item_id`)) ENGINE = InnoDB;
ALTER TABLE `posten` ADD `isInvoice` BOOLEAN NOT NULL AFTER `discount`;

/* Änderungen 20.05.2022 */
ALTER TABLE `attachments` CHANGE `fileSrc` `fileSrc` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
INSERT INTO `attachments` (`id`, `articleId`, `anchor`, `fileSrc`, `fileName`, `fileType`) VALUES (NULL, '10', 'head', 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.1.6/Chart.bundle.min.js', '0', 'extJs');
UPDATE posten INNER JOIN auftrag ON posten.Auftragsnummer = auftrag.Auftragsnummer SET isInvoice = 1 WHERE auftrag.Rechnungsnummer > 0;
CREATE TABLE `invoice` ( `id` INT NOT NULL , `order_id` INT NOT NULL , `creation_date` DATE NOT NULL , `payment_date` DATE NOT NULL , `payment_type` INT NOT NULL , `amount` INT NOT NULL ) ENGINE = InnoDB;

/* Änderungen 26.05.2022 */
CREATE TABLE `settings` ( `id` INT NOT NULL AUTO_INCREMENT , `title` VARCHAR(32) NOT NULL , `content` VARCHAR(128) NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
INSERT INTO `settings` (`id`, `title`, `content`) VALUES (NULL, 'defaultWage', '50');

/* Änderungen 29.05.2022 */
INSERT INTO `attachments` (`id`, `articleId`, `anchor`, `fileSrc`, `fileName`, `fileType`) VALUES (NULL, '26', 'head', 'funktionen.js', '0', 'js');

/* Änderungen 30.05.2022 */
CREATE TABLE `manual` (`id` INT NULL , `page` VARCHAR(64) NOT NULL , `intent` VARCHAR(64) NOT NULL , `info` TEXT NOT NULL ) ENGINE = InnoDB;

/* Änderungen 01.06.2022 */
DELETE FROM `attachments` WHERE `attachments`.`id` = 16 AND `articleId` = 4;

/* Änderungen 02.06.2022 */
INSERT INTO `attachments` (`id`, `articleId`, `anchor`, `fileSrc`, `fileName`, `fileType`) VALUES (NULL, '4', 'head', 'rechnung.css', '', 'css');
DELETE attachments FROM attachments LEFT JOIN articles ON attachments.articleId = articles.id WHERE CONCAT(articles.src, ".js") = attachments.fileSrc;
DELETE attachments FROM attachments LEFT JOIN articles ON attachments.articleId = articles.id WHERE CONCAT(articles.src, ".css") = attachments.fileSrc;

/* Änderungen 05.06.2022 */
INSERT INTO settings (title, content) VALUES ("cacheStatus", "on");

/* Änderungen 06.06.2022 */
ALTER TABLE `auftrag` CHANGE `Fertigstellung` `Fertigstellung` DATE NULL DEFAULT '0000-00-00';
ALTER TABLE `auftrag` CHANGE `Rechnungsnummer` `Rechnungsnummer` INT(11) NOT NULL DEFAULT '0';
ALTER TABLE `auftrag` CHANGE `Bezahlt` `Bezahlt` INT(11) NOT NULL DEFAULT '0';

/* Änderungen 13.06.2022 */
ALTER TABLE `verbesserungen` CHANGE `erledigt` `erledigt` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '';

/* Änderungen 16.06.2022 */
INSERT INTO `attachments` (`id`, `articleId`, `anchor`, `fileSrc`, `fileName`, `fileType`) VALUES (NULL, '5', 'footer', 'neuer-auftrag.js', '0', 'js');

/* Änderungen 20.06.2022 */
CREATE TABLE `zeiterfassung` (`id` INT NOT NULL AUTO_INCREMENT , `id_zeit` INT NOT NULL , `from_time` INT NOT NULL , `to_time` INT NOT NULL , `date` DATE NULL DEFAULT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;

/* Änderungen 21.06.2022 */
ALTER TABLE `posten` CHANGE `angebotsNr` `angebotsNr` INT(11) NOT NULL DEFAULT '0';
ALTER TABLE `posten` CHANGE `rechnungsNr` `rechnungsNr` INT(11) NOT NULL DEFAULT '0';
ALTER TABLE `posten` CHANGE `istStandard` `istStandard` INT(11) NOT NULL DEFAULT '0';
ALTER TABLE `history` ADD `alternative_text` TINYTEXT NULL DEFAULT NULL AFTER `member_id`;

/* Änderungen 24.06.2022 */
ALTER TABLE `leistung`
MODIFY `Nummer` INT,
DROP PRIMARY KEY;
UPDATE `leistung` SET Nummer = Nummer + 1;
ALTER TABLE `leistung`
ADD PRIMARY KEY (Nummer);
INSERT INTO `leistung` (`Nummer`, `Bezeichnung`, `Beschreibung`, `Quelle`, `Aufschlag`) VALUES (1, "Standardleistung", "Standardwert, falls nicht genauer ausgewählt", "keine", 0);
ALTER TABLE `leistung` CHANGE `Nummer` `Nummer` INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `leistung` AUTO_INCREMENT = 17;

/* Änderungen 29.06.2022 */
ALTER TABLE `attribute_to_product`
  DROP PRIMARY KEY;
ALTER TABLE `attribute_to_product` ADD `id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`);
ALTER TABLE `produkt` CHANGE `Bezeichnung` `Bezeichnung` VARCHAR(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;

/* Änderungen 03.07.2022 */
ALTER TABLE `schritte` CHANGE `finishingDate` `finishingDate` DATE NULL DEFAULT '0000-00-00';

/* Änderungen 05.07.2022 */
DROP TABLE `produkt_varianten`;
CREATE TABLE `produkt_attribute` (`id` INT NOT NULL AUTO_INCREMENT , `id_produkt` INT NOT NULL , `id_attribute_to_product` INT NOT NULL , `amount` INT NULL DEFAULT NULL , `purchasing_price` INT NULL DEFAULT NULL , `price` INT NULL DEFAULT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
ALTER TABLE `produkt_attribute` DROP `id_attribute_to_product`;
ALTER TABLE `attribute_to_product` CHANGE `id` `id` INT(11) NOT NULL;
ALTER TABLE `attribute_to_product`
  DROP PRIMARY KEY,
   ADD PRIMARY KEY(
     `attribute_id`,
     `product_id`
   );
ALTER TABLE `attribute_to_product` CHANGE `id` `id_produkt_attribute` INT(11) NOT NULL;
ALTER TABLE `attribute_to_product`
  DROP PRIMARY KEY,
   ADD PRIMARY KEY(
     `id_produkt_attribute`,
     `attribute_id`
   );
ALTER TABLE `attribute_to_product` DROP `product_id`;
RENAME TABLE `attribute_to_product` TO `produkt_attribute_to_attribute`;

/* Änderungen 11.07.2022 */
INSERT INTO `attachments` (`id`, `articleId`, `anchor`, `fileSrc`, `fileName`, `fileType`) VALUES (NULL, '25', 'head', 'funktionen.js', '0', 'js');

/* Änderungen 12.07.2022 */
ALTER TABLE `kunde` CHANGE `Email` `Email` VARCHAR(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;

/* Änderungen 17.07.2022 */
ALTER TABLE `wiki_articles` CHANGE `keywords` `keywords` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL;

/* Änderungen 20.07.2022 */
ALTER TABLE `invoice` ADD `performance_date` DATE NOT NULL AFTER `creation_date`;
ALTER TABLE `invoice` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT, add PRIMARY KEY (`id`);

/* Änderungen 22.07.2022 */
INSERT INTO `settings` (`id`, `title`, `content`) VALUES (NULL, 'errorReporting', 'on');
INSERT INTO `attachments` (`id`, `articleId`, `anchor`, `fileSrc`, `fileName`, `fileType`) VALUES (NULL, '26', 'head', 'tableeditor.js', '0', 'js');
ALTER TABLE `recurring_payments` CHANGE `amount` `amount` INT NOT NULL;
ALTER TABLE `listendata` CHANGE `lid` `lid` INT(11) NOT NULL;
ALTER TABLE `listendata`
  DROP PRIMARY KEY,
   ADD PRIMARY KEY(
     `lid`,
     `orderid`
   );
INSERT INTO `info_texte` (`id`, `info`) VALUES (NULL, 'Wähle aus einer der erstellten Listen.\r\nListen können im Auftrag als Stütze für die Herangehensweise verwendet werden.\r\nUm eine neue Liste zu erstellen, gehe auf "Listen" im Endbereich der Seite.');

/* Änderungen 01.08.2022 */
ALTER TABLE `posten` ADD `position` INT NULL AFTER `Auftragsnummer`;

/* Änderungen 18.08.2022 */
ALTER TABLE `user_notifications` CHANGE `ischecked` `ischecked` TINYINT(1) NOT NULL DEFAULT '1';

/* Änderungen 30.08.2022 */
ALTER TABLE `produkt` CHANGE `Preis` `Preis` INT NOT NULL;
ALTER TABLE `produkt` CHANGE `Einkaufspreis` `Einkaufspreis` INT NOT NULL;

/* Änderungen 06.09.2022 */
UPDATE `history_type` SET `name` = 'Dateien' WHERE `history_type`.`type_id` = 4;

/* Änderunge 25.09.2022 */
ALTER TABLE `verbesserungen` ADD `creator` INT NOT NULL AFTER `verbesserungen`;
ALTER TABLE `verbesserungen` CHANGE `creator` `creator` INT(11) NULL DEFAULT NULL;
ALTER TABLE `manual` ADD PRIMARY KEY(`id`);

/* Änderungen 19.10.2022 */
UPDATE `attachments` SET `fileSrc` = 'neuer-kunde_f.js' WHERE `attachments`.`id` = 49;

/* Änderungen 31.10.2022 */
CREATE TABLE `user_login` ( `id` INT NOT NULL , `user_id` INT NOT NULL , `md_hash` VARCHAR(64) NOT NULL , `expiration_date` DATE NOT NULL , `device_name` VARCHAR(64) NOT NULL , `ip_adress` VARCHAR(64) NOT NULL , `browser_agent` VARCHAR(64) NOT NULL ) ENGINE = InnoDB;
ALTER TABLE `user_login` CHANGE `device_name` `device_name` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE `user_login` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT, add PRIMARY KEY (`id`);
ALTER TABLE `user_login` DROP `device_name`;
ALTER TABLE `user_login` ADD `device_name` TEXT NOT NULL AFTER `expiration_date`;

/* Änderungen 01.10.2022 */
ALTER TABLE `user_login` ADD `loginkey` CHAR(12) NOT NULL AFTER `md_hash`;
