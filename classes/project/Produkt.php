<?php

error_reporting(E_ALL);

/**
 * unbenanntesModell - class.Produkt.php
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
 * include Posten
 *
 * @author firstname and lastname of author, <author@example.org>
 */
require_once('Posten.php');

/* user defined includes */
// section -64--88--78-22--1785616:16c6bb0e419:-8000:0000000000000AB5-includes begin
// section -64--88--78-22--1785616:16c6bb0e419:-8000:0000000000000AB5-includes end

/* user defined constants */
// section -64--88--78-22--1785616:16c6bb0e419:-8000:0000000000000AB5-constants begin
// section -64--88--78-22--1785616:16c6bb0e419:-8000:0000000000000AB5-constants end

/**
 * Short description of class Produkt
 *
 * @access public
 * @author firstname and lastname of author, <author@example.org>
 */
class Produkt
    extends Posten
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute Preis
     *
     * @access public
     * @var Integer
     */
    public $Preis = null;

    /**
     * Short description of attribute Produktnummer
     *
     * @access public
     * @var Integer
     */
    public $Produktnummer = null;

    /**
     * Short description of attribute Bezeichnung
     *
     * @access public
     * @var String
     */
    public $Bezeichnung = null;

    /**
     * Short description of attribute Beschreibung
     *
     * @access public
     * @var String
     */
    public $Beschreibung = null;

    // --- OPERATIONS ---

    /**
     * Short description of method bekommePreis
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    public function bekommePreis()
    {
        // section -64--88--78-22--dbaf7cb:16c8686fa2d:-8000:00000000000009A8 begin
        // section -64--88--78-22--dbaf7cb:16c8686fa2d:-8000:00000000000009A8 end
    }

	public function getHTMLData() {
		return "";
	}

	public function fillToArray($arr) {
		return "";
	}

} /* end of class Produkt */

?>