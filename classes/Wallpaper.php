<?php
/**
 * @author Petri Haikonen <sharkmachine@ecxol.net>
 * @package MyLittleWallpaper
 * @subpackage Classes
 */

// Check that correct entry point was used
if (!defined('INDEX')) exit();

require_once(ROOT_DIR . 'classes/Tag.php');
require_once(ROOT_DIR . 'classes/Colours.php');

/**
 * Wallpaper class.
 */
class Wallpaper {
	/**
	 * @var int|null
	 */
	private $id = null;
	
	/**
	 * @var string
	 */
	private $name = '';
	
	/**
	 * @var string
	 */
	private $url = '';

	/**
	 * @var string
	 */
	private $directDownloadLink = '';
	
	/**
	 * @var string
	 */
	private $filename = '';
	
	/**
	 * @var string
	 */
	private $fileId = '';
	
	/**
	 * @var int
	 */
	private $width = 0;
	
	/**
	 * @var int
	 */
	private $height = 0;
	
	/**
	 * Unix timestamp
	 * @var int
	 */
	private $timeAdded = 0;
	
	/**
	 * @var string
	 */
	private $mime = '';

	/**
	 * @var int
	 */
	private $clicks = 0;
	
	/**
	 * @var int
	 */
	private $favourites = 0;
	
	/**
	 * @var bool
	 */
	private $hasAspect = true;
	
	/**
	 * @var bool
	 */
	private $hasResolution = true;
	
	/**
	 * @var Tag[]
	 */
	private $tags = array();
	
	/**
	 * @var TagAuthor[]
	 */
	private $authorTags = array();
	
	/**
	 * @var TagAspect[]
	 */
	private $aspectTags = array();

	/**
	 * @var TagPlatform[]
	 */
	private $platformTags = array();
	
	/**
	 * @var TagColour[]
	 */
	private $colourTags = array();
	
	/**
	 * @var Database
	 */
	private $db;
	
	/**
	 * @param array|null $data
	 * @param Database|null $db If null, looks for $GLOBALS['db']
	 * @throws Exception if database not found
	 */
	public function __construct($data = null, &$db = null) {
		if (!($db instanceof Database)) {
			if (!isset($GLOBALS['db']) || !($GLOBALS['db'] instanceof Database)) {
				throw new Exception('No database connection found');
			} else {
				$this->db =& $GLOBALS['db'];
			}
		} else {
			$this->db = $db;
		}
		if (!empty($data) && is_array($data)) {
			$this->bindData($data);
		}
	}

