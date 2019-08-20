<?php

error_reporting(E_ALL);

/**
 * unbenanntesModell - class.Zeit.php
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
require_once('class.Posten.php');

/* user defined includes */
// section -64--88--78-22--1785616:16c6bb0e419:-8000:0000000000000ABD-includes begin
// section -64--88--78-22--1785616:16c6bb0e419:-8000:0000000000000ABD-includes end

/* user defined constants */
// section -64--88--78-22--1785616:16c6bb0e419:-8000:0000000000000ABD-constants begin
// section -64--88--78-22--1785616:16c6bb0e419:-8000:0000000000000ABD-constants end

/**
 * Short description of class Zeit
 *
 * @access public
 * @author firstname and lastname of author, <author@example.org>
 */
class Zeit
    extends Posten
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute Stundenlohn
     *
     * @access public
     * @var Stundenlohn
     */
    public $Stundenlohn = null;

    /**
     * Short description of attribute ZeitInMinuten
     *
     * @access public
     * @var Integer
     */
    public $ZeitInMinuten = null;

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
        // section -64--88--78-22--dbaf7cb:16c8686fa2d:-8000:00000000000009A6 begin
        // section -64--88--78-22--dbaf7cb:16c8686fa2d:-8000:00000000000009A6 end
    }

    /**
     * Short description of method kalkulierePreis
     *
     * @access private
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    private function kalkulierePreis()
    {
        // section -64--88--78-22--dbaf7cb:16c8686fa2d:-8000:00000000000009AC begin
        // section -64--88--78-22--dbaf7cb:16c8686fa2d:-8000:00000000000009AC end
    }

} /* end of class Zeit */

?>