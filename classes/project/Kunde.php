<?php

error_reporting(E_ALL);

/**
 * unbenanntesModell - class.Kunde.php
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
 * include
 *
 * @author firstname and lastname of author, <author@example.org>
 */
require_once('class..php');

/**
 * include Auftrag
 *
 * @author firstname and lastname of author, <author@example.org>
 */
require_once('class.Auftrag.php');

/* user defined includes */
// section -64--88--78-22--1785616:16c6bb0e419:-8000:0000000000000A66-includes begin
// section -64--88--78-22--1785616:16c6bb0e419:-8000:0000000000000A66-includes end

/* user defined constants */
// section -64--88--78-22--1785616:16c6bb0e419:-8000:0000000000000A66-constants begin
// section -64--88--78-22--1785616:16c6bb0e419:-8000:0000000000000A66-constants end

/**
 * Short description of class Kunde
 *
 * @access public
 * @author firstname and lastname of author, <author@example.org>
 */
class Kunde
{
    // --- ASSOCIATIONS ---
    // generateAssociationEnd :     // generateAssociationEnd : Auftr‰ge

    // --- ATTRIBUTES ---

    /**
     * Short description of attribute Kundennummer
     *
     * @access public
     * @var Integer
     */
    public $Kundennummer = null;

    /**
     * Short description of attribute Vorname
     *
     * @access public
     * @var String
     */
    public $Vorname = null;

    /**
     * Short description of attribute Nachname
     *
     * @access public
     * @var String
     */
    public $Nachname = null;

    /**
     * Short description of attribute Firmenname
     *
     * @access public
     * @var String
     */
    public $Firmenname = null;

    /**
     * Short description of attribute Straﬂe
     *
     * @access public
     * @var String
     */
    public $Straﬂe = null;

    /**
     * Short description of attribute Hausnummer
     *
     * @access public
     * @var Integer
     */
    public $Hausnummer = null;

    /**
     * Short description of attribute Postleitzahl
     *
     * @access public
     * @var Integer
     */
    public $Postleitzahl = null;

    /**
     * Short description of attribute Ort
     *
     * @access public
     * @var String
     */
    public $Ort = null;

    /**
     * Short description of attribute Email
     *
     * @access public
     * @var String
     */
    public $Email = null;

    // --- OPERATIONS ---

    /**
     * Short description of method neuerAuftrag
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    public function neuerAuftrag()
    {
        // section -64--88--78-22--1785616:16c6bb0e419:-8000:0000000000000A83 begin
        // section -64--88--78-22--1785616:16c6bb0e419:-8000:0000000000000A83 end
    }

} /* end of class Kunde */

?>