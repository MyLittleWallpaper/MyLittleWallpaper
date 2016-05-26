<?php
/**
 * @author Petri Haikonen <sharkmachine@ecxol.net>
 * @package MyLittleWallpaper
 * @subpackage Classes
 */

if (!defined('INDEX')) exit();

require_once(ROOT_DIR . 'classes/Wallpaper.php');

/**
 * Wallpaper list class.
 * Used for loading a list of wallpapers.
 */
class WallpaperList extends Output {
	// Constants for wallpaper list order
	const ORDER_DATE_ADDED = 'date';
	const ORDER_POPULARITY = 'popularity';
	const ORDER_RANDOM = -1;

	/**
	 * Order selection titles
	 * @param string|int $val Use self::ORDER_*
	 * @return string
	 * @throws Exception if an invalid order parameter is given
	 */
	static function GET_ORDER_TITLE($val) {
		switch ($val) {
			case self::ORDER_DATE_ADDED:
				return 'Date added';
			case self::ORDER_POPULARITY:
				return 'Popularity';
			case self::ORDER_RANDOM:
				return 'Random';
			default:
				throw new Exception('Invalid order parameter');
		}
	}
	
	// Constants for wallpaper resolution search
	const RESOLUTION_1366X768 = 4;
	const RESOLUTION_1680X1050 = 3;
	const RESOLUTION_1920X1080 = 2;
	const RESOLUTION_1920X1200 = 1;
	const RESOLUTION_2560X1600 = 5;
	const RESOLUTION_2560X1440 = 6;
	const RESOLUTION_3840X2160 = 7;

	/**
	 * @param int $val Use self::RESOLUTION_*
	 * @return string
	 * @throws Exception if an invalid resolution value is given
	 */
	static function GET_RESOLUTION_TITLE($val) {
		$return = 'Greater than ';
		switch ($val) {
			case self::RESOLUTION_1366X768:
				$return .= '1366x768';
				break;
			case self::RESOLUTION_1680X1050:
				$return .= '1680x1050';
				break;
			case self::RESOLUTION_1920X1080:
				$return .= '1920x1080';
				break;
			case self::RESOLUTION_1920X1200:
				$return .= '1920x1200';
				break;
			case self::RESOLUTION_2560X1440:
				$return .= '2560x1440';
				break;
			case self::RESOLUTION_2560X1600:
				$return .= '2560x1600';
				break;
			case self::RESOLUTION_3840X2160:
				$return .= '3840x2160';
				break;
			default:
				throw new Exception('Invalid resolution search parameter');
		}
		return $return;
	}
	
	// Other constants
	const MAX_JOIN_AMOUNT = 8;

	/**
	 * @var int
	 */
	private $joinAmount = 0;
	
	/**
	 * @var string 
	 */
	private $pageTitleAddition = '';

	/**
	 * @var string
	 */
	private $pageTitleSearch = '';
	
	/**
	 * @var string
	 */
	private $metaTags = '';
	
	/**
	 * @var int
	 */
	private $pageNumber = 1;

	/**
	 * @var int|null
	 */
	private $offset = null;
	
	/**
	 * @var string
	 */
	private $pageBase = '';
	
	/**
	 * @var string
	 */
	private $redirect = '';
	
	/**
	 * @var string
	 */
	private $rssSearch = '';
	
	/**
	 * @var int
	 */
	private $wallpapersPerPage = 50;
	
	/**
	 * @var Wallpaper[]
	 */
	private $wallpapers = [];
	
	/**
	 * How many total wallpapers the search finds.
	 * @var int
	 */
	private $wallpaperSearchCount = 0;

	/**
	 * @var int|null
	 */
	private $searchFavouritesUserId = null;

	/**
	 * Either date in format YYYY-MM-DD or null.
	 * @var string|null
	 */
	private $searchDate = null;
	
	/**
	 * Use self::RESOLUTION_* or set as null
	 * @var int|null
	 */
	private $searchSize = null;
	
	/**
	 * @var string[]
	 */
	private $searchTags = [];
	
	/**
	 * @var string[]
	 */
	private $searchTagsCharacter = [];
	
	/**
	 * @var string[]
	 */
	private $searchTagsAuthor = [];

	/**
	 * @var string[]
	 */
	private $searchTagsAspect = [];

	/**
	 * @var string[]
	 */
	private $searchTagsPlatform = [];

	/**
	 * @var string[]
	 */
	private $searchTagsColour = [];

	/**
	 * @var string[]
	 */
	private $searchTagsMajorColour = [];

	/**
	 * @var string[]
	 */
	private $searchTagsAny = [];

	/**
	 * @var string[]
	 */
	private $searchTagsAnyCharacter = [];

	/**
	 * @var string[]
	 */
	private $searchTagsAnyAuthor = [];

	/**
	 * @var string[]
	 */
	private $searchTagsAnyAspect = [];

	/**
	 * @var string[]
	 */
	private $searchTagsAnyPlatform = [];

	/**
	 * @var string[]
	 */
	private $searchTagsAnyColour = [];

	/**
	 * @var string[]
	 */
	private $searchTagsAnyMajorColour = [];

	/**
	 * @var string[]
	 */
	private $searchTagsExclude = [];

	/**
	 * @var string[]
	 */
	private $searchTagsExcludeCharacter = [];

	/**
	 * @var string[]
	 */
	private $searchTagsExcludeAuthor = [];

	/**
	 * @var string[]
	 */
	private $searchTagsExcludeAspect = [];

	/**
	 * @var string[]
	 */
	private $searchTagsExcludePlatform = [];

	/**
	 * @var string[]
	 */
	private $searchTagsExcludeColour = [];

	/**
	 * @var string[]
	 */
	private $searchTagsExcludeMajorColour = [];

	/**
	 * @var bool
	 */
	private $maxJoinAmountExceeded = false;

	/**
	 * @var int|null
	 */
	private $category = null;

	/**
	 * Use self::ORDER_*
	 * Default self::ORDER_DATE_ADDED
	 * @var string|int
	 */
	private $displayOrder = self::ORDER_DATE_ADDED;
	
	/**
	 * @var bool
	 */
	private $renderWallpapersOnly = false;
	
	/**
	 * @var bool
	 */
	private $largeWallpaperThumbs = false;
	
	/**
	 * @var string|null 
	 */
	private $customTemplate = null;
	
	/**
	 * @var string
	 */
	private $ajaxLoadMorePage = 'list';
	
	/**
	 * @var Database
	 */
	private $db;

	/**
	 * @var string
	 */
	private $sqlJoins = "";

	/**
	 * @var string
	 */
	private $sqlWhere = "";

	/**
	 * @var array
	 */
	private $sqlData = [];

	
	/**
	 * @param Database|null $db If null, looks for $GLOBALS['db']
	 * @throws Exception if database not found
	 */
	public function __construct(&$db = null) {
		if (!($db instanceof Database)) {
			if (!isset($GLOBALS['db']) || !($GLOBALS['db'] instanceof Database)) {
				throw new Exception('No database connection found');
			} else {
				$this->db =& $GLOBALS['db'];
			}
		} else {
			$this->db = $db;
		}
	}
	
