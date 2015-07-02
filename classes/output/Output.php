<?php
/**
 * @author Petri Haikonen <sharkmachine@ecxol.net>
 * @package MyLittleWallpaper
 * @subpackage Classes
 */

if (!defined('INDEX')) exit();

/**
 * Abstract class for output classes used by Response class.
 */
abstract class Output {
	public abstract function output();

	public abstract function getHeaderType();

	public abstract function getIncludeHeaderAndFooter();
}