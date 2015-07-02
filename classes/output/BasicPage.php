<?php
/**
 * @author Petri Haikonen <sharkmachine@ecxol.net>
 * @package MyLittleWallpaper
 * @subpackage Classes
 */

if (!defined('INDEX')) exit();

/**
 * Class for basic (& static) pages.
 */
class BasicPage extends Output {
	/**
	 * @var string
	 */
	private $pageTitleAddition;
	
	/**
	 * @var string
	 */
	private $html;
	
	/**
	 * @var string
	 */
	private $javaScript;
	
	/**
	 * @var string
	 */
	private $meta;
	
	/**
	 * @var bool
	 */
	private $noContainer = false;
	
	/**
	 * @param string|null $title
	 * @param string|null $html
	 */
	public function __construct($title = null, $html = null) {
		if ($title !== null) {
			$this->setPageTitleAddition($title);
		}
		if ($html !== null) {
			$this->setHtml($html);
		}
	}
	
	/**
	 * @param string $html
	 */
	public function setHtml($html) {
		$this->html = (string) $html;
	}
	
	/**
	 * @param string $title
	 */
	public function setPageTitleAddition($title) {
		$this->pageTitleAddition = (string) $title;
	}
	
	/**
	 * @param string $javaScript
	 */
	public function setJavascript($javaScript) {
		$this->javaScript = (string) $javaScript;
	}
	
	/**
	 * @param string $meta
	 */
	public function setMeta($meta) {
		$this->meta = (string) $meta;
	}
	
	public function setNoContainer() {
		$this->noContainer = true;
	}
	
	/**
	 * @return string
	 */
	public function getPageTitleAddition() {
		return $this->pageTitleAddition;
	}
	
	/**
	 * @return string
	 */
	public function getJavaScript() {
		return $this->javaScript;
	}
	
	/**
	 * @return string
	 */
	public function getMeta() {
		return $this->meta;
	}
	
	public function output() {
		global $response;
		ob_start();
		$response->responseVariables->html = $this->html;
		if (!$this->noContainer) {
			require_once(DOC_DIR . THEME . '/basic_page.php');
		} else {
			echo $response->responseVariables->html;
		}
		return ob_get_clean();
	}

	/**
	 * @return string
	 */
	public function getHeaderType() {
		return 'text/html; charset=utf-8';
	}

	/**
	 * @return bool
	 */
	public function getIncludeHeaderAndFooter() {
		return true;
	}
}