	/**
	 * Loads the search and ordering straight from request
	 */
	public function loadSearchFromRequest() {
		$getValues = $_GET;
		
		if (isset($getValues['page']) && filter_var($getValues['page'], FILTER_VALIDATE_INT) !== false && $getValues['page'] > 1) {
			$this->pageNumber = (int) $getValues['page'];
		}

		if (!empty($getValues['search'])) {
			if (strpos($getValues['search'], ',') !== false) {
				$tagList = explode(',', $getValues['search']);
			} else {
				$tagList = array($getValues['search']);
			}
			$this->searchAddTags($tagList);
		}
		if (!empty($getValues['searchAny'])) {
			if (strpos($getValues['searchAny'], ',') !== false) {
				$tagList = explode(',', $getValues['searchAny']);
			} else {
				$tagList = array($getValues['searchAny']);
			}
			$this->searchAddTagsAny($tagList);
		}
		if (!empty($getValues['searchExclude'])) {
			if (strpos($getValues['searchExclude'], ',') !== false) {
				$tagList = explode(',', $getValues['searchExclude']);
			} else {
				$tagList = array($getValues['searchExclude']);
			}
			$this->searchAddTagsExclude($tagList);
		}

		if (!empty($getValues['size']) && filter_var($getValues['size'], FILTER_VALIDATE_INT) !== false) {
			$this->searchAddSize((int) $getValues['size']);
		}
			
		if (!empty($getValues['date'])) {
			$this->searchAddDate($getValues['date']);
		}
		
		if (!empty($getValues['sort'])) {
			$this->setDisplayOrder($getValues['sort']);
		}
		$pageBase = '';
		$redirect = '';
		foreach($getValues as $key => $val) {
			if (strcmp($key, 'page') !== 0 && strcmp($key, 'date') !== 0 && strcmp($key, 'sort') !== 0 && strcmp($key, 'c') !== 0) {
				if (!empty($val)) {
					if ($pageBase == '') {
						$pageBase .= '?';
						$redirect .= '?';
					} else {
						$pageBase .= '&amp;';
						$redirect .= '&';
					}
					$pageBase .= $key.'='.urlencode($val);
					$redirect .= $key.'='.urlencode($val);
				}
			}
		}
		
		$rssSearch = $pageBase;

		if (!empty($getValues['date'])) {
			if ($pageBase == '') {
				$pageBase .= '?';
				$redirect .= '?';
			} else {
				$pageBase .= '&amp;';
				$redirect .= '&';
			}
			$pageBase .= 'date='.urlencode($getValues['date']);
			$redirect .= 'date='.urlencode($getValues['date']);
		}

		if (!empty($getValues['sort']) && $getValues['sort'] == 'popularity') {
			if ($pageBase == '') {
				$pageBase .= '?';
				$redirect .= '?';
			} else {
				$pageBase .= '&amp;';
				$redirect .= '&';
			}
			$pageBase .= 'sort='.urlencode($getValues['sort']);
			$redirect .= 'sort='.urlencode($getValues['sort']);
		}
		
		$this->pageBase = $pageBase;
		$this->redirect = $redirect;
		$this->rssSearch = $rssSearch;
	}
	
	/**
	 * @param string|int $val use self::ORDER_*
	 */
	public function setDisplayOrder($val) {
		switch($val) {
			case self::ORDER_DATE_ADDED:
				$this->displayOrder = self::ORDER_DATE_ADDED;
				break;
			case self::ORDER_POPULARITY:
				$this->displayOrder = self::ORDER_POPULARITY;
				break;
			case self::ORDER_RANDOM:
				$this->displayOrder = self::ORDER_RANDOM;
				break;
		}
	}
	
	/**
	 * Set whether to show only the wallpaper list without search box or not.
	 * Wallpaper list is shown with search box by default.
	 * @param bool $val
	 */
	public function setRenderWallpapersOnly($val) {
		$this->renderWallpapersOnly = (bool) $val;
	}
	
	/**
	 * Set whether to show large wallpaper thumbs or not.
	 * Large wallpaper thumbs are not shown by default.
	 * @param bool $val
	 */
	public function setLargeWallpaperThumbs($val) {
		$this->largeWallpaperThumbs = (bool) $val;
	}
	
	/**
	 * Set a custom template.
	 * Set to null to remove custom template.
	 * @param string|null $val
	 */
	public function setCustomTemplate($val) {
		$this->customTemplate = $val;
	}
	
	/**
	 * Set the php-script that loads more wallpapers.
	 * Default 'list'
	 * @param string $val
	 */
	public function setAjaxLoadMorePage($val) {
		$this->ajaxLoadMorePage = $val;
	}
	
	/**
	 * Set wallpaper category.
	 * Default value is null, which gets wallpapers from all categories.
	 * @param int|null $val
	 */
	public function setCategory($val) {
		if ($val === null || $val <= 0) {
			$this->category = null;
		} else {
			$this->category = (int) $val;
		}
	}
	
	/**
	 * Validates and adds wallpaper add date to search.
	 * @param string $val In format that strtotime recognizes
	 */
	public function searchAddDate($val) {
		$thatime = @strtotime($val);
		if ($thatime !== false) {
			$this->searchDate = date('Y-m-d', $thatime);
		}
	}
	
	/**
	 * Validates and adds the wallpaper size to search.
	 * @param int $val
	 */
	public function searchAddSize($val) {
		switch ($val) {
			case self::RESOLUTION_1366X768:
			case self::RESOLUTION_1680X1050:
			case self::RESOLUTION_1920X1080:
			case self::RESOLUTION_1920X1200:
			case self::RESOLUTION_2560X1440:
			case self::RESOLUTION_2560X1600:
			case self::RESOLUTION_3840X2160:
				$this->searchSize = $val;
				break;
		}
	}
	
	/**
	 * @param string $tag
	 */
	public function searchAddTag($tag) {
		if ($this->checkTagCount()) {
			$this->searchTags[] = $tag;
		}
	}
	
	/**
	 * @param string $tag
	 */
	public function searchAddTagAspect($tag) {
		if ($this->checkTagCount()) {
			$this->searchTagsAspect[] = $tag;
		}
	}
	
	/**
	 * @param string $tag
	 */
	public function searchAddTagAuthor($tag) {
		if ($this->checkTagCount()) {
			$this->searchTagsAuthor[] = $tag;
		}
	}
	
	/**
	 * @param string $tag
	 */
	public function searchAddTagCharacter($tag) {
		if ($this->checkTagCount()) {
			$this->searchTagsCharacter[] = $tag;
		}
	}
	
	/**
	 * @param string $tag
	 */
	public function searchAddTagColour($tag) {
		if ($this->checkTagCount()) {
			$this->searchTagsColour[] = $tag;
		}
	}
	
	/**
	 * @param string $tag
	 */
	public function searchAddTagMajorColour($tag) {
		if ($this->checkTagCount()) {
			$this->searchTagsMajorColour[] = $tag;
		}
	}
	
	/**
	 * @param string $tag
	 */
	public function searchAddTagPlatform($tag) {
		if ($this->checkTagCount()) {
			$this->searchTagsPlatform[] = $tag;
		}
	}
	
	/**
	 * @param string[] $tags
	 */
	public function searchAddTags($tags) {
		foreach($tags as $tag) {
			$tag = trim($tag);
			if (str_replace(' ', '', $tag) != '') {
				/*$tag_count = count($this->searchTags);
				$tag_count += count($this->searchTagsAspect);
				$tag_count += count($this->searchTagsAuthor);
				$tag_count += count($this->searchTagsCharacter);
				$tag_count += count($this->searchTagsColour);
				$tag_count += count($this->searchTagsMajorColour);
				$tag_count += count($this->searchTagsPlatform);
				if ($tag_count == self::MAX_JOIN_AMOUNT) {
					$this->maxTagAmountExceeded = true;
				} else {*/
					$this->searchAddTagWithType($tag);
				//}
			}
		}
	}

	/**
	 * @param string[] $tags
	 */
	public function searchAddTagsAny($tags) {
		foreach($tags as $tag) {
			$tag = trim($tag);
			if (str_replace(' ', '', $tag) != '') {
				$this->searchAddTagAnyWithType($tag);
			}
		}
	}

	/**
	 * @param string[] $tags
	 */
	public function searchAddTagsExclude($tags) {
		foreach($tags as $tag) {
			$tag = trim($tag);
			if (str_replace(' ', '', $tag) != '') {
				$this->searchAddTagExcludeWithType($tag);
			}
		}
	}

