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