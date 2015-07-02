<?php
/**
 * @author Petri Haikonen <sharkmachine@ecxol.net>
 * @package MyLittleWallpaper
 * @subpackage Classes
 */

if (!defined('INDEX')) exit();

/**
 * Basic class for XML output.
 * @todo Add support for building XML from an array
 */
class BasicXML extends Output {
	/**
	 * @var array|string|null
	 */
	private $contents = null;

	/**
	 * @param array|string|null $contents
	 */
	public function __construct($contents = null) {
		if ($contents !== null) {
			$this->set_contents($contents);
		}
	}

	/**
	 * @param array $contents
	 */
	public function set_contents($contents) {
		$this->contents = $contents;
	}

	/**
	 * @return array|null
	 */
	public function output() {
		return '<?xml version="1.0" encoding="utf-8"?>' . "\n" . $this->contents;
	}

	/**
	 * @return string
	 */
	public function getHeaderType() {
		return 'application/xml; charset=utf-8';
	}

	/**
	 * @return bool
	 */
	public function getIncludeHeaderAndFooter() {
		return false;
	}
}