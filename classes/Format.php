<?php
/**
 * @author Petri Haikonen <sharkmachine@ecxol.net>
 * @package MyLittleWallpaper
 * @subpackage Classes
 */

// Check that correct entry point was used
if (!defined('INDEX')) exit();

/**
 * Format class.
 * Used for formatting user input etc.
 */
class Format {
	const QUOTE_TYPES_SINGLE = 1;
	const QUOTE_TYPES_DOUBLE = 2;
	const QUOTE_TYPES_BOTH = 3;

	/**
	 * @param string $string
	 * @return string
	 */
	public static function htmlEntities($string) {
		return htmlentities($string, ENT_QUOTES, 'utf-8');
	}

	/**
	 * @param $string
	 * @return string
	 */
	public static function xmlEntities($string) {
		return htmlspecialchars($string, ENT_QUOTES | ENT_XML1, 'utf-8');
	}

	/**
	 * @param string $string
	 * @param int $quoteTypes see Format::QUOTE_TYPES_*
	 * @return string
	 */
	public static function escapeQuotes($string, $quoteTypes = self::QUOTE_TYPES_SINGLE) {
		$replace = ['\\\\'];
		$pattern = ['\\'];
		if ($quoteTypes === self::QUOTE_TYPES_SINGLE || $quoteTypes == self::QUOTE_TYPES_BOTH) {
			$replace[] = '\'';
			$pattern[] = '\\\'';
		}
		if ($quoteTypes === self::QUOTE_TYPES_DOUBLE || $quoteTypes == self::QUOTE_TYPES_BOTH) {
			$replace[] = '"';
			$pattern[] = '\"';
		}
		return str_replace($pattern, $replace, $string);
	}
	
	/**
	 * Returns given filename's extension.
	 * @param string $string
	 * @return string
	 */
	public static function fileExtension($string) {
		return pathinfo($string, PATHINFO_EXTENSION);
	}

	/**
	 * Returns filename without extension
	 * @param $string
	 * @return string
	 */
	public static function fileWithoutExtension($string) {
		return pathinfo($string, PATHINFO_FILENAME);
	}

	/**
	 * @param string $password
	 * @param string $salt
	 * @return string
	 */
	public static function passwordHash($password, $salt) {
		$saltProcess = '';
		for ($a = 0; $a < mb_strlen($salt, 'utf-8'); $a++) {
			if ($a % 3 == 0 || $a % 5 == 0) {
				$saltProcess .= mb_strtolower(mb_substr($salt, $a, 1, 'utf-8'));
			}
		}
		$data = base64_encode(gzcompress($password . $saltProcess));
		return hash_hmac('whirlpool', $data, HASHKEY);
	}
}