	/**
	 * @param string $tag
	 */
	public function searchAddTagWithType($tag) {
		if (!$this->checkTagCount()) {
			return;
		}
		if (strcmp(mb_substr($tag, 0, 1, 'utf-8'), '=') === 0) {
			$this->searchTagsCharacter[] = mb_substr($tag, 1, mb_strlen($tag, 'utf-8'), 'utf-8');
		} else {
			if (mb_substr($tag, 0, 7, 'utf-8') == 'author:') {
				$this->searchTagsAuthor[] = mb_substr($tag, 7, mb_strlen($tag, 'utf-8'), 'utf-8');
			} elseif (mb_substr($tag, 0, 9, 'utf-8') == 'platform:') {
				$this->searchTagsPlatform[] = mb_substr($tag, 9, mb_strlen($tag, 'utf-8'), 'utf-8');
			} elseif (mb_substr($tag, 0, 7, 'utf-8') == 'aspect:') {
				$this->searchTagsAspect[] = mb_substr($tag, 7, mb_strlen($tag, 'utf-8'), 'utf-8');
			} elseif (mb_substr($tag, 0, 7, 'utf-8') == 'colour:') {
				$this->searchTagsColour[] = mb_substr($tag, 7, mb_strlen($tag, 'utf-8'), 'utf-8');
			} elseif (mb_substr($tag, 0, 13, 'utf-8') == 'major-colour:') {
				$this->searchTagsMajorColour[] = mb_substr($tag, 13, mb_strlen($tag, 'utf-8'), 'utf-8');
			} else {
				$this->searchTags[] = $tag;
			}
		}
	}

	/**
	 * @param string $tag
	 */
	public function searchAddTagAnyWithType($tag) {
		if (!$this->checkTagCount()) {
			return;
		}
		if (strcmp(mb_substr($tag, 0, 1, 'utf-8'), '=') === 0) {
			$this->searchTagsAnyCharacter[] = mb_substr($tag, 1, mb_strlen($tag, 'utf-8'), 'utf-8');
		} else {
			if (mb_substr($tag, 0, 7, 'utf-8') == 'author:') {
				$this->searchTagsAnyAuthor[] = mb_substr($tag, 7, mb_strlen($tag, 'utf-8'), 'utf-8');
			} elseif (mb_substr($tag, 0, 9, 'utf-8') == 'platform:') {
				$this->searchTagsAnyPlatform[] = mb_substr($tag, 9, mb_strlen($tag, 'utf-8'), 'utf-8');
			} elseif (mb_substr($tag, 0, 7, 'utf-8') == 'aspect:') {
				$this->searchTagsAnyAspect[] = mb_substr($tag, 7, mb_strlen($tag, 'utf-8'), 'utf-8');
			} elseif (mb_substr($tag, 0, 7, 'utf-8') == 'colour:') {
				$this->searchTagsAnyColour[] = mb_substr($tag, 7, mb_strlen($tag, 'utf-8'), 'utf-8');
			} elseif (mb_substr($tag, 0, 13, 'utf-8') == 'major-colour:') {
				$this->searchTagsAnyMajorColour[] = mb_substr($tag, 13, mb_strlen($tag, 'utf-8'), 'utf-8');
			} else {
				$this->searchTagsAny[] = $tag;
			}
		}
	}

	/**
	 * @param string $tag
	 */
	public function searchAddTagExcludeWithType($tag) {
		if (!$this->checkTagCount()) {
			return;
		}
		if (strcmp(mb_substr($tag, 0, 1, 'utf-8'), '=') === 0) {
			$this->searchTagsExcludeCharacter[] = mb_substr($tag, 1, mb_strlen($tag, 'utf-8'), 'utf-8');
		} else {
			if (mb_substr($tag, 0, 7, 'utf-8') == 'author:') {
				$this->searchTagsExcludeAuthor[] = mb_substr($tag, 7, mb_strlen($tag, 'utf-8'), 'utf-8');
			} elseif (mb_substr($tag, 0, 9, 'utf-8') == 'platform:') {
				$this->searchTagsExcludePlatform[] = mb_substr($tag, 9, mb_strlen($tag, 'utf-8'), 'utf-8');
			} elseif (mb_substr($tag, 0, 7, 'utf-8') == 'aspect:') {
				$this->searchTagsExcludeAspect[] = mb_substr($tag, 7, mb_strlen($tag, 'utf-8'), 'utf-8');
			} elseif (mb_substr($tag, 0, 7, 'utf-8') == 'colour:') {
				$this->searchTagsExcludeColour[] = mb_substr($tag, 7, mb_strlen($tag, 'utf-8'), 'utf-8');
			} elseif (mb_substr($tag, 0, 13, 'utf-8') == 'major-colour:') {
				$this->searchTagsExcludeMajorColour[] = mb_substr($tag, 13, mb_strlen($tag, 'utf-8'), 'utf-8');
			} else {
				$this->searchTagsExclude[] = $tag;
			}
		}
	}

