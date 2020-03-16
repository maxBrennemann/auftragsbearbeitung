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
INSERT INTO `attachments` (`id`, `articleId`, `anchor`, `fileSrc`, `fileName`, `fileType`) VALUES (NULL, '14', 'head', 'attribute.js', '0', 'js')

/* Änderungen 16.03.2020 */
CREATE TABLE `auftragsbearbeitung`.`angebot` ( `id` INT NOT NULL AUTO_INCREMENT , `kdnr` INT NOT NULL , `status` INT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
ALTER TABLE `posten` ADD `angebotsNr` INT NOT NULL AFTER `Auftragsnummer`;
