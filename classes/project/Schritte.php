<?php

error_reporting(E_ALL);

/**
 * unbenanntesModell - class.Schritte.php
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
 * include Auftrag
 *
 * @author firstname and lastname of author, <author@example.org>
 */
require_once('class.Auftrag.php');

/* user defined includes */
// section -64--88--78-22--dbaf7cb:16c8686fa2d:-8000:00000000000009E6-includes begin
// section -64--88--78-22--dbaf7cb:16c8686fa2d:-8000:00000000000009E6-includes end

/* user defined constants */
// section -64--88--78-22--dbaf7cb:16c8686fa2d:-8000:00000000000009E6-constants begin
// section -64--88--78-22--dbaf7cb:16c8686fa2d:-8000:00000000000009E6-constants end

/**
 * Short description of class Schritte
 *
 * @access public
 * @author firstname and lastname of author, <author@example.org>
 */
class Schritte
{
    // --- ASSOCIATIONS ---
    // generateAssociationEnd : 

    // --- ATTRIBUTES ---

    /**
     * Short description of attribute istAllgemein
     *
     * @access public
     * @var Boolean
     */
    public $istAllgemein = null;

    /**
     * Short description of attribute Bezeichnung
     *
     * @access public
     * @var String
     */
    public $Bezeichnung = null;

    /**
     * Short description of attribute Datum
     *
     * @access public
     * @var Date
     */
    public $Datum = null;

    /**
     * Short description of attribute Priorität
     *
     * @access public
     * @var Integer
     */
    public $Priorität = null;

    // --- OPERATIONS ---

    /**
     * Short description of method bearbeiten
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    public function bearbeiten()
    {
        // section -64--88--78-22-6f584299:16ca497f3f8:-8000:00000000000009E9 begin
        // section -64--88--78-22-6f584299:16ca497f3f8:-8000:00000000000009E9 end
    }

    /**
     * Short description of method erledigen
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    public function erledigen()
    {
        // section -64--88--78-22-6f584299:16ca497f3f8:-8000:00000000000009EB begin
        // section -64--88--78-22-6f584299:16ca497f3f8:-8000:00000000000009EB end
    }

} /* end of class Schritte */

?>