	/**
	 * Fetches the wallpapers from the database and stores to $wallpapers.
	 */
	public function loadWallpapers() {
		$sql = "SELECT w.id, w.name, w.url, w.filename, w.file, w.width, w.height, w.mime, w.timeadded, w.clicks, w.favs, w.no_aspect, w.no_resolution, w.direct_with_link FROM ".
		"wallpaper w ";

		$this->sqlJoins = "";
		$this->sqlWhere = "";
		$this->sqlData = [];

		$this->pageTitleSearch = '';
		$this->metaTags = '';

		$this->loadWallpapersTagExcludeSearch();
		$this->loadWallpapersTagSearch();
		$this->loadWallpapersTagAnySearch();

		if ($this->displayOrder == self::ORDER_POPULARITY) {
			$order = "ORDER BY w.clicks DESC, w.id DESC ";
		} else {
			$order = "ORDER BY w.id DESC ";
		}

		if ($this->searchSize == self::RESOLUTION_3840X2160) {
			if ($this->sqlWhere != "") $this->sqlWhere .= " AND "; else $this->sqlWhere .= "WHERE ";
			$this->sqlWhere .= "w.width >= 3840 AND w.height >= 2160 ";
		} elseif ($this->searchSize == self::RESOLUTION_2560X1600) {
			if ($this->sqlWhere != "") $this->sqlWhere .= " AND "; else $this->sqlWhere .= "WHERE ";
			$this->sqlWhere .= "w.width >= 2560 AND w.height >= 1600 ";
		} elseif ($this->searchSize == self::RESOLUTION_2560X1440) {
			if ($this->sqlWhere != "") $this->sqlWhere .= " AND "; else $this->sqlWhere .= "WHERE ";
			$this->sqlWhere .= "w.width >= 2560 AND w.height >= 1440 ";
		} elseif ($this->searchSize == self::RESOLUTION_1920X1200) {
			if ($this->sqlWhere != "") $this->sqlWhere .= " AND "; else $this->sqlWhere .= "WHERE ";
			$this->sqlWhere .= "w.width >= 1920 AND w.height >= 1200 ";
		} elseif ($this->searchSize == self::RESOLUTION_1920X1080) {
			if ($this->sqlWhere != "") $this->sqlWhere .= " AND "; else $this->sqlWhere .= "WHERE ";
			$this->sqlWhere .= "w.width >= 1920 AND w.height >= 1080 ";
		} elseif ($this->searchSize == self::RESOLUTION_1680X1050) {
			if ($this->sqlWhere != "") $this->sqlWhere .= " AND "; else $this->sqlWhere .= "WHERE ";
			$this->sqlWhere .= "w.width >= 1680 AND w.height >= 1050 ";
		} elseif ($this->searchSize == self::RESOLUTION_1366X768) {
			if ($this->sqlWhere != "") $this->sqlWhere .= " AND "; else $this->sqlWhere .= "WHERE ";
			$this->sqlWhere .= "w.width >= 1366 AND w.height >= 768 ";
		}

		if ($this->sqlWhere != "") $this->sqlWhere .= " AND "; else $this->sqlWhere .= "WHERE ";
		$this->sqlWhere .= "w.deleted = 0 ";

		if ($this->searchFavouritesUserId !== null) {
			$this->sqlJoins .= " JOIN wallpaper_fav fav ON (fav.wallpaper_id = w.id) ";
			$this->sqlWhere .= "AND fav.user_id = ? ";
			$this->sqlData[] .= $this->searchFavouritesUserId;
		}

		if ($this->category !== null) {
			$this->sqlWhere .= " AND w.series = ? ";
			$this->sqlData[] = $this->category;
		}

		if ($this->searchDate !== null) {
			$searchDateUnixTimestamp = strtotime($this->searchDate);
			$this->sqlWhere .= "AND w.timeadded > ".mktime(0, 0, 0, date('n', $searchDateUnixTimestamp), date('j', $searchDateUnixTimestamp), date('Y', $searchDateUnixTimestamp))." AND w.timeadded < ".mktime(23, 59, 59, date('n', $searchDateUnixTimestamp), date('j', $searchDateUnixTimestamp), date('Y', $searchDateUnixTimestamp))." ";
			$this->pageTitleSearch = date('Y-m-d', $searchDateUnixTimestamp);
		}
		$sql .= $this->sqlJoins.$this->sqlWhere;
		$sql .= "GROUP BY w.id ".$order;

		if ($this->displayOrder != self::ORDER_RANDOM) {
			if ($this->offset !== null) {
				$offset = $this->offset;
			} else {
				$offset = ($this->pageNumber - 1) * $this->wallpapersPerPage;
			}
			$sql .= "LIMIT ".$this->wallpapersPerPage." OFFSET ".$offset;
		}

		if ($this->joinAmount > self::MAX_JOIN_AMOUNT) {
			$this->maxJoinAmountExceeded = true;
			return;
		}
		
		// Wallpaper count query
		$countSql = "SELECT ";
		$countSql .= "COUNT(1) cnt FROM (SELECT DISTINCT w.id FROM wallpaper w ".$this->sqlJoins.$this->sqlWhere.") asd";

		// How many wallpapers there are in the search result
		$countResult = $this->db->query($countSql, $this->sqlData);
		while ($row = $countResult->fetch(PDO::FETCH_ASSOC)) {
			$this->wallpaperSearchCount = (int) $row['cnt'];
		}

		// Fetch the wallpapers
		if ($this->displayOrder == self::ORDER_RANDOM) {
			for ($a = 0; $a < $this->wallpapersPerPage; $a++) {
				$random_offset = rand(0, ($this->wallpaperSearchCount - 1));
				$res = $this->db->query($sql . "LIMIT 1 OFFSET " . (int) $random_offset, $this->sqlData);
				while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
					$this->wallpapers[] = new Wallpaper($row);
				}
			}
		} else {
			$res = $this->db->query($sql, $this->sqlData);
			while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
				$this->wallpapers[] = new Wallpaper($row);
			}
		}
	}

	/**
	 * Adds tag search to query
	 */
	private function loadWallpapersTagSearch() {
		$tagJoinCount = 0;
		$authorJoinCount = 0;
		$aspectJoinCount = 0;
		$platformJoinCount = 0;
		$colourJoinCount = 0;
		$majorColourJoinCount = 0;

		$allSearchTags = [];
		$allSearchTagIds = [];
		$tagInArray = [];
		$tagIn = '';
		foreach($this->searchTags as $tag) {
			if ($tagIn != '') {
				$tagIn .= ', ';
			}
			$tagIn .= '?';
			$tagInArray[] = $tag;
		}
		if ($tagIn != '') {
			$query = "SELECT id, `name` FROM tag WHERE `name` IN (" . $tagIn . ")";
			$result = $this->db->query($query, $tagInArray);
			while ($fld = $result->fetch(PDO::FETCH_ASSOC)) {
				$allSearchTags[mb_strtolower($fld['name'], 'utf-8')] = $fld['name'];
				$allSearchTagIds[mb_strtolower($fld['name'], 'utf-8')] = $fld['id'];
			}
		}

		$allAuthorSearchTags = [];
		$allAuthorSearchTagIds = [];
		$tagInArray = [];
		$tagIn = '';
		foreach($this->searchTagsAuthor as $tag) {
			if ($tagIn != '') {
				$tagIn .= ', ';
			}
			$tagIn .= '?';
			$tagInArray[] = $tag;
		}
		if ($tagIn != '') {
			$query = "SELECT id, `name` FROM tag_artist WHERE `name` IN (" . $tagIn . ")";
			$result = $this->db->query($query, $tagInArray);
			while ($fld = $result->fetch(PDO::FETCH_ASSOC)) {
				$allAuthorSearchTags[mb_strtolower($fld['name'], 'utf-8')] = $fld['name'];
				$allAuthorSearchTagIds[mb_strtolower($fld['name'], 'utf-8')] = $fld['id'];
			}
		}

		$allPlatformSearchTags = [];
		$allPlatformSearchTagIds = [];
		$tagInArray = [];
		$tagIn = '';
		foreach($this->searchTagsPlatform as $tag) {
			if ($tagIn != '') {
				$tagIn .= ', ';
			}
			$tagIn .= '?';
			$tagInArray[] = $tag;
		}
		if ($tagIn != '') {
			$query = "SELECT id, `name` FROM tag_platform WHERE `name` IN (" . $tagIn . ")";
			$result = $this->db->query($query, $tagInArray);
			while ($fld = $result->fetch(PDO::FETCH_ASSOC)) {
				$allPlatformSearchTags[mb_strtolower($fld['name'], 'utf-8')] = $fld['name'];
				$allPlatformSearchTagIds[mb_strtolower($fld['name'], 'utf-8')] = $fld['id'];
			}
		}

		$allAspectSearchTags = [];
		$allAspectSearchTagIds = [];
		$tagInArray = [];
		$tagIn = '';
		foreach($this->searchTagsAspect as $tag) {
			if ($tagIn != '') {
				$tagIn .= ', ';
			}
			$tagIn .= '?';
			$tagInArray[] = $tag;
		}
		if ($tagIn != '') {
			$query = "SELECT id, `name` FROM tag_aspect WHERE `name` IN (" . $tagIn . ")";
			$result = $this->db->query($query, $tagInArray);
			while ($fld = $result->fetch(PDO::FETCH_ASSOC)) {
				$allAspectSearchTags[mb_strtolower($fld['name'], 'utf-8')] = $fld['name'];
				$allAspectSearchTagIds[mb_strtolower($fld['name'], 'utf-8')] = $fld['id'];
			}
		}

		unset($tagData);

		foreach ($this->searchTagsCharacter as $tag) {
			if (!empty($allSearchTags[mb_strtolower($tag, 'utf-8')])) {
				$this->metaTags .= ', ='.$allSearchTags[mb_strtolower($tag, 'utf-8')];
				if (!empty($this->pageTitleSearch)) {
					$this->pageTitleSearch .= ', ';
				}
				$this->pageTitleSearch .= '='.$allSearchTags[mb_strtolower($tag, 'utf-8')];
			}
		}

		foreach ($allAuthorSearchTagIds as $key => $id) {
			$this->joinAmount ++;
			if ($this->sqlWhere != "") {
				$this->sqlWhere .= " AND ";
			} else {
				$this->sqlWhere .= "WHERE ";
			}
			$authorJoinCount ++;
			$this->sqlJoins .= "JOIN wallpaper_tag_artist awt".$authorJoinCount." ON (awt".$authorJoinCount.".wallpaper_id = w.id) ";
			$this->sqlWhere .= "awt".$authorJoinCount.".tag_artist_id = ?";
			$this->sqlData[] = $id;
			if (!empty($allAuthorSearchTags[$key])) {
				$this->metaTags .= ', '.$allAuthorSearchTags[$key];
				if (!empty($this->pageTitleSearch)) {
					$this->pageTitleSearch .= ', ';
				}
				$this->pageTitleSearch .= $allAuthorSearchTags[$key];
			}
		}

		foreach ($allPlatformSearchTagIds as $key => $id) {
			$this->joinAmount ++;
			if ($this->sqlWhere != "") {
				$this->sqlWhere .= " AND ";
			} else {
				$this->sqlWhere .= "WHERE ";
			}
			$platformJoinCount ++;
			$this->sqlJoins .= "JOIN wallpaper_tag_platform pwt".$platformJoinCount." ON (pwt".$platformJoinCount.".wallpaper_id = w.id) ";
			$this->sqlWhere .= "pwt".$platformJoinCount.".tag_platform_id = ?";
			$this->sqlData[] = $id;
			if (!empty($allPlatformSearchTags[$key])) {
				$this->metaTags .= ', '.$allPlatformSearchTags[$key];
				if (!empty($this->pageTitleSearch)) {
					$this->pageTitleSearch .= ', ';
				}
				$this->pageTitleSearch .= $allPlatformSearchTags[$key];
			}
		}

		foreach ($allAspectSearchTagIds as $id) {
			$this->joinAmount ++;
			if ($this->sqlWhere != "") {
				$this->sqlWhere .= " AND ";
			} else {
				$this->sqlWhere .= "WHERE ";
			}
			$aspectJoinCount ++;
			$this->sqlJoins .= "JOIN wallpaper_tag_aspect aswt".$aspectJoinCount." ON (aswt".$aspectJoinCount.".wallpaper_id = w.id) ";
			$this->sqlWhere .= "aswt".$aspectJoinCount.".tag_aspect_id = ?";
			$this->sqlData[] = $id;
		}

		foreach($this->searchTagsColour as $tag) {
			$this->joinAmount += 2;
			if ($this->sqlWhere != "") {
				$this->sqlWhere .= " AND ";
			} else {
				$this->sqlWhere .= "WHERE ";
			}
			$colourJoinCount ++;
			$this->sqlJoins .= "JOIN wallpaper_tag_colour clwt".$colourJoinCount." ON (clwt".$colourJoinCount.".wallpaper_id = w.id) ";
			$this->sqlJoins .= "JOIN wallpaper_tag_colour_similar clt".$colourJoinCount." ON (clwt".$colourJoinCount.".tag_colour = clt".$colourJoinCount.".similar_colour) ";
			$this->sqlWhere .= "clt".$colourJoinCount.".colour = ?";
			$this->sqlData[] = $tag;
		}

		foreach($this->searchTagsMajorColour as $tag) {
			$this->joinAmount += 2;
			if ($this->sqlWhere != "") {
				$this->sqlWhere .= " AND ";
			} else {
				$this->sqlWhere .= "WHERE ";
			}
			$majorColourJoinCount ++;
			$this->sqlJoins .= "JOIN wallpaper_tag_colour mclwt".$majorColourJoinCount." ON (mclwt".$majorColourJoinCount.".wallpaper_id = w.id AND mclwt".$majorColourJoinCount.".amount >= 20) ";
			$this->sqlJoins .= "JOIN wallpaper_tag_colour_similar mclt".$majorColourJoinCount." ON (mclwt".$majorColourJoinCount.".tag_colour = mclt".$majorColourJoinCount.".similar_colour) ";
			$this->sqlWhere .= "mclt".$majorColourJoinCount.".colour = ?";
			$this->sqlData[] = $tag;
		}

		foreach ($allSearchTagIds as $key => $id) {
			$this->joinAmount ++;
			if ($this->sqlWhere != "") {
				$this->sqlWhere .= " AND ";
			} else {
				$this->sqlWhere .= "WHERE ";
			}
			$tagJoinCount ++;
			$this->sqlJoins .= "JOIN wallpaper_tag wt".$tagJoinCount." ON (wt".$tagJoinCount.".wallpaper_id = w.id) ";
			$this->sqlWhere .= "wt".$tagJoinCount.".tag_id = ?";
			$this->sqlData[] = $id;
			if (!empty($allSearchTags[$key])) {
				$this->metaTags .= ', '.$allSearchTags[$key];
				if (!empty($this->pageTitleSearch)) {
					$this->pageTitleSearch .= ', ';
				}
				$this->pageTitleSearch .= $allSearchTags[$key];
			}
		}

		if (!empty($this->searchTagsCharacter)) {
			$allCharacterSearchTagIds = [];
			$tagInArray = [];
			$tagIn = '';
			foreach($this->searchTagsCharacter as $tag) {
				if ($tagIn != '') {
					$tagIn .= ', ';
				}
				$tagIn .= '?';
				$tagInArray[] = $tag;
			}
			if ($tagIn != '') {
				$query = "SELECT id, `name` FROM tag WHERE `name` IN (" . $tagIn . ")";
				$result = $this->db->query($query, $tagInArray);
				while ($fld = $result->fetch(PDO::FETCH_ASSOC)) {
					$allCharacterSearchTagIds[mb_strtolower($fld['name'], 'utf-8')] = $fld['id'];
				}
			}

			$characterTags = $this->searchTagsCharacter;
			$characterTagSearch = '';
			sort($characterTags);
			foreach($characterTags as $characterTag) {
				if ($characterTagSearch != '') {
					$characterTagSearch .= ',';
				}
				if (!empty($allCharacterSearchTagIds[mb_strtolower($characterTag, 'utf-8')])) {
					$characterTagSearch .= $allCharacterSearchTagIds[mb_strtolower($characterTag, 'utf-8')];
				} else {
					$characterTagSearch .= '0';
				}
			}
			if ($this->sqlWhere != "") {
				$this->sqlWhere .= " AND ";
			} else {
				$this->sqlWhere .= "WHERE ";
			}
			$this->sqlWhere .= "w.chartags = ? ";
			$this->sqlData[] = $characterTagSearch;
		}
	}

	/**
	 * Adds tag search to query
	 */
	private function loadWallpapersTagAnySearch() {
		$allSearchTags = [];
		$allSearchTagIds = [];
		$tagInArray = [];
		$tagIn = '';
		foreach($this->searchTagsAny as $tag) {
			if ($tagIn != '') {
				$tagIn .= ', ';
			}
			$tagIn .= '?';
			$tagInArray[] = $tag;
		}
		if ($tagIn != '') {
			$query = "SELECT id, `name` FROM tag WHERE `name` IN (" . $tagIn . ")";
			$result = $this->db->query($query, $tagInArray);
			while ($fld = $result->fetch(PDO::FETCH_ASSOC)) {
				$allSearchTags[mb_strtolower($fld['name'], 'utf-8')] = $fld['name'];
				$allSearchTagIds[mb_strtolower($fld['name'], 'utf-8')] = $fld['id'];
			}
		}

		$allAuthorSearchTags = [];
		$allAuthorSearchTagIds = [];
		$tagInArray = [];
		$tagIn = '';
		foreach($this->searchTagsAnyAuthor as $tag) {
			if ($tagIn != '') {
				$tagIn .= ', ';
			}
			$tagIn .= '?';
			$tagInArray[] = $tag;
		}
		if ($tagIn != '') {
			$query = "SELECT id, `name` FROM tag_artist WHERE `name` IN (" . $tagIn . ")";
			$result = $this->db->query($query, $tagInArray);
			while ($fld = $result->fetch(PDO::FETCH_ASSOC)) {
				$allAuthorSearchTags[mb_strtolower($fld['name'], 'utf-8')] = $fld['name'];
				$allAuthorSearchTagIds[mb_strtolower($fld['name'], 'utf-8')] = $fld['id'];
			}
		}

		$allPlatformSearchTags = [];
		$allPlatformSearchTagIds = [];
		$tagInArray = [];
		$tagIn = '';
		foreach($this->searchTagsAnyPlatform as $tag) {
			if ($tagIn != '') {
				$tagIn .= ', ';
			}
			$tagIn .= '?';
			$tagInArray[] = $tag;
		}
		if ($tagIn != '') {
			$query = "SELECT id, `name` FROM tag_platform WHERE `name` IN (" . $tagIn . ")";
			$result = $this->db->query($query, $tagInArray);
			while ($fld = $result->fetch(PDO::FETCH_ASSOC)) {
				$allPlatformSearchTags[mb_strtolower($fld['name'], 'utf-8')] = $fld['name'];
				$allPlatformSearchTagIds[mb_strtolower($fld['name'], 'utf-8')] = $fld['id'];
			}
		}

		$allAspectSearchTags = [];
		$allAspectSearchTagIds = [];
		$tagInArray = [];
		$tagIn = '';
		foreach($this->searchTagsAnyAspect as $tag) {
			if ($tagIn != '') {
				$tagIn .= ', ';
			}
			$tagIn .= '?';
			$tagInArray[] = $tag;
		}
		if ($tagIn != '') {
			$query = "SELECT id, `name` FROM tag_aspect WHERE `name` IN (" . $tagIn . ")";
			$result = $this->db->query($query, $tagInArray);
			while ($fld = $result->fetch(PDO::FETCH_ASSOC)) {
				$allAspectSearchTags[mb_strtolower($fld['name'], 'utf-8')] = $fld['name'];
				$allAspectSearchTagIds[mb_strtolower($fld['name'], 'utf-8')] = $fld['id'];
			}
		}

		unset($tagData);

		if (!empty($allAuthorSearchTagIds)) {
			$tagIdIn = '';
			foreach ($allAuthorSearchTagIds as $id) {
				if ($tagIdIn != '') {
					$tagIdIn .= ', ';
				}
				$tagIdIn .= '?';
				$this->sqlData[] = $id;
			}
			$this->joinAmount++;
			if ($this->sqlWhere != "") {
				$this->sqlWhere .= " AND ";
			} else {
				$this->sqlWhere .= "WHERE ";
			}
			$this->sqlJoins .= "JOIN wallpaper_tag_artist awtAny ON (awtAny.wallpaper_id = w.id) ";
			$this->sqlWhere .= "awtAny.tag_artist_id IN (" . $tagIdIn . ")";
		}

		if (!empty($allPlatformSearchTagIds)) {
			$tagIdIn = '';
			foreach ($allPlatformSearchTagIds as $id) {
				if ($tagIdIn != '') {
					$tagIdIn .= ', ';
				}
				$tagIdIn .= '?';
				$this->sqlData[] = $id;
			}
			$this->joinAmount++;
			if ($this->sqlWhere != "") {
				$this->sqlWhere .= " AND ";
			} else {
				$this->sqlWhere .= "WHERE ";
			}
			$this->sqlJoins .= "JOIN wallpaper_tag_platform pwtAny ON (pwtAny.wallpaper_id = w.id) ";
			$this->sqlWhere .= "pwtAny.tag_platform_id IN (" . $tagIdIn . ")";

		}

		if (!empty($allAspectSearchTagIds)) {
			$tagIdIn = '';
			foreach ($allAspectSearchTagIds as $id) {
				if ($tagIdIn != '') {
					$tagIdIn .= ', ';
				}
				$tagIdIn .= '?';
				$this->sqlData[] = $id;
			}
			$this->joinAmount++;
			if ($this->sqlWhere != "") {
				$this->sqlWhere .= " AND ";
			} else {
				$this->sqlWhere .= "WHERE ";
			}
			$this->sqlJoins .= "JOIN wallpaper_tag_aspect aswtAny ON (aswtAny.wallpaper_id = w.id) ";
			$this->sqlWhere .= "aswtAny.tag_aspect_id IN (" . $tagIdIn . ")";
		}

		// @todo Colour support

		if (!empty($allSearchTagIds)) {
			$tagIdIn = '';
			foreach ($allSearchTagIds as $id) {
				if ($tagIdIn != '') {
					$tagIdIn .= ', ';
				}
				$tagIdIn .= '?';
				$this->sqlData[] = $id;
			}
			$this->joinAmount++;
			if ($this->sqlWhere != "") {
				$this->sqlWhere .= " AND ";
			} else {
				$this->sqlWhere .= "WHERE ";
			}
			$this->sqlJoins .= "JOIN wallpaper_tag wtAny ON (wtAny.wallpaper_id = w.id) ";
			$this->sqlWhere .= "wtAny.tag_id IN (" . $tagIdIn . ")";
		}

		if (!empty($this->searchTagsAnyCharacter)) {
			$allCharacterSearchTagIds = [];
			$tagInArray = [];
			$tagIn = '';
			foreach($this->searchTagsAnyCharacter as $tag) {
				if ($tagIn != '') {
					$tagIn .= ', ';
				}
				$tagIn .= '?';
				$tagInArray[] = $tag;
			}
			if ($tagIn != '') {
				$query = "SELECT id, `name` FROM tag WHERE `name` IN (" . $tagIn . ")";
				$result = $this->db->query($query, $tagInArray);
				while ($fld = $result->fetch(PDO::FETCH_ASSOC)) {
					$allCharacterSearchTagIds[mb_strtolower($fld['name'], 'utf-8')] = $fld['id'];
				}
			}

			$characterTagCount = 0;
			$characterTagSearch = '';
			foreach ($this->searchTagsAnyCharacter as $characterTag) {
				if (!empty($allCharacterSearchTagIds[mb_strtolower($characterTag, 'utf-8')])) {
					if ($characterTagCount > 0) {
						$characterTagSearch .= " OR ";
					}
					$characterTagCount ++;
					$characterTagSearch .= "w.chartags = ?";
					$this->sqlData[] = $allCharacterSearchTagIds[mb_strtolower($characterTag, 'utf-8')];
				}
			}
			if ($characterTagCount > 0) {
				if ($this->sqlWhere != "") {
					$this->sqlWhere .= " AND ";
				} else {
					$this->sqlWhere .= "WHERE ";
				}
				$this->sqlWhere .= "(";
				$this->sqlWhere .= $characterTagSearch;
				$this->sqlWhere .= ") ";
			}
		}
	}

	/**
	 * Adds tag search to query
	 */
	private function loadWallpapersTagExcludeSearch() {
		$allSearchTags = [];
		$allSearchTagIds = [];
		$tagInArray = [];
		$tagIn = '';
		foreach($this->searchTagsExclude as $tag) {
			if ($tagIn != '') {
				$tagIn .= ', ';
			}
			$tagIn .= '?';
			$tagInArray[] = $tag;
		}
		if ($tagIn != '') {
			$query = "SELECT id, `name` FROM tag WHERE `name` IN (" . $tagIn . ")";
			$result = $this->db->query($query, $tagInArray);
			while ($fld = $result->fetch(PDO::FETCH_ASSOC)) {
				$allSearchTags[mb_strtolower($fld['name'], 'utf-8')] = $fld['name'];
				$allSearchTagIds[mb_strtolower($fld['name'], 'utf-8')] = $fld['id'];
			}
		}

		$allAuthorSearchTags = [];
		$allAuthorSearchTagIds = [];
		$tagInArray = [];
		$tagIn = '';
		foreach($this->searchTagsExcludeAuthor as $tag) {
			if ($tagIn != '') {
				$tagIn .= ', ';
			}
			$tagIn .= '?';
			$tagInArray[] = $tag;
		}
		if ($tagIn != '') {
			$query = "SELECT id, `name` FROM tag_artist WHERE `name` IN (" . $tagIn . ")";
			$result = $this->db->query($query, $tagInArray);
			while ($fld = $result->fetch(PDO::FETCH_ASSOC)) {
				$allAuthorSearchTags[mb_strtolower($fld['name'], 'utf-8')] = $fld['name'];
				$allAuthorSearchTagIds[mb_strtolower($fld['name'], 'utf-8')] = $fld['id'];
			}
		}

		$allPlatformSearchTags = [];
		$allPlatformSearchTagIds = [];
		$tagInArray = [];
		$tagIn = '';
		foreach($this->searchTagsExcludePlatform as $tag) {
			if ($tagIn != '') {
				$tagIn .= ', ';
			}
			$tagIn .= '?';
			$tagInArray[] = $tag;
		}
		if ($tagIn != '') {
			$query = "SELECT id, `name` FROM tag_platform WHERE `name` IN (" . $tagIn . ")";
			$result = $this->db->query($query, $tagInArray);
			while ($fld = $result->fetch(PDO::FETCH_ASSOC)) {
				$allPlatformSearchTags[mb_strtolower($fld['name'], 'utf-8')] = $fld['name'];
				$allPlatformSearchTagIds[mb_strtolower($fld['name'], 'utf-8')] = $fld['id'];
			}
		}

		$allAspectSearchTags = [];
		$allAspectSearchTagIds = [];
		$tagInArray = [];
		$tagIn = '';
		foreach($this->searchTagsExcludeAspect as $tag) {
			if ($tagIn != '') {
				$tagIn .= ', ';
			}
			$tagIn .= '?';
			$tagInArray[] = $tag;
		}
		if ($tagIn != '') {
			$query = "SELECT id, `name` FROM tag_aspect WHERE `name` IN (" . $tagIn . ")";
			$result = $this->db->query($query, $tagInArray);
			while ($fld = $result->fetch(PDO::FETCH_ASSOC)) {
				$allAspectSearchTags[mb_strtolower($fld['name'], 'utf-8')] = $fld['name'];
				$allAspectSearchTagIds[mb_strtolower($fld['name'], 'utf-8')] = $fld['id'];
			}
		}

		unset($tagData);

		if (!empty($allAuthorSearchTagIds)) {
			$tagIdIn = '';
			foreach ($allAuthorSearchTagIds as $id) {
				if ($tagIdIn != '') {
					$tagIdIn .= ', ';
				}
				$tagIdIn .= '?';
				$this->sqlData[] = $id;
			}
			$this->joinAmount++;
			if ($this->sqlWhere != "") {
				$this->sqlWhere .= " AND ";
			} else {
				$this->sqlWhere .= "WHERE ";
			}
			$this->sqlJoins .= "LEFT JOIN wallpaper_tag_artist awtExc ON (awtExc.wallpaper_id = w.id AND awtExc.tag_artist_id IN (" . $tagIdIn . ")) ";
			$this->sqlWhere .= "awtExc.id IS NULL";
		}

		if (!empty($allPlatformSearchTagIds)) {
			$tagIdIn = '';
			foreach ($allPlatformSearchTagIds as $id) {
				if ($tagIdIn != '') {
					$tagIdIn .= ', ';
				}
				$tagIdIn .= '?';
				$this->sqlData[] = $id;
			}
			$this->joinAmount++;
			if ($this->sqlWhere != "") {
				$this->sqlWhere .= " AND ";
			} else {
				$this->sqlWhere .= "WHERE ";
			}
			$this->sqlJoins .= "LEFT JOIN wallpaper_tag_platform pwtExc ON (pwtExc.wallpaper_id = w.id AND pwtExc.tag_platform_id IN (" . $tagIdIn . ")) ";
			$this->sqlWhere .= "pwtExc.id IS NULL";

		}

		if (!empty($allAspectSearchTagIds)) {
			$tagIdIn = '';
			foreach ($allAspectSearchTagIds as $id) {
				if ($tagIdIn != '') {
					$tagIdIn .= ', ';
				}
				$tagIdIn .= '?';
				$this->sqlData[] = $id;
			}
			$this->joinAmount++;
			if ($this->sqlWhere != "") {
				$this->sqlWhere .= " AND ";
			} else {
				$this->sqlWhere .= "WHERE ";
			}
			$this->sqlJoins .= "LEFT JOIN wallpaper_tag_aspect aswtExc ON (aswtExc.wallpaper_id = w.id AND aswtExc.tag_aspect_id IN (" . $tagIdIn . ")) ";
			$this->sqlWhere .= "aswtExc.id IS NULL";
		}

		// @todo Colour support

		if (!empty($allSearchTagIds)) {
			$tagIdIn = '';
			foreach ($allSearchTagIds as $id) {
				if ($tagIdIn != '') {
					$tagIdIn .= ', ';
				}
				$tagIdIn .= '?';
				$this->sqlData[] = $id;
			}
			$this->joinAmount++;
			if ($this->sqlWhere != "") {
				$this->sqlWhere .= " AND ";
			} else {
				$this->sqlWhere .= "WHERE ";
			}
			$this->sqlJoins .= "LEFT JOIN wallpaper_tag wtExc ON (wtExc.wallpaper_id = w.id AND wtExc.tag_id IN (" . $tagIdIn . ")) ";
			$this->sqlWhere .= "wtExc.id IS NULL";
		}

		if (!empty($this->searchTagsExcludeCharacter)) {
			$allCharacterSearchTagIds = [];
			$tagInArray = [];
			$tagIn = '';
			foreach($this->searchTagsExcludeCharacter as $tag) {
				if ($tagIn != '') {
					$tagIn .= ', ';
				}
				$tagIn .= '?';
				$tagInArray[] = $tag;
			}
			if ($tagIn != '') {
				$query = "SELECT id, `name` FROM tag WHERE `name` IN (" . $tagIn . ")";
				$result = $this->db->query($query, $tagInArray);
				while ($fld = $result->fetch(PDO::FETCH_ASSOC)) {
					$allCharacterSearchTagIds[mb_strtolower($fld['name'], 'utf-8')] = $fld['id'];
				}
			}

			foreach ($this->searchTagsExcludeCharacter as $characterTag) {
				if (!empty($allCharacterSearchTagIds[mb_strtolower($characterTag, 'utf-8')])) {
					if ($this->sqlWhere != "") {
						$this->sqlWhere .= " AND ";
					} else {
						$this->sqlWhere .= "WHERE ";
					}

					$this->sqlWhere .= "w.chartags != ?";
					$this->sqlData[] = $allCharacterSearchTagIds[mb_strtolower($characterTag, 'utf-8')];
				}
			}
		}
	}

	/**
	 * @return Wallpaper[]
	 */
	public function getWallpapers() {
		return $this->wallpapers;
	}
	
	/**
	 * @return int
	 */
	public function getWallpaperCount() {
		return $this->wallpaperSearchCount;
	}
	
	/**
	 * Set current page.
	 * @param int $val
	 */
	public function setPageNumber($val) {
		$this->pageNumber = (int) $val;
	}
	
	/**
	 * @return int
	 */
	public function getPageNumber() {
		return $this->pageNumber;
	}
	
	/**
	 * Set how many wallpapers are displayer per page
	 * @param int $val
	 */
	public function setWallpapersPerPage($val) {
		$this->wallpapersPerPage = (int) $val;
	}
	
	/**
	 * @return int
	 */
	public function getWallpapersPerPage() {
		return $this->wallpapersPerPage;
	}
	
	/**
	 * @return string
	 */
	public function getPageTitleAddition() {
		return (!empty($this->pageTitleAddition) ? $this->pageTitleAddition . (!empty($this->pageTitleSearch) ? ' | ' : '') : '') . $this->pageTitleSearch;
	}
	
	/**
	 * @return string
	 */
	public function getMetaTags() {
		return $this->metaTags;
	}

	/**
	 * @return string[]
	 */
	public function getSearchTagsWithType() {
		$return = [];
		foreach($this->searchTagsCharacter as $tag) {
			$return[] = '=' . $tag;
		}
		foreach($this->searchTags as $tag) {
			$return[] = $tag;
		}
		foreach($this->searchTagsAspect as $tag) {
			$return[] = 'aspect:' . $tag;
		}
		foreach($this->searchTagsAuthor as $tag) {
			$return[] = 'author:' . $tag;
		}
		foreach($this->searchTagsColour as $tag) {
			$return[] = 'colour:' . $tag;
		}
		foreach($this->searchTagsMajorColour as $tag) {
			$return[] = 'major-colour:' . $tag;
		}
		foreach($this->searchTagsPlatform as $tag) {
			$return[] = 'platform:' . $tag;
		}

		return $return;
	}
	
	/**
	 * @return string
	 */
	public function getRss() {
		return '<link rel="alternate" type="application/rss+xml" title="RSS" href="'.$this->getRssUrl().'">';
	}
	
	/**
	 * @return string
	 */
	public function getRssUrl() {
		return 'http://'.$_SERVER['SERVER_NAME'].PUB_PATH_CAT.'feed/'.$this->rssSearch;
	}
	
	/**
	 * @return string
	 */
	public function getRssSearch() {
		return $this->rssSearch;
	}
	
	/**
	 * @return string
	 */
	public function getJavaScript() {
		global $user;

		return '$(document).ready(function() {window.wallpaperList = WallpaperList({' .
			'"ajaxLoadMorePage": \'' . Format::escapeQuotes($this->ajaxLoadMorePage) . '\', ' .
			'"ajaxRedirect": \'' . Format::escapeQuotes($this->redirect) . '\', ' .
			'"basePathUrl": \'' . Format::escapeQuotes(PUB_PATH_CAT) . '\', ' .
			'"largeWallpaperThumbs": ' . ($this->largeWallpaperThumbs ? 'true' : 'false') . ', ' .
			'"nextPage": ' . ($this->pageNumber + 1) . ', ' .
			'"userIsAnonymous": ' . ($user->getIsAnonymous() ? 'true' : 'false') .
		'});});';
	}

	public function getJavaScriptFiles() {
		return array('wallpaper-list-2.0.js');
	}
	
	/**
	 * @return string
	 */
	public function getMeta() {
		$meta = "\n";
		if ($this->displayOrder != self::ORDER_RANDOM) {
			if (!empty($this->wallpapers)) {
				$image1 = '';
				$image2 = '';
				$image3 = '';
				$image4 = '';

				if (count($this->wallpapers) == 1) {
					$meta .= '		<meta name="twitter:card" content="photo">'."\n";
					$meta .= '		<meta name="twitter:image:src" content="http://'.$_SERVER['SERVER_NAME'].PUB_PATH.'images/r2_'.$this->wallpapers[0]->getFileId().'.jpg">'."\n";
				} else {
					$meta .= '		<meta name="twitter:card" content="gallery">'."\n";
					if (count($this->wallpapers) >= 4) {
						$image1 = $this->wallpapers[0]->getFileId();
						$image2 = $this->wallpapers[1]->getFileId();
						$image3 = $this->wallpapers[2]->getFileId();
						$image4 = $this->wallpapers[3]->getFileId();
					} elseif (count($this->wallpapers) == 3) {
						$image1 = $this->wallpapers[0]->getFileId();
						$image2 = $this->wallpapers[1]->getFileId();
						$image3 = $this->wallpapers[2]->getFileId();
						$image4 = $this->wallpapers[0]->getFileId();
					} elseif (count($this->wallpapers) == 2) {
						$image1 = $this->wallpapers[0]->getFileId();
						$image2 = $this->wallpapers[1]->getFileId();
						$image3 = $this->wallpapers[1]->getFileId();
						$image4 = $this->wallpapers[0]->getFileId();
					}
					$meta .= '		<meta name="twitter:image0:src" content="http://'.$_SERVER['SERVER_NAME'].PUB_PATH.'images/r2_'.$image1.'.jpg">'."\n";
					$meta .= '		<meta name="twitter:image1:src" content="http://'.$_SERVER['SERVER_NAME'].PUB_PATH.'images/r2_'.$image2.'.jpg">'."\n";
					$meta .= '		<meta name="twitter:image2:src" content="http://'.$_SERVER['SERVER_NAME'].PUB_PATH.'images/r2_'.$image3.'.jpg">'."\n";
					$meta .= '		<meta name="twitter:image3:src" content="http://'.$_SERVER['SERVER_NAME'].PUB_PATH.'images/r2_'.$image4.'.jpg">'."\n";
				}
			}
		} else {
			$meta .= '		<meta name="twitter:card" content="summary">'."\n";
			$meta .= '		<meta name="twitter:description" content="Random wallpaper listing.">'."\n";
		}
		return $meta;
	}
	
	/**
	 * @return string
	 */
	public function output() {
		global $response;
		ob_start();

		$response->responseVariables->rss_search = $this->getRssSearch();
		$response->responseVariables->large_wallpaper_thumbs = $this->largeWallpaperThumbs;
		$response->responseVariables->wallpaper_count = $this->wallpaperSearchCount;
		$response->responseVariables->maxJoinAmountExceeded = $this->maxJoinAmountExceeded;
		$response->responseVariables->wallpapers = $this->getWallpapers();

		if ($this->customTemplate !== null && file_exists(DOC_DIR.THEME.'/'.$this->customTemplate)) {
			/** @noinspection PhpIncludeInspection */
			require_once(DOC_DIR.THEME.'/'.$this->customTemplate);
		} else {
			if ($this->renderWallpapersOnly) {
				require_once(DOC_DIR . THEME . '/wallpaper_list_wallpapers.php');
			} else {
				require_once(DOC_DIR . THEME . '/wallpaper_list.php');
			}
		}
		return ob_get_clean();
	}
	
	/**
	 * Checks whether there are too many search tags or not.
	 * @return bool
	 */
	private function checkTagCount() {
		$count = count($this->searchTags) + count($this->searchTagsAspect) + count($this->searchTagsAuthor)
				+ count($this->searchTagsCharacter) + count($this->searchTagsColour)
				+ count($this->searchTagsMajorColour) + count($this->searchTagsPlatform);
		if ($count >= 8) {
			return false;
		}
		return true;
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

	/**
	 * @param string $pageTitleAddition
	 */
	public function setPageTitleAddition($pageTitleAddition) {
		$this->pageTitleAddition = (string) $pageTitleAddition;
	}

	/**
	 * @return int
	 */
	public function getOffset() {
		if ($this->offset !== null) {
			return $this->offset;
		} else {
			return ($this->pageNumber - 1) * $this->wallpapersPerPage;
		}
	}

	/**
	 * @param int|null $offset
	 */
	public function setOffset($offset) {
		$this->offset = (int) $offset;
	}

	/**
	 * @return int
	 */
	public function getJoinAmount() {
		return $this->joinAmount;
	}

	/**
	 * @return int|null
	 */
	public function getSearchFavouritesUserId() {
		return $this->searchFavouritesUserId;
	}

	/**
	 * @param int|null $searchFavouritesUserId
	 */
	public function setSearchFavouritesUserId($searchFavouritesUserId) {
		if ($searchFavouritesUserId === null) {
			$this->searchFavouritesUserId = null;
		} else {
			$this->searchFavouritesUserId = (int) $searchFavouritesUserId;
		}
	}
}