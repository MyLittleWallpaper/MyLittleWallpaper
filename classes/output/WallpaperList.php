<?php

declare(strict_types=1);

namespace MyLittleWallpaper\classes\output;

use Exception;
use MyLittleWallpaper\classes\Database;
use MyLittleWallpaper\classes\Format;
use MyLittleWallpaper\classes\Wallpaper;
use PDO;

/**
 * Wallpaper list class.
 * Used for loading a list of wallpapers.
 */
class WallpaperList extends Output
{
    // Constants for wallpaper list order
    public const ORDER_DATE_ADDED = 'date';
    public const ORDER_POPULARITY = 'popularity';
    public const ORDER_RANDOM     = -1;

    /**
     * Order selection titles
     *
     * @param string|int $val Use self::ORDER_*
     *
     * @return string
     */
    public static function getOrderTitle($val): ?string
    {
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
    public const RESOLUTION_1366X768  = 4;
    public const RESOLUTION_1680X1050 = 3;
    public const RESOLUTION_1920X1080 = 2;
    public const RESOLUTION_1920X1200 = 1;
    public const RESOLUTION_2560X1600 = 5;
    public const RESOLUTION_2560X1440 = 6;
    public const RESOLUTION_3840X2160 = 7;

    /**
     * @param int $val Use self::RESOLUTION_*
     *
     * @return string
     */
    public static function getResolutionTitle(int $val): string
    {
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
    public const MAX_JOIN_AMOUNT = 8;

    /**
     * @var int
     */
    private int $joinAmount = 0;

    /**
     * @var string
     */
    private string $pageTitleAddition = '';

    /**
     * @var string
     */
    private string $pageTitleSearch = '';

    /**
     * @var string
     */
    private string $metaTags = '';

    /**
     * @var int
     */
    private int $pageNumber = 1;

    /**
     * @var int|null
     */
    private ?int $offset = null;

    /**
     * @var string
     */
    private string $pageBase = '';

    /**
     * @var string
     */
    private string $redirect = '';

    /**
     * @var string
     */
    private string $rssSearch = '';

    /**
     * @var int
     */
    private int $wallpapersPerPage = 50;

    /**
     * @var Wallpaper[]
     */
    private array $wallpapers = [];

    /**
     * How many total wallpapers the search finds.
     * @var int
     */
    private int $wallpaperSearchCount = 0;

    /**
     * @var int|null
     */
    private ?int $searchFavouritesUserId = null;

    /**
     * Either date in format YYYY-MM-DD or null.
     * @var string|null
     */
    private ?string $searchDate = null;

    /**
     * Use self::RESOLUTION_* or set as null
     * @var int|null
     */
    private ?int $searchSize = null;

    /**
     * @var string[]
     */
    private array $searchTags = [];

    /**
     * @var string[]
     */
    private array $searchTagsCharacter = [];

    /**
     * @var string[]
     */
    private array $searchTagsAuthor = [];

    /**
     * @var string[]
     */
    private array $searchTagsAspect = [];

    /**
     * @var string[]
     */
    private array $searchTagsPlatform = [];

    /**
     * @var string[]
     */
    private array $searchTagsColour = [];

    /**
     * @var string[]
     */
    private array $searchTagsMajorColour = [];

    /**
     * @var string[]
     */
    private array $searchTagsAny = [];

    /**
     * @var string[]
     */
    private array $searchTagsAnyCharacter = [];

    /**
     * @var string[]
     */
    private array $searchTagsAnyAuthor = [];

    /**
     * @var string[]
     */
    private array $searchTagsAnyAspect = [];

    /**
     * @var string[]
     */
    private array $searchTagsAnyPlatform = [];

    /**
     * @var string[]
     */
    private array $searchTagsAnyColour = [];

    /**
     * @var string[]
     */
    private array $searchTagsAnyMajorColour = [];

    /**
     * @var string[]
     */
    private array $searchTagsExclude = [];

    /**
     * @var string[]
     */
    private array $searchTagsExcludeCharacter = [];

    /**
     * @var string[]
     */
    private array $searchTagsExcludeAuthor = [];

    /**
     * @var string[]
     */
    private array $searchTagsExcludeAspect = [];

    /**
     * @var string[]
     */
    private array $searchTagsExcludePlatform = [];

    /**
     * @var string[]
     */
    private array $searchTagsExcludeColour = [];

    /**
     * @var string[]
     */
    private array $searchTagsExcludeMajorColour = [];

    /**
     * @var bool
     */
    private bool $maxJoinAmountExceeded = false;

    /**
     * @var int|null
     */
    private ?int $category = null;

    /**
     * Use self::ORDER_*
     * Default self::ORDER_DATE_ADDED
     * @var string|int
     */
    private $displayOrder = self::ORDER_DATE_ADDED;

    /**
     * @var bool
     */
    private bool $renderWallpapersOnly = false;

    /**
     * @var bool
     */
    private bool $largeWallpaperThumbs = false;

    /**
     * @var string|null
     */
    private ?string $customTemplate = null;

    /**
     * @var string
     */
    private string $ajaxLoadMorePage = 'list';

    /**
     * @var Database
     */
    private Database $db;

    /**
     * @var string
     */
    private string $sqlJoins = "";

    /**
     * @var string
     */
    private string $sqlWhere = "";

    /**
     * @var array
     */
    private array $sqlData = [];


    /**
     * @param Database|null $db If null, looks for $GLOBALS['db']
     */
    public function __construct(?Database $db = null)
    {
        if (!($db instanceof Database)) {
            if (!isset($GLOBALS['db']) || !($GLOBALS['db'] instanceof Database)) {
                throw new Exception('No database connection found');
            }

            $this->db =& $GLOBALS['db'];
        } else {
            $this->db = $db;
        }
    }

    /**
     * Loads the search and ordering straight from request
     *
     * @return void
     */
    public function loadSearchFromRequest(): void
    {
        $getValues = $_GET;

        if (
            isset($getValues['page']) && filter_var($getValues['page'], FILTER_VALIDATE_INT) !== false &&
            $getValues['page'] > 1
        ) {
            $this->pageNumber = (int)$getValues['page'];
        }

        if (!empty($getValues['search'])) {
            if (strpos($getValues['search'], ',') !== false) {
                $tagList = explode(',', $getValues['search']);
            } else {
                $tagList = [$getValues['search']];
            }
            $this->searchAddTags($tagList);
        }
        if (!empty($getValues['searchAny'])) {
            if (strpos($getValues['searchAny'], ',') !== false) {
                $tagList = explode(',', $getValues['searchAny']);
            } else {
                $tagList = [$getValues['searchAny']];
            }
            $this->searchAddTagsAny($tagList);
        }
        if (!empty($getValues['searchExclude'])) {
            if (strpos($getValues['searchExclude'], ',') !== false) {
                $tagList = explode(',', $getValues['searchExclude']);
            } else {
                $tagList = [$getValues['searchExclude']];
            }
            $this->searchAddTagsExclude($tagList);
        }

        if (!empty($getValues['size']) && filter_var($getValues['size'], FILTER_VALIDATE_INT) !== false) {
            $this->searchAddSize((int)$getValues['size']);
        }

        if (!empty($getValues['date'])) {
            $this->searchAddDate($getValues['date']);
        }

        if (!empty($getValues['sort'])) {
            $this->setDisplayOrder($getValues['sort']);
        }
        $pageBase = '';
        $redirect = '';
        foreach ($getValues as $key => $val) {
            if (
                strcmp($key, 'page') !== 0 &&
                strcmp($key, 'date') !== 0 &&
                strcmp($key, 'sort') !== 0 &&
                strcmp($key, 'c') !== 0 &&
                !empty($val)
            ) {
                if ($pageBase === '') {
                    $pageBase .= '?';
                    $redirect .= '?';
                } else {
                    $pageBase .= '&amp;';
                    $redirect .= '&';
                }
                $pageBase .= $key . '=' . urlencode($val);
                $redirect .= $key . '=' . urlencode($val);
            }
        }

        $rssSearch = $pageBase;

        if (!empty($getValues['date'])) {
            if ($pageBase === '') {
                $pageBase .= '?';
                $redirect .= '?';
            } else {
                $pageBase .= '&amp;';
                $redirect .= '&';
            }
            $pageBase .= 'date=' . urlencode($getValues['date']);
            $redirect .= 'date=' . urlencode($getValues['date']);
        }

        if (!empty($getValues['sort']) && $getValues['sort'] === 'popularity') {
            if ($pageBase === '') {
                $pageBase .= '?';
                $redirect .= '?';
            } else {
                $pageBase .= '&amp;';
                $redirect .= '&';
            }
            $pageBase .= 'sort=' . urlencode($getValues['sort']);
            $redirect .= 'sort=' . urlencode($getValues['sort']);
        }

        $this->pageBase  = $pageBase;
        $this->redirect  = $redirect;
        $this->rssSearch = $rssSearch;
    }

    /**
     * @param string|int $val use self::ORDER_*
     *
     * @return void
     */
    public function setDisplayOrder($val): void
    {
        switch ($val) {
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
     *
     * @param bool $val
     *
     * @return void
     */
    public function setRenderWallpapersOnly(bool $val): void
    {
        $this->renderWallpapersOnly = (bool)$val;
    }

    /**
     * Set whether to show large wallpaper thumbs or not.
     * Large wallpaper thumbs are not shown by default.
     *
     * @param bool $val
     *
     * @return void
     */
    public function setLargeWallpaperThumbs(bool $val): void
    {
        $this->largeWallpaperThumbs = (bool)$val;
    }

    /**
     * Set a custom template.
     * Set to null to remove custom template.
     *
     * @param string|null $val
     *
     * @return void
     */
    public function setCustomTemplate(?string $val): void
    {
        $this->customTemplate = $val;
    }

    /**
     * Set the php-script that loads more wallpapers.
     * Default 'list'
     *
     * @param string $val
     *
     * @return void
     */
    public function setAjaxLoadMorePage(string $val): void
    {
        $this->ajaxLoadMorePage = $val;
    }

    /**
     * Set wallpaper category.
     * Default value is null, which gets wallpapers from all categories.
     *
     * @param int|null $val
     *
     * @return void
     */
    public function setCategory(?int $val): void
    {
        if ($val === null || $val <= 0) {
            $this->category = null;
        } else {
            $this->category = (int)$val;
        }
    }

    /**
     * Validates and adds wallpaper add date to search.
     *
     * @param string $val In format that strtotime recognizes
     *
     * @return void
     */
    public function searchAddDate(string $val): void
    {
        $thatime = @strtotime($val);
        if ($thatime !== false) {
            $this->searchDate = date('Y-m-d', $thatime);
        }
    }

    /**
     * Validates and adds the wallpaper size to search.
     *
     * @param int $val
     *
     * @return void
     */
    public function searchAddSize(int $val): void
    {
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
     *
     * @return void
     */
    public function searchAddTag(string $tag): void
    {
        if ($this->checkTagCount()) {
            $this->searchTags[] = $tag;
        }
    }

    /**
     * @param string $tag
     *
     * @return void
     */
    public function searchAddTagAspect(string $tag): void
    {
        if ($this->checkTagCount()) {
            $this->searchTagsAspect[] = $tag;
        }
    }

    /**
     * @param string $tag
     *
     * @return void
     */
    public function searchAddTagAuthor(string $tag): void
    {
        if ($this->checkTagCount()) {
            $this->searchTagsAuthor[] = $tag;
        }
    }

    /**
     * @param string $tag
     *
     * @return void
     */
    public function searchAddTagCharacter(string $tag): void
    {
        if ($this->checkTagCount()) {
            $this->searchTagsCharacter[] = $tag;
        }
    }

    /**
     * @param string $tag
     *
     * @return void
     */
    public function searchAddTagColour(string $tag): void
    {
        if ($this->checkTagCount()) {
            $this->searchTagsColour[] = $tag;
        }
    }

    /**
     * @param string $tag
     *
     * @return void
     */
    public function searchAddTagMajorColour(string $tag): void
    {
        if ($this->checkTagCount()) {
            $this->searchTagsMajorColour[] = $tag;
        }
    }

    /**
     * @param string $tag
     *
     * @return void
     */
    public function searchAddTagPlatform(string $tag): void
    {
        if ($this->checkTagCount()) {
            $this->searchTagsPlatform[] = $tag;
        }
    }

    /**
     * @param string[] $tags
     *
     * @return void
     */
    public function searchAddTags(array $tags): void
    {
        foreach ($tags as $tag) {
            $tag = trim($tag);
            if (str_replace(' ', '', $tag) !== '') {
                $this->searchAddTagWithType($tag);
            }
        }
    }

    /**
     * @param string[] $tags
     *
     * @return void
     */
    public function searchAddTagsAny(array $tags): void
    {
        foreach ($tags as $tag) {
            $tag = trim($tag);
            if (str_replace(' ', '', $tag) !== '') {
                $this->searchAddTagAnyWithType($tag);
            }
        }
    }

    /**
     * @param string[] $tags
     *
     * @return void
     */
    public function searchAddTagsExclude(array $tags): void
    {
        foreach ($tags as $tag) {
            $tag = trim($tag);
            if (str_replace(' ', '', $tag) !== '') {
                $this->searchAddTagExcludeWithType($tag);
            }
        }
    }

    /**
     * @param string $tag
     *
     * @return void
     */
    public function searchAddTagWithType(string $tag): void
    {
        if (!$this->checkTagCount()) {
            return;
        }
        if (strcmp(mb_substr($tag, 0, 1, 'utf-8'), '=') === 0) {
            $this->searchTagsCharacter[] = mb_substr($tag, 1, mb_strlen($tag, 'utf-8'), 'utf-8');
        } elseif (mb_substr($tag, 0, 7, 'utf-8') === 'author:') {
            $this->searchTagsAuthor[] = mb_substr($tag, 7, mb_strlen($tag, 'utf-8'), 'utf-8');
        } elseif (mb_substr($tag, 0, 9, 'utf-8') === 'platform:') {
            $this->searchTagsPlatform[] = mb_substr($tag, 9, mb_strlen($tag, 'utf-8'), 'utf-8');
        } elseif (mb_substr($tag, 0, 7, 'utf-8') === 'aspect:') {
            $this->searchTagsAspect[] = mb_substr($tag, 7, mb_strlen($tag, 'utf-8'), 'utf-8');
        } elseif (mb_substr($tag, 0, 7, 'utf-8') === 'colour:') {
            $this->searchTagsColour[] = mb_substr($tag, 7, mb_strlen($tag, 'utf-8'), 'utf-8');
        } elseif (mb_substr($tag, 0, 13, 'utf-8') === 'major-colour:') {
            $this->searchTagsMajorColour[] = mb_substr($tag, 13, mb_strlen($tag, 'utf-8'), 'utf-8');
        } else {
            $this->searchTags[] = $tag;
        }
    }

    /**
     * @param string $tag
     *
     * @return void
     */
    public function searchAddTagAnyWithType(string $tag): void
    {
        if (!$this->checkTagCount()) {
            return;
        }
        if (strcmp(mb_substr($tag, 0, 1, 'utf-8'), '=') === 0) {
            $this->searchTagsAnyCharacter[] = mb_substr($tag, 1, mb_strlen($tag, 'utf-8'), 'utf-8');
        } elseif (mb_substr($tag, 0, 7, 'utf-8') === 'author:') {
            $this->searchTagsAnyAuthor[] = mb_substr($tag, 7, mb_strlen($tag, 'utf-8'), 'utf-8');
        } elseif (mb_substr($tag, 0, 9, 'utf-8') === 'platform:') {
            $this->searchTagsAnyPlatform[] = mb_substr($tag, 9, mb_strlen($tag, 'utf-8'), 'utf-8');
        } elseif (mb_substr($tag, 0, 7, 'utf-8') === 'aspect:') {
            $this->searchTagsAnyAspect[] = mb_substr($tag, 7, mb_strlen($tag, 'utf-8'), 'utf-8');
        } elseif (mb_substr($tag, 0, 7, 'utf-8') === 'colour:') {
            $this->searchTagsAnyColour[] = mb_substr($tag, 7, mb_strlen($tag, 'utf-8'), 'utf-8');
        } elseif (mb_substr($tag, 0, 13, 'utf-8') === 'major-colour:') {
            $this->searchTagsAnyMajorColour[] = mb_substr($tag, 13, mb_strlen($tag, 'utf-8'), 'utf-8');
        } else {
            $this->searchTagsAny[] = $tag;
        }
    }

    /**
     * @param string $tag
     *
     * @return void
     */
    public function searchAddTagExcludeWithType(string $tag): void
    {
        if (!$this->checkTagCount()) {
            return;
        }
        if (strcmp(mb_substr($tag, 0, 1, 'utf-8'), '=') === 0) {
            $this->searchTagsExcludeCharacter[] = mb_substr($tag, 1, mb_strlen($tag, 'utf-8'), 'utf-8');
        } elseif (mb_substr($tag, 0, 7, 'utf-8') === 'author:') {
            $this->searchTagsExcludeAuthor[] = mb_substr($tag, 7, mb_strlen($tag, 'utf-8'), 'utf-8');
        } elseif (mb_substr($tag, 0, 9, 'utf-8') === 'platform:') {
            $this->searchTagsExcludePlatform[] = mb_substr($tag, 9, mb_strlen($tag, 'utf-8'), 'utf-8');
        } elseif (mb_substr($tag, 0, 7, 'utf-8') === 'aspect:') {
            $this->searchTagsExcludeAspect[] = mb_substr($tag, 7, mb_strlen($tag, 'utf-8'), 'utf-8');
        } elseif (mb_substr($tag, 0, 7, 'utf-8') === 'colour:') {
            $this->searchTagsExcludeColour[] = mb_substr($tag, 7, mb_strlen($tag, 'utf-8'), 'utf-8');
        } elseif (mb_substr($tag, 0, 13, 'utf-8') === 'major-colour:') {
            $this->searchTagsExcludeMajorColour[] = mb_substr($tag, 13, mb_strlen($tag, 'utf-8'), 'utf-8');
        } else {
            $this->searchTagsExclude[] = $tag;
        }
    }

    /**
     * Fetches the wallpapers from the database and stores to $wallpapers.
     *
     * @return void
     */
    public function loadWallpapers(): void
    {
        $sql = <<<SQL
            SELECT w.id, w.name, w.url, w.filename, w.file, w.width, w.height, w.mime, w.timeadded, w.clicks, w.favs,
                   w.no_aspect, w.no_resolution, w.direct_with_link FROM 
            wallpaper w 
        SQL;

        $this->sqlJoins = "";
        $this->sqlWhere = "";
        $this->sqlData  = [];

        $this->pageTitleSearch = '';
        $this->metaTags        = '';

        $this->loadWallpapersTagExcludeSearch();
        $this->loadWallpapersTagSearch();
        $this->loadWallpapersTagAnySearch();

        if ($this->displayOrder === self::ORDER_POPULARITY) {
            $order = "ORDER BY w.clicks DESC, w.id DESC ";
        } else {
            $order = "ORDER BY w.id DESC ";
        }

        if ($this->searchSize === self::RESOLUTION_3840X2160) {
            if ($this->sqlWhere !== "") {
                $this->sqlWhere .= " AND ";
            } else {
                $this->sqlWhere .= "WHERE ";
            }
            $this->sqlWhere .= "w.width >= 3840 AND w.height >= 2160 ";
        } elseif ($this->searchSize == self::RESOLUTION_2560X1600) {
            if ($this->sqlWhere !== "") {
                $this->sqlWhere .= " AND ";
            } else {
                $this->sqlWhere .= "WHERE ";
            }
            $this->sqlWhere .= "w.width >= 2560 AND w.height >= 1600 ";
        } elseif ($this->searchSize == self::RESOLUTION_2560X1440) {
            if ($this->sqlWhere !== "") {
                $this->sqlWhere .= " AND ";
            } else {
                $this->sqlWhere .= "WHERE ";
            }
            $this->sqlWhere .= "w.width >= 2560 AND w.height >= 1440 ";
        } elseif ($this->searchSize == self::RESOLUTION_1920X1200) {
            if ($this->sqlWhere !== "") {
                $this->sqlWhere .= " AND ";
            } else {
                $this->sqlWhere .= "WHERE ";
            }
            $this->sqlWhere .= "w.width >= 1920 AND w.height >= 1200 ";
        } elseif ($this->searchSize == self::RESOLUTION_1920X1080) {
            if ($this->sqlWhere !== "") {
                $this->sqlWhere .= " AND ";
            } else {
                $this->sqlWhere .= "WHERE ";
            }
            $this->sqlWhere .= "w.width >= 1920 AND w.height >= 1080 ";
        } elseif ($this->searchSize == self::RESOLUTION_1680X1050) {
            if ($this->sqlWhere !== "") {
                $this->sqlWhere .= " AND ";
            } else {
                $this->sqlWhere .= "WHERE ";
            }
            $this->sqlWhere .= "w.width >= 1680 AND w.height >= 1050 ";
        } elseif ($this->searchSize == self::RESOLUTION_1366X768) {
            if ($this->sqlWhere !== "") {
                $this->sqlWhere .= " AND ";
            } else {
                $this->sqlWhere .= "WHERE ";
            }
            $this->sqlWhere .= "w.width >= 1366 AND w.height >= 768 ";
        }

        if ($this->sqlWhere !== "") {
            $this->sqlWhere .= " AND ";
        } else {
            $this->sqlWhere .= "WHERE ";
        }
        $this->sqlWhere .= "w.deleted = 0 ";

        if ($this->searchFavouritesUserId !== null) {
            $this->sqlJoins  .= " JOIN wallpaper_fav fav ON (fav.wallpaper_id = w.id) ";
            $this->sqlWhere  .= "AND fav.user_id = ? ";
            $this->sqlData[] .= $this->searchFavouritesUserId;
        }

        if ($this->category !== null) {
            $this->sqlWhere  .= " AND w.series = ? ";
            $this->sqlData[] = $this->category;
        }

        if ($this->searchDate !== null) {
            $searchDateUnixTimestamp = strtotime($this->searchDate);
            $this->sqlWhere          .= sprintf(
                'AND w.timeadded > %s AND w.timeadded < %s ',
                mktime(
                    0,
                    0,
                    0,
                    (int)date('n', $searchDateUnixTimestamp),
                    (int)date('j', $searchDateUnixTimestamp),
                    (int)date('Y', $searchDateUnixTimestamp)
                ),
                mktime(
                    23,
                    59,
                    59,
                    (int)date('n', $searchDateUnixTimestamp),
                    (int)date('j', $searchDateUnixTimestamp),
                    (int)date('Y', $searchDateUnixTimestamp)
                )
            );
            $this->pageTitleSearch   = date('Y-m-d', $searchDateUnixTimestamp);
        }
        $sql .= $this->sqlJoins . $this->sqlWhere;
        $sql .= "GROUP BY w.id " . $order;

        if ($this->displayOrder != self::ORDER_RANDOM) {
            $offset = $this->offset ?? (($this->pageNumber - 1) * $this->wallpapersPerPage);
            $sql .= "LIMIT " . $this->wallpapersPerPage . " OFFSET " . $offset;
        }

        if ($this->joinAmount > self::MAX_JOIN_AMOUNT) {
            $this->maxJoinAmountExceeded = true;
            return;
        }

        // Wallpaper count query
        $countSql = "SELECT ";
        $countSql .= "COUNT(1) cnt FROM (SELECT DISTINCT w.id FROM wallpaper w " . $this->sqlJoins . $this->sqlWhere .
            ") asd";

        // How many wallpapers there are in the search result
        $countResult = $this->db->query($countSql, $this->sqlData);
        while ($row = $countResult->fetch(PDO::FETCH_ASSOC)) {
            $this->wallpaperSearchCount = (int)$row['cnt'];
        }

        // Fetch the wallpapers
        if ($this->displayOrder == self::ORDER_RANDOM) {
            if ($this->wallpaperSearchCount === 0) {
                $this->wallpapers = [];
            } else {
                for ($a = 0; $a < $this->wallpapersPerPage; $a++) {
                    $randomOffset = random_int(0, ($this->wallpaperSearchCount - 1));
                    $res          = $this->db->query($sql . "LIMIT 1 OFFSET " . $randomOffset, $this->sqlData);
                    while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
                        $this->wallpapers[] = new Wallpaper($row);
                    }
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
     *
     * @return void
     */
    private function loadWallpapersTagSearch(): void
    {
        $tagJoinCount         = 0;
        $authorJoinCount      = 0;
        $aspectJoinCount      = 0;
        $platformJoinCount    = 0;
        $colourJoinCount      = 0;
        $majorColourJoinCount = 0;

        $allSearchTags   = [];
        $allSearchTagIds = [];
        $tagInArray      = [];
        $tagIn           = '';
        foreach ($this->searchTags as $tag) {
            if ($tagIn !== '') {
                $tagIn .= ', ';
            }
            $tagIn        .= '?';
            $tagInArray[] = $tag;
        }
        if ($tagIn !== '') {
            $query  = "SELECT id, `name` FROM tag WHERE `name` IN (" . $tagIn . ")";
            $result = $this->db->query($query, $tagInArray);
            while ($fld = $result->fetch(PDO::FETCH_ASSOC)) {
                $allSearchTags[mb_strtolower($fld['name'], 'utf-8')]   = $fld['name'];
                $allSearchTagIds[mb_strtolower($fld['name'], 'utf-8')] = $fld['id'];
            }
        }

        $allAuthorSearchTags   = [];
        $allAuthorSearchTagIds = [];
        $tagInArray            = [];
        $tagIn                 = '';
        foreach ($this->searchTagsAuthor as $tag) {
            if ($tagIn !== '') {
                $tagIn .= ', ';
            }
            $tagIn        .= '?';
            $tagInArray[] = $tag;
        }
        if ($tagIn !== '') {
            $query  = "SELECT id, `name` FROM tag_artist WHERE `name` IN (" . $tagIn . ")";
            $result = $this->db->query($query, $tagInArray);
            while ($fld = $result->fetch(PDO::FETCH_ASSOC)) {
                $allAuthorSearchTags[mb_strtolower($fld['name'], 'utf-8')]   = $fld['name'];
                $allAuthorSearchTagIds[mb_strtolower($fld['name'], 'utf-8')] = $fld['id'];
            }
        }

        $allPlatformSearchTags   = [];
        $allPlatformSearchTagIds = [];
        $tagInArray              = [];
        $tagIn                   = '';
        foreach ($this->searchTagsPlatform as $tag) {
            if ($tagIn !== '') {
                $tagIn .= ', ';
            }
            $tagIn        .= '?';
            $tagInArray[] = $tag;
        }
        if ($tagIn !== '') {
            $query  = "SELECT id, `name` FROM tag_platform WHERE `name` IN (" . $tagIn . ")";
            $result = $this->db->query($query, $tagInArray);
            while ($fld = $result->fetch(PDO::FETCH_ASSOC)) {
                $allPlatformSearchTags[mb_strtolower($fld['name'], 'utf-8')]   = $fld['name'];
                $allPlatformSearchTagIds[mb_strtolower($fld['name'], 'utf-8')] = $fld['id'];
            }
        }

        $allAspectSearchTags   = [];
        $allAspectSearchTagIds = [];
        $tagInArray            = [];
        $tagIn                 = '';
        foreach ($this->searchTagsAspect as $tag) {
            if ($tagIn !== '') {
                $tagIn .= ', ';
            }
            $tagIn        .= '?';
            $tagInArray[] = $tag;
        }
        if ($tagIn !== '') {
            $query  = "SELECT id, `name` FROM tag_aspect WHERE `name` IN (" . $tagIn . ")";
            $result = $this->db->query($query, $tagInArray);
            while ($fld = $result->fetch(PDO::FETCH_ASSOC)) {
                $allAspectSearchTags[mb_strtolower($fld['name'], 'utf-8')]   = $fld['name'];
                $allAspectSearchTagIds[mb_strtolower($fld['name'], 'utf-8')] = $fld['id'];
            }
        }

        unset($tagData);

        foreach ($this->searchTagsCharacter as $tag) {
            if (!empty($allSearchTags[mb_strtolower($tag, 'utf-8')])) {
                $this->metaTags .= ', =' . $allSearchTags[mb_strtolower($tag, 'utf-8')];
                if (!empty($this->pageTitleSearch)) {
                    $this->pageTitleSearch .= ', ';
                }
                $this->pageTitleSearch .= '=' . $allSearchTags[mb_strtolower($tag, 'utf-8')];
            }
        }

        foreach ($allAuthorSearchTagIds as $key => $id) {
            $this->joinAmount++;
            if ($this->sqlWhere !== "") {
                $this->sqlWhere .= " AND ";
            } else {
                $this->sqlWhere .= "WHERE ";
            }
            $authorJoinCount++;
            $this->sqlJoins  .= "JOIN wallpaper_tag_artist awt" . $authorJoinCount . " ON (awt" . $authorJoinCount .
                ".wallpaper_id = w.id) ";
            $this->sqlWhere  .= "awt" . $authorJoinCount . ".tag_artist_id = ?";
            $this->sqlData[] = $id;
            if (!empty($allAuthorSearchTags[$key])) {
                $this->metaTags .= ', ' . $allAuthorSearchTags[$key];
                if (!empty($this->pageTitleSearch)) {
                    $this->pageTitleSearch .= ', ';
                }
                $this->pageTitleSearch .= $allAuthorSearchTags[$key];
            }
        }

        foreach ($allPlatformSearchTagIds as $key => $id) {
            $this->joinAmount++;
            if ($this->sqlWhere !== "") {
                $this->sqlWhere .= " AND ";
            } else {
                $this->sqlWhere .= "WHERE ";
            }
            $platformJoinCount++;
            $this->sqlJoins  .= "JOIN wallpaper_tag_platform pwt" . $platformJoinCount . " ON (pwt" .
                $platformJoinCount . ".wallpaper_id = w.id) ";
            $this->sqlWhere  .= "pwt" . $platformJoinCount . ".tag_platform_id = ?";
            $this->sqlData[] = $id;
            if (!empty($allPlatformSearchTags[$key])) {
                $this->metaTags .= ', ' . $allPlatformSearchTags[$key];
                if (!empty($this->pageTitleSearch)) {
                    $this->pageTitleSearch .= ', ';
                }
                $this->pageTitleSearch .= $allPlatformSearchTags[$key];
            }
        }

        foreach ($allAspectSearchTagIds as $id) {
            $this->joinAmount++;
            if ($this->sqlWhere !== "") {
                $this->sqlWhere .= " AND ";
            } else {
                $this->sqlWhere .= "WHERE ";
            }
            $aspectJoinCount++;
            $this->sqlJoins  .= "JOIN wallpaper_tag_aspect aswt" . $aspectJoinCount . " ON (aswt" . $aspectJoinCount .
                ".wallpaper_id = w.id) ";
            $this->sqlWhere  .= "aswt" . $aspectJoinCount . ".tag_aspect_id = ?";
            $this->sqlData[] = $id;
        }

        foreach ($this->searchTagsColour as $tag) {
            $this->joinAmount += 2;
            if ($this->sqlWhere !== "") {
                $this->sqlWhere .= " AND ";
            } else {
                $this->sqlWhere .= "WHERE ";
            }
            $colourJoinCount++;
            $this->sqlJoins  .= "JOIN wallpaper_tag_colour clwt" . $colourJoinCount . " ON (clwt" . $colourJoinCount .
                ".wallpaper_id = w.id) ";
            $this->sqlJoins  .= "JOIN wallpaper_tag_colour_similar clt" . $colourJoinCount . " ON (clwt" .
                $colourJoinCount . ".tag_colour = clt" . $colourJoinCount . ".similar_colour) ";
            $this->sqlWhere  .= "clt" . $colourJoinCount . ".colour = ?";
            $this->sqlData[] = $tag;
        }

        foreach ($this->searchTagsMajorColour as $tag) {
            $this->joinAmount += 2;
            if ($this->sqlWhere !== "") {
                $this->sqlWhere .= " AND ";
            } else {
                $this->sqlWhere .= "WHERE ";
            }
            $majorColourJoinCount++;
            $this->sqlJoins  .= "JOIN wallpaper_tag_colour mclwt" . $majorColourJoinCount . " ON (mclwt" .
                $majorColourJoinCount . ".wallpaper_id = w.id AND mclwt" . $majorColourJoinCount . ".amount >= 20) ";
            $this->sqlJoins  .= "JOIN wallpaper_tag_colour_similar mclt" . $majorColourJoinCount . " ON (mclwt" .
                $majorColourJoinCount . ".tag_colour = mclt" . $majorColourJoinCount . ".similar_colour) ";
            $this->sqlWhere  .= "mclt" . $majorColourJoinCount . ".colour = ?";
            $this->sqlData[] = $tag;
        }

        foreach ($allSearchTagIds as $key => $id) {
            $this->joinAmount++;
            if ($this->sqlWhere !== "") {
                $this->sqlWhere .= " AND ";
            } else {
                $this->sqlWhere .= "WHERE ";
            }
            $tagJoinCount++;
            $this->sqlJoins  .= "JOIN wallpaper_tag wt" . $tagJoinCount . " ON (wt" . $tagJoinCount .
                ".wallpaper_id = w.id) ";
            $this->sqlWhere  .= "wt" . $tagJoinCount . ".tag_id = ?";
            $this->sqlData[] = $id;
            if (!empty($allSearchTags[$key])) {
                $this->metaTags .= ', ' . $allSearchTags[$key];
                if (!empty($this->pageTitleSearch)) {
                    $this->pageTitleSearch .= ', ';
                }
                $this->pageTitleSearch .= $allSearchTags[$key];
            }
        }

        if (!empty($this->searchTagsCharacter)) {
            $allCharacterSearchTagIds = [];
            $tagInArray               = [];
            $tagIn                    = '';
            foreach ($this->searchTagsCharacter as $tag) {
                if ($tagIn !== '') {
                    $tagIn .= ', ';
                }
                $tagIn        .= '?';
                $tagInArray[] = $tag;
            }
            if ($tagIn !== '') {
                $query  = "SELECT id, `name` FROM tag WHERE `name` IN (" . $tagIn . ")";
                $result = $this->db->query($query, $tagInArray);
                while ($fld = $result->fetch(PDO::FETCH_ASSOC)) {
                    $allCharacterSearchTagIds[mb_strtolower($fld['name'], 'utf-8')] = $fld['id'];
                }
            }

            $characterTags      = $this->searchTagsCharacter;
            $characterTagSearch = '';
            sort($characterTags);
            foreach ($characterTags as $characterTag) {
                if ($characterTagSearch !== '') {
                    $characterTagSearch .= ',';
                }
                if (!empty($allCharacterSearchTagIds[mb_strtolower($characterTag, 'utf-8')])) {
                    $characterTagSearch .= $allCharacterSearchTagIds[mb_strtolower($characterTag, 'utf-8')];
                } else {
                    $characterTagSearch .= '0';
                }
            }
            if ($this->sqlWhere !== "") {
                $this->sqlWhere .= " AND ";
            } else {
                $this->sqlWhere .= "WHERE ";
            }
            $this->sqlWhere  .= "w.chartags = ? ";
            $this->sqlData[] = $characterTagSearch;
        }
    }

    /**
     * Adds tag search to query
     *
     * @return void
     */
    private function loadWallpapersTagAnySearch(): void
    {
        $allSearchTags   = [];
        $allSearchTagIds = [];
        $tagInArray      = [];
        $tagIn           = '';
        foreach ($this->searchTagsAny as $tag) {
            if ($tagIn !== '') {
                $tagIn .= ', ';
            }
            $tagIn        .= '?';
            $tagInArray[] = $tag;
        }
        if ($tagIn !== '') {
            $query  = "SELECT id, `name` FROM tag WHERE `name` IN (" . $tagIn . ")";
            $result = $this->db->query($query, $tagInArray);
            while ($fld = $result->fetch(PDO::FETCH_ASSOC)) {
                $allSearchTags[mb_strtolower($fld['name'], 'utf-8')]   = $fld['name'];
                $allSearchTagIds[mb_strtolower($fld['name'], 'utf-8')] = $fld['id'];
            }
        }

        $allAuthorSearchTags   = [];
        $allAuthorSearchTagIds = [];
        $tagInArray            = [];
        $tagIn                 = '';
        foreach ($this->searchTagsAnyAuthor as $tag) {
            if ($tagIn !== '') {
                $tagIn .= ', ';
            }
            $tagIn        .= '?';
            $tagInArray[] = $tag;
        }
        if ($tagIn !== '') {
            $query  = "SELECT id, `name` FROM tag_artist WHERE `name` IN (" . $tagIn . ")";
            $result = $this->db->query($query, $tagInArray);
            while ($fld = $result->fetch(PDO::FETCH_ASSOC)) {
                $allAuthorSearchTags[mb_strtolower($fld['name'], 'utf-8')]   = $fld['name'];
                $allAuthorSearchTagIds[mb_strtolower($fld['name'], 'utf-8')] = $fld['id'];
            }
        }

        $allPlatformSearchTags   = [];
        $allPlatformSearchTagIds = [];
        $tagInArray              = [];
        $tagIn                   = '';
        foreach ($this->searchTagsAnyPlatform as $tag) {
            if ($tagIn !== '') {
                $tagIn .= ', ';
            }
            $tagIn        .= '?';
            $tagInArray[] = $tag;
        }
        if ($tagIn !== '') {
            $query  = "SELECT id, `name` FROM tag_platform WHERE `name` IN (" . $tagIn . ")";
            $result = $this->db->query($query, $tagInArray);
            while ($fld = $result->fetch(PDO::FETCH_ASSOC)) {
                $allPlatformSearchTags[mb_strtolower($fld['name'], 'utf-8')]   = $fld['name'];
                $allPlatformSearchTagIds[mb_strtolower($fld['name'], 'utf-8')] = $fld['id'];
            }
        }

        $allAspectSearchTags   = [];
        $allAspectSearchTagIds = [];
        $tagInArray            = [];
        $tagIn                 = '';
        foreach ($this->searchTagsAnyAspect as $tag) {
            if ($tagIn !== '') {
                $tagIn .= ', ';
            }
            $tagIn        .= '?';
            $tagInArray[] = $tag;
        }
        if ($tagIn !== '') {
            $query  = "SELECT id, `name` FROM tag_aspect WHERE `name` IN (" . $tagIn . ")";
            $result = $this->db->query($query, $tagInArray);
            while ($fld = $result->fetch(PDO::FETCH_ASSOC)) {
                $allAspectSearchTags[mb_strtolower($fld['name'], 'utf-8')]   = $fld['name'];
                $allAspectSearchTagIds[mb_strtolower($fld['name'], 'utf-8')] = $fld['id'];
            }
        }

        unset($tagData);

        if (!empty($allAuthorSearchTagIds)) {
            $tagIdIn = '';
            foreach ($allAuthorSearchTagIds as $id) {
                if ($tagIdIn !== '') {
                    $tagIdIn .= ', ';
                }
                $tagIdIn         .= '?';
                $this->sqlData[] = $id;
            }
            $this->joinAmount++;
            if ($this->sqlWhere !== "") {
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
                if ($tagIdIn !== '') {
                    $tagIdIn .= ', ';
                }
                $tagIdIn         .= '?';
                $this->sqlData[] = $id;
            }
            $this->joinAmount++;
            if ($this->sqlWhere !== "") {
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
                if ($tagIdIn !== '') {
                    $tagIdIn .= ', ';
                }
                $tagIdIn         .= '?';
                $this->sqlData[] = $id;
            }
            $this->joinAmount++;
            if ($this->sqlWhere !== "") {
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
                if ($tagIdIn !== '') {
                    $tagIdIn .= ', ';
                }
                $tagIdIn         .= '?';
                $this->sqlData[] = $id;
            }
            $this->joinAmount++;
            if ($this->sqlWhere !== "") {
                $this->sqlWhere .= " AND ";
            } else {
                $this->sqlWhere .= "WHERE ";
            }
            $this->sqlJoins .= "JOIN wallpaper_tag wtAny ON (wtAny.wallpaper_id = w.id) ";
            $this->sqlWhere .= "wtAny.tag_id IN (" . $tagIdIn . ")";
        }

        if (!empty($this->searchTagsAnyCharacter)) {
            $allCharacterSearchTagIds = [];
            $tagInArray               = [];
            $tagIn                    = '';
            foreach ($this->searchTagsAnyCharacter as $tag) {
                if ($tagIn !== '') {
                    $tagIn .= ', ';
                }
                $tagIn        .= '?';
                $tagInArray[] = $tag;
            }
            if ($tagIn !== '') {
                $query  = "SELECT id, `name` FROM tag WHERE `name` IN (" . $tagIn . ")";
                $result = $this->db->query($query, $tagInArray);
                while ($fld = $result->fetch(PDO::FETCH_ASSOC)) {
                    $allCharacterSearchTagIds[mb_strtolower($fld['name'], 'utf-8')] = $fld['id'];
                }
            }

            $characterTagCount  = 0;
            $characterTagSearch = '';
            foreach ($this->searchTagsAnyCharacter as $characterTag) {
                if (!empty($allCharacterSearchTagIds[mb_strtolower($characterTag, 'utf-8')])) {
                    if ($characterTagCount > 0) {
                        $characterTagSearch .= " OR ";
                    }
                    $characterTagCount++;
                    $characterTagSearch .= "w.chartags = ?";
                    $this->sqlData[]    = $allCharacterSearchTagIds[mb_strtolower($characterTag, 'utf-8')];
                }
            }
            if ($characterTagCount > 0) {
                if ($this->sqlWhere !== "") {
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
     *
     * @return void
     */
    private function loadWallpapersTagExcludeSearch(): void
    {
        $allSearchTags   = [];
        $allSearchTagIds = [];
        $tagInArray      = [];
        $tagIn           = '';
        foreach ($this->searchTagsExclude as $tag) {
            if ($tagIn !== '') {
                $tagIn .= ', ';
            }
            $tagIn        .= '?';
            $tagInArray[] = $tag;
        }
        if ($tagIn !== '') {
            $query  = "SELECT id, `name` FROM tag WHERE `name` IN (" . $tagIn . ")";
            $result = $this->db->query($query, $tagInArray);
            while ($fld = $result->fetch(PDO::FETCH_ASSOC)) {
                $allSearchTags[mb_strtolower($fld['name'], 'utf-8')]   = $fld['name'];
                $allSearchTagIds[mb_strtolower($fld['name'], 'utf-8')] = $fld['id'];
            }
        }

        $allAuthorSearchTags   = [];
        $allAuthorSearchTagIds = [];
        $tagInArray            = [];
        $tagIn                 = '';
        foreach ($this->searchTagsExcludeAuthor as $tag) {
            if ($tagIn !== '') {
                $tagIn .= ', ';
            }
            $tagIn        .= '?';
            $tagInArray[] = $tag;
        }
        if ($tagIn !== '') {
            $query  = "SELECT id, `name` FROM tag_artist WHERE `name` IN (" . $tagIn . ")";
            $result = $this->db->query($query, $tagInArray);
            while ($fld = $result->fetch(PDO::FETCH_ASSOC)) {
                $allAuthorSearchTags[mb_strtolower($fld['name'], 'utf-8')]   = $fld['name'];
                $allAuthorSearchTagIds[mb_strtolower($fld['name'], 'utf-8')] = $fld['id'];
            }
        }

        $allPlatformSearchTags   = [];
        $allPlatformSearchTagIds = [];
        $tagInArray              = [];
        $tagIn                   = '';
        foreach ($this->searchTagsExcludePlatform as $tag) {
            if ($tagIn !== '') {
                $tagIn .= ', ';
            }
            $tagIn        .= '?';
            $tagInArray[] = $tag;
        }
        if ($tagIn !== '') {
            $query  = "SELECT id, `name` FROM tag_platform WHERE `name` IN (" . $tagIn . ")";
            $result = $this->db->query($query, $tagInArray);
            while ($fld = $result->fetch(PDO::FETCH_ASSOC)) {
                $allPlatformSearchTags[mb_strtolower($fld['name'], 'utf-8')]   = $fld['name'];
                $allPlatformSearchTagIds[mb_strtolower($fld['name'], 'utf-8')] = $fld['id'];
            }
        }

        $allAspectSearchTags   = [];
        $allAspectSearchTagIds = [];
        $tagInArray            = [];
        $tagIn                 = '';
        foreach ($this->searchTagsExcludeAspect as $tag) {
            if ($tagIn !== '') {
                $tagIn .= ', ';
            }
            $tagIn        .= '?';
            $tagInArray[] = $tag;
        }
        if ($tagIn !== '') {
            $query  = "SELECT id, `name` FROM tag_aspect WHERE `name` IN (" . $tagIn . ")";
            $result = $this->db->query($query, $tagInArray);
            while ($fld = $result->fetch(PDO::FETCH_ASSOC)) {
                $allAspectSearchTags[mb_strtolower($fld['name'], 'utf-8')]   = $fld['name'];
                $allAspectSearchTagIds[mb_strtolower($fld['name'], 'utf-8')] = $fld['id'];
            }
        }

        unset($tagData);

        if (!empty($allAuthorSearchTagIds)) {
            $tagIdIn = '';
            foreach ($allAuthorSearchTagIds as $id) {
                if ($tagIdIn !== '') {
                    $tagIdIn .= ', ';
                }
                $tagIdIn         .= '?';
                $this->sqlData[] = $id;
            }
            $this->joinAmount++;
            if ($this->sqlWhere !== "") {
                $this->sqlWhere .= " AND ";
            } else {
                $this->sqlWhere .= "WHERE ";
            }
            $this->sqlJoins .= "LEFT JOIN wallpaper_tag_artist awtExc ON (awtExc.wallpaper_id = w.id 
                AND awtExc.tag_artist_id IN (" . $tagIdIn . ")) ";
            $this->sqlWhere .= "awtExc.id IS NULL";
        }

        if (!empty($allPlatformSearchTagIds)) {
            $tagIdIn = '';
            foreach ($allPlatformSearchTagIds as $id) {
                if ($tagIdIn !== '') {
                    $tagIdIn .= ', ';
                }
                $tagIdIn         .= '?';
                $this->sqlData[] = $id;
            }
            $this->joinAmount++;
            if ($this->sqlWhere !== "") {
                $this->sqlWhere .= " AND ";
            } else {
                $this->sqlWhere .= "WHERE ";
            }
            $this->sqlJoins .= "LEFT JOIN wallpaper_tag_platform pwtExc ON (pwtExc.wallpaper_id = w.id 
                AND pwtExc.tag_platform_id IN (" . $tagIdIn . ")) ";
            $this->sqlWhere .= "pwtExc.id IS NULL";
        }

        if (!empty($allAspectSearchTagIds)) {
            $tagIdIn = '';
            foreach ($allAspectSearchTagIds as $id) {
                if ($tagIdIn !== '') {
                    $tagIdIn .= ', ';
                }
                $tagIdIn         .= '?';
                $this->sqlData[] = $id;
            }
            $this->joinAmount++;
            if ($this->sqlWhere !== "") {
                $this->sqlWhere .= " AND ";
            } else {
                $this->sqlWhere .= "WHERE ";
            }
            $this->sqlJoins .= "LEFT JOIN wallpaper_tag_aspect aswtExc ON (aswtExc.wallpaper_id = w.id 
                AND aswtExc.tag_aspect_id IN (" . $tagIdIn . ")) ";
            $this->sqlWhere .= "aswtExc.id IS NULL";
        }

        // @todo Colour support

        if (!empty($allSearchTagIds)) {
            $tagIdIn = '';
            foreach ($allSearchTagIds as $id) {
                if ($tagIdIn !== '') {
                    $tagIdIn .= ', ';
                }
                $tagIdIn         .= '?';
                $this->sqlData[] = $id;
            }
            $this->joinAmount++;
            if ($this->sqlWhere !== "") {
                $this->sqlWhere .= " AND ";
            } else {
                $this->sqlWhere .= "WHERE ";
            }
            $this->sqlJoins .= "LEFT JOIN wallpaper_tag wtExc ON (wtExc.wallpaper_id = w.id AND wtExc.tag_id IN (" .
                $tagIdIn . ")) ";
            $this->sqlWhere .= "wtExc.id IS NULL";
        }

        if (!empty($this->searchTagsExcludeCharacter)) {
            $allCharacterSearchTagIds = [];
            $tagInArray               = [];
            $tagIn                    = '';
            foreach ($this->searchTagsExcludeCharacter as $tag) {
                if ($tagIn !== '') {
                    $tagIn .= ', ';
                }
                $tagIn        .= '?';
                $tagInArray[] = $tag;
            }
            if ($tagIn !== '') {
                $query  = "SELECT id, `name` FROM tag WHERE `name` IN (" . $tagIn . ")";
                $result = $this->db->query($query, $tagInArray);
                while ($fld = $result->fetch(PDO::FETCH_ASSOC)) {
                    $allCharacterSearchTagIds[mb_strtolower($fld['name'], 'utf-8')] = $fld['id'];
                }
            }

            foreach ($this->searchTagsExcludeCharacter as $characterTag) {
                if (!empty($allCharacterSearchTagIds[mb_strtolower($characterTag, 'utf-8')])) {
                    if ($this->sqlWhere !== "") {
                        $this->sqlWhere .= " AND ";
                    } else {
                        $this->sqlWhere .= "WHERE ";
                    }

                    $this->sqlWhere  .= "w.chartags != ?";
                    $this->sqlData[] = $allCharacterSearchTagIds[mb_strtolower($characterTag, 'utf-8')];
                }
            }
        }
    }

    /**
     * @return Wallpaper[]
     */
    public function getWallpapers(): array
    {
        return $this->wallpapers;
    }

    /**
     * @return int
     */
    public function getWallpaperCount(): int
    {
        return $this->wallpaperSearchCount;
    }

    /**
     * Set current page.
     *
     * @param int $val
     *
     * @return void
     */
    public function setPageNumber(int $val): void
    {
        $this->pageNumber = (int)$val;
    }

    /**
     * @return int
     */
    public function getPageNumber(): int
    {
        return $this->pageNumber;
    }

    /**
     * Set how many wallpapers are displayer per page
     *
     * @param int $val
     *
     * @return void
     */
    public function setWallpapersPerPage(int $val): void
    {
        $this->wallpapersPerPage = (int)$val;
    }

    /**
     * @return int
     */
    public function getWallpapersPerPage(): int
    {
        return $this->wallpapersPerPage;
    }

    /**
     * @return string
     */
    public function getPageTitleAddition(): string
    {
        return (!empty($this->pageTitleAddition) ? $this->pageTitleAddition .
                (!empty($this->pageTitleSearch) ? ' | ' : '') : '') . $this->pageTitleSearch;
    }

    /**
     * @return string
     */
    public function getMetaTags(): string
    {
        return $this->metaTags;
    }

    /**
     * @return string[]
     */
    public function getSearchTagsWithType(): array
    {
        $return = [];
        foreach ($this->searchTagsCharacter as $tag) {
            $return[] = '=' . $tag;
        }
        foreach ($this->searchTags as $tag) {
            $return[] = $tag;
        }
        foreach ($this->searchTagsAspect as $tag) {
            $return[] = 'aspect:' . $tag;
        }
        foreach ($this->searchTagsAuthor as $tag) {
            $return[] = 'author:' . $tag;
        }
        foreach ($this->searchTagsColour as $tag) {
            $return[] = 'colour:' . $tag;
        }
        foreach ($this->searchTagsMajorColour as $tag) {
            $return[] = 'major-colour:' . $tag;
        }
        foreach ($this->searchTagsPlatform as $tag) {
            $return[] = 'platform:' . $tag;
        }

        return $return;
    }

    /**
     * @return string
     */
    public function getRss(): string
    {
        return '<link rel="alternate" type="application/rss+xml" title="RSS" href="' . $this->getRssUrl() . '">';
    }

    /**
     * @return string
     */
    public function getRssUrl(): string
    {
        return 'https://' . $_SERVER['SERVER_NAME'] . PUB_PATH_CAT . 'feed/' . $this->rssSearch;
    }

    /**
     * @return string
     */
    public function getRssSearch(): string
    {
        return $this->rssSearch;
    }

    /**
     * @return string
     */
    public function getJavaScript(): string
    {
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

    /**
     * @return string[]
     */
    public function getJavaScriptFiles(): array
    {
        return ['wallpaper-list-2.0.0-beta-v3.js'];
    }

    /**
     * @return string
     */
    public function getMeta(): string
    {
        $meta = "\n";
        if ($this->displayOrder != self::ORDER_RANDOM) {
            if (!empty($this->wallpapers)) {
                $image1 = '';
                $image2 = '';
                $image3 = '';
                $image4 = '';

                if (count($this->wallpapers) === 1) {
                    $meta .= '		<meta name="twitter:card" content="photo">' . "\n";
                    $meta .= '		<meta name="twitter:image:src" content="https://' . $_SERVER['SERVER_NAME'] .
                        PUB_PATH . 'images/r2_' . $this->wallpapers[0]->getFileId() . '.jpg">' . "\n";
                } else {
                    $meta .= '		<meta name="twitter:card" content="gallery">' . "\n";
                    if (count($this->wallpapers) >= 4) {
                        $image1 = $this->wallpapers[0]->getFileId();
                        $image2 = $this->wallpapers[1]->getFileId();
                        $image3 = $this->wallpapers[2]->getFileId();
                        $image4 = $this->wallpapers[3]->getFileId();
                    } elseif (count($this->wallpapers) === 3) {
                        $image1 = $this->wallpapers[0]->getFileId();
                        $image2 = $this->wallpapers[1]->getFileId();
                        $image3 = $this->wallpapers[2]->getFileId();
                        $image4 = $this->wallpapers[0]->getFileId();
                    } elseif (count($this->wallpapers) === 2) {
                        $image1 = $this->wallpapers[0]->getFileId();
                        $image2 = $this->wallpapers[1]->getFileId();
                        $image3 = $this->wallpapers[1]->getFileId();
                        $image4 = $this->wallpapers[0]->getFileId();
                    }
                    $meta .= '		<meta name="twitter:image0:src" content="http://' . $_SERVER['SERVER_NAME'] .
                        PUB_PATH . 'images/r2_' . $image1 . '.jpg">' . "\n";
                    $meta .= '		<meta name="twitter:image1:src" content="http://' . $_SERVER['SERVER_NAME'] .
                        PUB_PATH . 'images/r2_' . $image2 . '.jpg">' . "\n";
                    $meta .= '		<meta name="twitter:image2:src" content="http://' . $_SERVER['SERVER_NAME'] .
                        PUB_PATH . 'images/r2_' . $image3 . '.jpg">' . "\n";
                    $meta .= '		<meta name="twitter:image3:src" content="http://' . $_SERVER['SERVER_NAME'] .
                        PUB_PATH . 'images/r2_' . $image4 . '.jpg">' . "\n";
                }
            }
        } else {
            $meta .= '		<meta name="twitter:card" content="summary">' . "\n";
            $meta .= '		<meta name="twitter:description" content="Random wallpaper listing.">' . "\n";
        }
        return $meta;
    }

    /**
     * @return string
     */
    public function output(): string
    {
        global $response;
        ob_start();

        $response->getResponseVariables()->rss_search             = $this->getRssSearch();
        $response->getResponseVariables()->large_wallpaper_thumbs = $this->largeWallpaperThumbs;
        $response->getResponseVariables()->wallpaper_count        = $this->wallpaperSearchCount;
        $response->getResponseVariables()->maxJoinAmountExceeded  = $this->maxJoinAmountExceeded;
        $response->getResponseVariables()->wallpapers             = $this->getWallpapers();

        if ($this->customTemplate !== null && file_exists(DOC_DIR . THEME . '/' . $this->customTemplate)) {
            require_once(DOC_DIR . THEME . '/' . $this->customTemplate);
        } elseif ($this->renderWallpapersOnly) {
            require_once(DOC_DIR . THEME . '/wallpaper_list_wallpapers.php');
        } else {
            require_once(DOC_DIR . THEME . '/wallpaper_list.php');
        }
        return ob_get_clean();
    }

    /**
     * Checks whether there are too many search tags or not.
     * @return bool
     */
    private function checkTagCount(): bool
    {
        $count = count($this->searchTags) + count($this->searchTagsAspect) + count($this->searchTagsAuthor)
            + count($this->searchTagsCharacter) + count($this->searchTagsColour)
            + count($this->searchTagsMajorColour) + count($this->searchTagsPlatform);
        return $count < 8;
    }

    /**
     * @return string
     */
    public function getHeaderType(): string
    {
        return 'text/html; charset=utf-8';
    }

    /**
     * @return bool
     */
    public function getIncludeHeaderAndFooter(): bool
    {
        return true;
    }

    /**
     * @param string $pageTitleAddition
     *
     * @return void
     */
    public function setPageTitleAddition(string $pageTitleAddition): void
    {
        $this->pageTitleAddition = $pageTitleAddition;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset ?? (($this->pageNumber - 1) * $this->wallpapersPerPage);
    }

    /**
     * @param int|null $offset
     *
     * @return void
     */
    public function setOffset(?int $offset): void
    {
        $this->offset = (int)$offset;
    }

    /**
     * @return int
     */
    public function getJoinAmount(): int
    {
        return $this->joinAmount;
    }

    /**
     * @return int|null
     */
    public function getSearchFavouritesUserId(): ?int
    {
        return $this->searchFavouritesUserId;
    }

    /**
     * @param int|null $searchFavouritesUserId
     *
     * @return void
     */
    public function setSearchFavouritesUserId(?int $searchFavouritesUserId): void
    {
        if ($searchFavouritesUserId === null) {
            $this->searchFavouritesUserId = null;
        } else {
            $this->searchFavouritesUserId = (int)$searchFavouritesUserId;
        }
    }
}
