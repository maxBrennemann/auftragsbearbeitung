<?php

namespace Classes;

class Protocol
{

	private static $file;

	function __construct() {}

	/** 
	 * Writes a given string to the protocol.txt file
	 * 
	 * @param $text string
	 */
	public static function write(string $textContent, ?string $details = null)
	{
		if (self::$file == null) {
			self::$file = fopen("protocol.txt", "a");
		}

		if ($details != null) {
			$text = $textContent . " - " . $details . "\n";
		} else {
			$text = $textContent . "\n";
		}

		fwrite(self::$file, $text);
	}

	/**
	 * Closes the protocol.txt file
	 */
	public static function close()
	{
		if (self::$file == null)
			return;
		fclose(self::$file);
	}

	public static function delete()
	{
		unlink("protocol.txt");
	}

	/**
	 * Pretty prints a given data structure
	 * 
	 * @param $data mixed
	 */
	public static function prettyPrint($data)
	{
		echo "<pre>";
		var_dump($data);
		echo "</pre>";
	}
}
