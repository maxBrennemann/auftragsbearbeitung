<?php

error_reporting(E_ALL);

/**
 * unbenanntesModell - class.Auftrag.php
 *
 * $Id$
 *
 * This file is part of unbenanntesModell.
 *
 * Automatically generated on 20.08.2019, 12:32:17 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author firstname and lastname of author, <author@example.org>
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include Kunde
 *
 * @author firstname and lastname of author, <author@example.org>
 */
require_once('class.Kunde.php');

/**
 * include Schritte
 *
 * @author firstname and lastname of author, <author@example.org>
 */
require_once('class.Schritte.php');

/* user defined includes */
// section -64--88--78-22--1785616:16c6bb0e419:-8000:0000000000000A88-includes begin
// section -64--88--78-22--1785616:16c6bb0e419:-8000:0000000000000A88-includes end

/* user defined constants */
// section -64--88--78-22--1785616:16c6bb0e419:-8000:0000000000000A88-constants begin
// section -64--88--78-22--1785616:16c6bb0e419:-8000:0000000000000A88-constants end

/**
 * Short description of class Auftrag
 *
 * @access public
 * @author firstname and lastname of author, <author@example.org>
 */
class Auftrag
{
    // --- ASSOCIATIONS ---
    // generateAssociationEnd : Kunde    // generateAssociationEnd : 

    // --- ATTRIBUTES ---

    /**
     * Short description of attribute Auftragsnummer
     *
     * @access public
     * @var Integer
     */
    public $Auftragsnummer = null;

    /**
     * Short description of attribute Auftragsbezeichnung
     *
     * @access public
     * @var String
     */
    public $Auftragsbezeichnung = null;

    /**
     * Short description of attribute Auftragsbeschreibung
     *
     * @access public
     * @var String
     */
    public $Auftragsbeschreibung = null;

    /**
     * Short description of attribute Auftragsposten
     *
     * @access public
     * @var Posten
     */
    public $Auftragsposten = null;

    /**
     * Short description of attribute Bearbeitungsschritte
     *
     * @access public
     * @var Schritte
     */
    public $Bearbeitungsschritte = null;

    /**
     * Short description of attribute Auftragstyp
     *
     * @access public
     * @var Typ
     */
    public $Auftragstyp = null;

    // --- OPERATIONS ---

    /**
     * Short description of method bearbeitungsschrittHinzufuegen
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    public function bearbeitungsschrittHinzufuegen()
    {
        // section -64--88--78-22--dbaf7cb:16c8686fa2d:-8000:00000000000009DC begin
        // section -64--88--78-22--dbaf7cb:16c8686fa2d:-8000:00000000000009DC end
    }

    /**
     * Short description of method bearbeitungsschrittEntfernen
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    public function bearbeitungsschrittEntfernen()
    {
        // section -64--88--78-22--dbaf7cb:16c8686fa2d:-8000:00000000000009DE begin
        // section -64--88--78-22--dbaf7cb:16c8686fa2d:-8000:00000000000009DE end
    }

    /**
     * Short description of method bearbeitunsschrittBearbeiten
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    public function bearbeitunsschrittBearbeiten()
    {
        // section -64--88--78-22--dbaf7cb:16c8686fa2d:-8000:00000000000009E0 begin
        // section -64--88--78-22--dbaf7cb:16c8686fa2d:-8000:00000000000009E0 end
    }

    /**
     * Short description of method postenHinzufuegen
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    public function postenHinzufuegen()
    {
        // section -64--88--78-22--dbaf7cb:16c8686fa2d:-8000:00000000000009E2 begin
        // section -64--88--78-22--dbaf7cb:16c8686fa2d:-8000:00000000000009E2 end
    }

    /**
     * Short description of method postenEntfernen
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    public function postenEntfernen()
    {
        // section -64--88--78-22--dbaf7cb:16c8686fa2d:-8000:00000000000009E4 begin
        // section -64--88--78-22--dbaf7cb:16c8686fa2d:-8000:00000000000009E4 end
    }

    /**
     * Short description of method schritteNachTypGenerieren
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    public function schritteNachTypGenerieren()
    {
        // section -64--88--78-22-6f584299:16ca497f3f8:-8000:0000000000000A0A begin
        // section -64--88--78-22-6f584299:16ca497f3f8:-8000:0000000000000A0A end
    }

} /* end of class Auftrag */

?>