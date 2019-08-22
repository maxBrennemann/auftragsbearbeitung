<?php

error_reporting(E_ALL);

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

require_once('Auftrag.php');

/**
 * Short description of class Kunde
 *
 * @access public
 * @author firstname and lastname of author, <author@example.org>
 */
class Kunde {

    public $Kundennummer = null;
	public $Vorname = null;
	public $Nachname = null;
	public $Firmenname = null;
	public $Straﬂe = null;
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