	/**
	 * @param int $id
	 * @return bool
	 */
	public function bindDataById($id) {
		$result = $this->db->query("SELECT * FROM wallpaper WHERE id = ? LIMIT 1", array($id));
		$return = false;
		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$this->bindData($row);
			$return = true;
		}
		return $return;
	}
	
	/**
	 * @param array $data wallpaper data
	 */
	public function bindData($data) {
		if (!empty($data['id']) && filter_var($data['id'], FILTER_VALIDATE_INT) !== FALSE) {
			$this->id = (int) $data['id'];
		}
		if (!empty($data['name'])) {
			$this->name = (string) $data['name'];
		}
		if (!empty($data['url'])) {
			$this->url = (string) $data['url'];
		}
		if (!empty($data['filename'])) {
			$this->filename = (string) $data['filename'];
		}
		if (!empty($data['file'])) {
			$this->fileId = (string) $data['file'];
		}
		if (!empty($data['width']) && filter_var($data['width'], FILTER_VALIDATE_INT) !== FALSE) {
			$this->width = (int) $data['width'];
		}
		if (!empty($data['height']) && filter_var($data['height'], FILTER_VALIDATE_INT) !== FALSE) {
			$this->height = (int) $data['height'];
		}
		if (!empty($data['mime'])) {
			$this->mime = (string) $data['mime'];
		}
		if (!empty($data['timeadded']) && filter_var($data['timeadded'], FILTER_VALIDATE_INT) !== FALSE) {
			$this->timeAdded = (int) $data['timeadded'];
		}
		if (!empty($data['clicks']) && filter_var($data['clicks'], FILTER_VALIDATE_INT) !== FALSE) {
			$this->clicks = (int) $data['clicks'];
		}
		if (!empty($data['favs']) && filter_var($data['favs'], FILTER_VALIDATE_INT) !== FALSE) {
			$this->favourites = (int) $data['favs'];
		}
		if (!empty($data['no_aspect'])) {
			$this->hasAspect = false;
		}
		if (!empty($data['no_resolution'])) {
			$this->hasResolution = false;
		}
		if (!empty($data['direct_with_link'])) {
			$this->directDownloadLink = PROTOCOL . SITE_DOMAIN . PUB_PATH . 'c/' . CATEGORY . '/download/' . $this->fileId;
		} else {
			$this->directDownloadLink = $this->url;
		}
		$this->loadTags();
	}
	
	/**
	 * Loads wallpaper tags.
	 */
	public function loadTags() {
		if ($this->id === null) {
			return;
		}
		
		// Author tags
		$sql = "SELECT t.id, t.name, t.oldname FROM tag_artist t "
				. "JOIN wallpaper_tag_artist wt ON (wt.tag_artist_id = t.id) "
				. "WHERE wt.wallpaper_id = ? AND t.deleted = 0 "
				. "ORDER BY t.name";
		$result = $this->db->query($sql, array($this->id));
		while ($tag = $result->fetch(PDO::FETCH_ASSOC)) {
			$this->authorTags[] = new TagAuthor($tag);
		}

		// Tags
		$sql = "SELECT t.id, t.name, t.alternate, t.type FROM tag t "
				. "JOIN wallpaper_tag wt ON (wt.tag_id = t.id) "
				. "WHERE wt.wallpaper_id = ? "
				. "ORDER BY t.name";
		$result = $this->db->query($sql, array($this->id));
		while ($tag = $result->fetch(PDO::FETCH_ASSOC)) {
			$this->tags[] = new Tag($tag);
		}

		// Platform tags
		$sql = "SELECT t.id, t.name FROM tag_platform t "
				. "JOIN wallpaper_tag_platform wt ON (wt.tag_platform_id = t.id) "
				. "WHERE wt.wallpaper_id = ? "
				. "ORDER BY t.name";
		$result = $this->db->query($sql, array($this->id));
		while ($tag = $result->fetch(PDO::FETCH_ASSOC)) {
			$this->platformTags[] = new TagPlatform($tag);
		}

		// Aspect tags
		if ($this->hasAspect) {
			$sql = "SELECT t.id, t.name FROM tag_aspect t "
					. "JOIN wallpaper_tag_aspect wt ON (wt.tag_aspect_id = t.id) "
					. "WHERE wt.wallpaper_id = ? "
					. "ORDER BY t.name";
			$result = $this->db->query($sql, array($this->id));
			while ($tag = $result->fetch(PDO::FETCH_ASSOC)) {
				$this->aspectTags[] = new TagAspect($tag);
			}
		}
	}
	
	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * @return string
	 */
	public function getUrl() {
		return $this->url;
	}

	/**
	 * @return string
	 */
	public function getDirectDownloadLink() {
		return $this->directDownloadLink;
	}

	/**
	 * @return string
	 */
	public function getDownloadLink() {
		return PROTOCOL . SITE_DOMAIN . PUB_PATH . 'c/' . CATEGORY . '/link/' . $this->fileId;
	}

	/**
	 * @param int $type (1-3)
	 * @return string
	 */
	public function getImageThumbnailLink($type = 1) {
		return PROTOCOL . SITE_DOMAIN . PUB_PATH . 'images/r' . (string) $type . '_' . $this->fileId . '.jpg';
	}

	/**
	 * @return string
	 */
	public function getImageLink() {
		return PROTOCOL . SITE_DOMAIN . PUB_PATH . 'images/o_' . $this->fileId . '.' . Format::fileExtension($this->filename);
	}

	/**
	 * @return string
	 */
	public function getFilename() {
		return $this->filename;
	}
	
	/**
	 * @return string
	 */
	public function getFileId() {
		return $this->fileId;
	}
	
	/**
	 * @return int
	 */
	public function getWidth() {
		return $this->width;
	}
	
	/**
	 * @return int
	 */
	public function getHeight() {
		return $this->height;
	}
	
	/**
	 * @return string
	 */
	public function getSize() {
		return $this->width.'x'.$this->height;
	}
	
	/**
	 * @return string
	 */
	public function getMime() {
		return $this->mime;
	}
	
	/**
	 * Unix timestamp.
	 * @return int
	 */
	public function getTimeAdded() {
		return $this->timeAdded;
	}
	
	/**
	 * @return int
	 */
	public function getClicks() {
		return $this->clicks;
	}
	
	/**
	 * @return int
	 */
	public function getFavourites() {
		return $this->favourites;
	}
	
	/**
	 * @return bool
	 */
	public function getHasAspect() {
		return $this->hasAspect;
	}
	
	/**
	 * @return bool
	 */
	public function getHasResolution() {
		return $this->hasResolution;
	}
	
	/**
	 * @return Tag[]
	 */
	public function getBasicTags() {
		return $this->tags;
	}
	
	/**
	 * @return TagAspect[]
	 */
	public function getAspectTags() {
		return $this->aspectTags;
	}
	
	/**
	 * @return TagAuthor[]
	 */
	public function getAuthorTags() {
		return $this->authorTags;
	}
	
	/**
	 * @return TagPlatform[]
	 */
	public function getPlatformTags() {
		return $this->platformTags;
	}
	
	/**
	 * @return TagColour[]
	 */
	public function getColourTags() {
		if (empty($this->colourTags) && $this->id !== null) {
			$this->loadColours();
		}
		return $this->colourTags;
	}
	
	/**
	 * @return TagColour[]
	 */
	public function getMajorColourTags() {
		if (empty($this->colourTags) && $this->id !== null) {
			$this->loadColours();
		}
		$return = array();
		if (!empty($this->colourTags)) {
			foreach($this->colourTags as $colour) {
				if ($colour->getAmount() >= 20) {
					$return[] = $colour;
				}
			}
		}
		return $return;
	}
	
	/**
	 * @param string $val
	 */
	public function setName($val) {
		$this->name = (string) $val;
	}
	
	/**
	 * @param string $val
	 * @throws Exception if invalid URL given
	 */
	public function setUrl($val) {
		if ($val !== '') {
			if (filter_var($val, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED) !== false) {
				$this->url = $val;
			} else {
				throw new Exception('Invalid URL');
			}
		} else {
			$this->url = $val;
		}
	}

	/**
	 * @param string $directDownloadLink
	 */
	public function setDirectDownloadLink($directDownloadLink) {
		$this->directDownloadLink = (string) $directDownloadLink;
	}

	/**
	 * @param string $val
	 */
	public function setFilename($val) {
		$this->filename = (string) $val;
	}

	/**
	 * @param string|null $val If null given, will be generated.
	 */
	public function setFile($val = null) {
		if ($val === null) {
			$this->fileId = uniqid('', TRUE);
		} else {
			$this->fileId = (string) $val;
		}
	}
	
	/**
	 * @param int $val
	 */
	public function setWidth($val) {
		if (filter_var($val, FILTER_VALIDATE_INT) !== false) {
			$this->width = (int) $val;
		}
	}
	
	/**
	 * @param int $val
	 */
	public function setHeight($val) {
		if (filter_var($val, FILTER_VALIDATE_INT) !== false) {
			$this->height = (int) $val;
		}
	}
	
	/**
	 * @param string $val
	 */
	public function setMime($val) {
		$this->mime = $val;
	}
	
	/**
	 * @param bool $val
	 */
	public function setHasAspect($val) {
		$this->hasAspect = (bool) $val;
	}
	
	/**
	 * @param bool $val
	 */
	public function setHasResolution($val) {
		$this->hasResolution = (bool) $val;
	}
	
	/**
	 * @param Tag $tag
	 */
	public function addBasicTag($tag) {
		if ($tag instanceof Tag) {
			$this->tags[] = $tag;
		}
	}
	
	/**
	 * @param TagAspect $tag
	 */
	public function addAspectTag($tag) {
		if ($tag instanceof TagAspect) {
			$this->aspectTags[] = $tag;
		}
	}
	
	/**
	 * @param TagAuthor $tag
	 */
	public function addAuthorTag($tag) {
		if ($tag instanceof TagAuthor) {
			$this->authorTags[] = $tag;
		}
	}
	
	/**
	 * @param TagPlatform $tag
	 */
	public function addPlatformTag($tag) {
		if ($tag instanceof TagPlatform) {
			$this->platformTags[] = $tag;
		}
	}
	
	/**
	 * @param string $colour
	 * @param float $amount
	 */
	public function addColourTag($colour, $amount) {
		if (is_numeric($amount) && $amount >= 0 && $amount <= 100) {
			$sql = "SELECT colour FROM wallpaper_tag_colour_similar WHERE similar_colour = ? LIMIT 1";
			$result = $this->db->query($sql, array($colour));
			while($row = $result->fetch(PDO::FETCH_ASSOC)) {
				$this->colourTags[] = new TagColour($row['colour'], $amount, $colour);
			}
		}
	}
	
	/**
	 * @param Tag|string|int $tag Tag class, tag name or tag id
	 */
	public function removeBasicTag($tag) {
		if ($tag instanceof Tag) {
			foreach($this->tags as $key => $existing) {
				if ($existing->getId() === $tag->getId()) {
					unset($this->tags[$key]);
				}
			}
		} elseif (is_int($tag)) {
			foreach($this->tags as $key => $existing) {
				if ($existing->getId() === (int) $tag) {
					unset($this->tags[$key]);
				}
			}
		} else {
			foreach($this->tags as $key => $existing) {
				if ($existing->getName() === $tag) {
					unset($this->tags[$key]);
				}
			}
		}
	}
	
	/**
	 * @param TagAspect|string|int $tag TagAspect class, tag name or tag id
	 */
	public function removeAspectTag($tag) {
		if ($tag instanceof TagAspect) {
			foreach($this->aspectTags as $key => $existing) {
				if ($existing->getId() === $tag->getId()) {
					unset($this->aspectTags[$key]);
				}
			}
		} elseif (is_int($tag)) {
			foreach($this->aspectTags as $key => $existing) {
				if ($existing->getId() === (int) $tag) {
					unset($this->aspectTags[$key]);
				}
			}
		} else {
			foreach($this->aspectTags as $key => $existing) {
				if ($existing->getName() === $tag) {
					unset($this->aspectTags[$key]);
				}
			}
		}
	}
	
	/**
	 * @param TagAuthor|string|int $tag TagAuthor class, tag name or tag id
	 */
	public function removeAuthorTag($tag) {
		if ($tag instanceof TagAuthor) {
			foreach($this->authorTags as $key => $existing) {
				if ($existing->getId() === $tag->getId()) {
					unset($this->authorTags[$key]);
				}
			}
		} elseif (is_int($tag)) {
			foreach($this->authorTags as $key => $existing) {
				if ($existing->getId() === (int) $tag) {
					unset($this->authorTags[$key]);
				}
			}
		} else {
			foreach($this->authorTags as $key => $existing) {
				if ($existing->getName() === $tag) {
					unset($this->authorTags[$key]);
				}
			}
		}
	}
	
	/**
	 * @param TagPlatform|string|int $tag TagAuthor class, tag name or tag id
	 */
	public function removePlatformTag($tag) {
		if ($tag instanceof TagPlatform) {
			foreach($this->platformTags as $key => $existing) {
				if ($existing->getId() === $tag->getId()) {
					unset($this->authorTags[$key]);
				}
			}
		} elseif (is_int($tag)) {
			foreach($this->platformTags as $key => $existing) {
				if ($existing->getId() === (int) $tag) {
					unset($this->authorTags[$key]);
				}
			}
		} else {
			foreach($this->platformTags as $key => $existing) {
				if ($existing->getName() === $tag) {
					unset($this->authorTags[$key]);
				}
			}
		}
	}
	
	/**
	 * @param string $colour
	 */
	public function removeColourTag($colour) {
		$tempColour = new TagColour($colour);
		foreach($this->colourTags as $key => $colour_class) {
			if ($tempColour->getSimilarColourHex() === $colour_class->getSimilarColourHex()) {
				unset($this->colourTags[$key]);
			}
		}
	}

	/**
	 * @param int $userId
	 * @return bool
	 */
	public function getIsFavourite($userId) {
		if (!empty($this->id)) {
			$sql = "SELECT wallpaper_id FROM wallpaper_fav WHERE wallpaper_id = ? AND user_id = ?";
			$result = $this->db->query($sql, array($this->id, $userId));
			while($favData = $result->fetch(PDO::FETCH_ASSOC)) {
				if ((int) $favData['wallpaper_id'] == $this->id) {
					return true;
				}
			}
		}
		return false;
	}

	private function loadColours() {
		if (empty($this->colourTags) && $this->id !== null) {
			$sql = "SELECT clt.colour, clwt.amount, clwt.tag_colour similar_colour FROM wallpaper_tag_colour clwt "
					. "JOIN wallpaper_tag_colour_similar clt ON (clwt.tag_colour = clt.similar_colour) "
					. "WHERE clwt.wallpaper_id = ? "
					. "ORDER BY clwt.amount DESC, clt.colour ASC";
			$result = $this->db->query($sql, array($this->id));
			while($colour = $result->fetch(PDO::FETCH_ASSOC)) {
				$this->colourTags[] = new TagColour($colour['colour'], $colour['amount'], $colour['similar_colour']);
			}
		}
	}
}