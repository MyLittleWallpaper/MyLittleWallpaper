<?php
/**
 * @author Petri Haikonen <sharkmachine@ecxol.net>
 * @package MyLittleWallpaper
 * @subpackage Classes
 */

if (!defined('INDEX')) exit();

/**
 * Basic class for JSON output.
 */
class BasicJSON extends Output {
	/**
	 * @var array|null
	 */
	private $data = null;
	
	/**
	 * @param array|null $data
	 */
	public function __construct($data = null) {
		if ($data !== null) {
			$this->set_data($data);
		}
	}
	
	/**
	 * @param array $data
	 */
	public function set_data($data) {
		$this->data = $data;
	}
	
	/**
	 * @return array|null
	 */
	public function output() {
		return json_encode($this->data);
	}

	/**
	 * @return string
	 */
	public function getHeaderType() {
		return 'application/json';
	}

	/**
	 * @return bool
	 */
	public function getIncludeHeaderAndFooter() {
		return false;
	}
}