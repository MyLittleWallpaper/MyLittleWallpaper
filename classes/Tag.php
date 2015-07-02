<?php
/**
 * @author Petri Haikonen <sharkmachine@ecxol.net>
 * @package MyLittleWallpaper
 * @subpackage Classes
 */

// Check that correct entry point was used
if (!defined('INDEX'))
	exit();

/**
 * Basic tag class.
 * This class is only used as a base for different tag types.
 */
class TagBase {
	/**
	 * @var int|null
	 */
	protected $id = null;

	/**
	 * @var string
	 */
	protected $name = '';

	/**
	 * @var Database
	 */
	protected $db;

	/**
	 * @var string
	 */
	protected $tableName = '';

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
	 * @param array $data tag data
	 */
	public function bindData($data) {
		if (!empty($data['id']) && filter_var($data['id'], FILTER_VALIDATE_INT) !== FALSE) {
			$this->id = (int) $data['id'];
		}
		if (!empty($data['name'])) {
			$this->name = (string) $data['name'];
		}
	}

	/**
	 * @param int $id
	 */
	public function loadById($id) {
		if ($this->tableName !== '') {
			$query = "SELECT ";
			$query .= "* FROM `" . $this->tableName . "` WHERE `id` = ? LIMIT 1";
			$result = $this->db->query($query, array($id));
			while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
				$this->bindData($row);
			}
		}
	}

	/**
	 * @param string $name
	 */
	public function loadByName($name) {
		if ($this->tableName !== '') {
			$query = "SELECT ";
			$query .= "* FROM `" . $this->tableName . "` WHERE `name` = ? ORDER BY `name` LIMIT 1";
			$result = $this->db->query($query, array($name));
			while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
				$this->bindData($row);
			}
		}
	}

	/**
	 * @return int|null
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
	 * @param string $val
	 */
	public function setName($val) {
		$this->name = (string) $val;
	}

	/**
	 * Saves the tag to database.
	 */
	public function save() {
		if ($this->tableName !== '') {
			$saveData = array(
				'name' => $this->name
			);
			if ($this->id === null) {
				$this->db->saveArray($this->tableName, $saveData);
			} else {
				$this->db->saveArray($this->tableName, $saveData, $this->id);
			}
		}
	}
}

/**
 * Normal tag class.
 * Tag can be type of Tag::TAG_TYPE_CHARACTER, Tag::TAG_TYPE_GENERAL or Tag::TAG_TYPE_STYLE
 */
class Tag extends TagBase {
	const TAG_TYPE_CHARACTER = 'character';
	const TAG_TYPE_GENERAL = 'general';
	const TAG_TYPE_STYLE = 'style';

	/**
	 * @var string
	 */
	private $alternate = '';

	/**
	 * @var string
	 */
	private $type = self::TAG_TYPE_GENERAL;

	/**
	 * @var string
	 */
	protected $tableName = 'tag';

	/**
	 * @param array $data tag data
	 */
	public function bindData($data) {
		parent::bindData($data);
		if (!empty($data['alternate'])) {
			$this->alternate = (string) $data['alternate'];
		}
		if (!empty($data['type'])) {
			$this->setType($data['type']);
		}
	}

	/**
	 * @return string
	 */
	public function getAlternate() {
		return $this->alternate;
	}

	/**
	 * Returns self::TAG_TYPE_*
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @param string $val
	 */
	public function setAlternate($val) {
		$this->alternate = (string) $val;
	}

	/**
	 * @param string $val use self::TAG_TYPE_*
	 */
	public function setType($val) {
		switch ($val) {
			case self::TAG_TYPE_CHARACTER:
			case self::TAG_TYPE_GENERAL:
			case self::TAG_TYPE_STYLE:
				$this->type = $val;
		}
	}

	/**
	 * Saves the tag to database.
	 */
	public function save() {
		if ($this->tableName !== '') {
			$saveData = array(
				'name' => $this->name,
				'alternate' => $this->alternate,
				'type' => $this->type
			);
			if ($this->id === null) {
				$this->db->saveArray($this->tableName, $saveData);
			} else {
				$this->db->saveArray($this->tableName, $saveData, $this->id);
			}
		}
	}
}

/**
 * Author tag class.
 */
class TagAuthor extends TagBase {
	/**
	 * @var string
	 */
	private $oldName = '';

	/**
	 * @var bool
	 */
	private $isDeleted = false;

	/**
	 * @var string
	 */
	protected $tableName = 'tag_artist';

	/**
	 * @param array $data tag data
	 */
	public function bindData($data) {
		parent::bindData($data);
		if (!empty($data['oldname'])) {
			$this->oldName = (string) $data['oldname'];
		}
		if (!empty($data['deleted'])) {
			$this->isDeleted = true;
		}
	}

	/**
	 * @return string
	 */
	public function getOldName() {
		return $this->oldName;
	}

	/**
	 * @return bool
	 */
	public function getIsDeleted() {
		return $this->isDeleted;
	}

	/**
	 * @param string $val
	 */
	public function setOldName($val) {
		$this->oldName = (string) $val;
	}

	/**
	 * @param bool $val
	 */
	public function setIsDeleted($val) {
		$this->isDeleted = (bool) $val;
	}

	/**
	 * Saves the tag to database.
	 */
	public function save() {
		if ($this->tableName !== '') {
			$saveData = array(
				'name' => $this->name,
				'oldname' => $this->oldName,
				'deleted' => ($this->isDeleted ? 1 : 0)
			);
			if ($this->id === null) {
				$this->db->saveArray($this->tableName, $saveData);
			} else {
				$this->db->saveArray($this->tableName, $saveData, $this->id);
			}
		}
	}
}

/**
 * Aspect ratio tag class.
 */
class TagAspect extends TagBase {
	/**
	 * @var string
	 */
	protected $tableName = 'tag_aspect';
}

/**
 * Platform tag class.
 */
class TagPlatform extends TagBase {
	/**
	 * @var string
	 */
	protected $tableName = 'tag_platform';
}

/**
 * Colour tag class.
 */
class TagColour {
	/**
	 * @var string
	 */
	private $colour = '';

	/**
	 * @var string
	 */
	private $similarColour = '';

	/**
	 * @var float
	 */
	private $amount = 0;

	/**
	 * @param string|null $colour
	 * @param float|null $amount
	 * @param string|null $similar_colour
	 */
	public function __construct($colour = null, $amount = null, $similar_colour = null) {
		if ($colour !== null) {
			$this->setColourHex($colour);
			if ($amount !== null) {
				$this->setAmount($amount);
			}
			if ($similar_colour !== null) {
				$this->setSimilarColourHex($similar_colour);
			} else {
				$this->setSimilarColourHex($colour);
			}
		}
	}

	/**
	 * Returns false if colour isn't set.
	 * @return string|bool
	 */
	public function getColourHex() {
		if ($this->colour == '') {
			return false;
		}
		return $this->colour;
	}

	/**
	 * Returns false if colour isn't set.
	 * @return string|bool
	 */
	public function getSimilarColourHex() {
		if ($this->similarColour == '') {
			return false;
		}
		return $this->similarColour;
	}

	/**
	 * @return float
	 */
	public function getAmount() {
		return $this->amount;
	}

	/**
	 * @param string $colour
	 * @return bool
	 */
	public function setColourHex($colour) {
		if (preg_match("/^#{0,1}[0-9a-f]{6}$/", strtolower($colour))) {
			if (strpos($colour, '#') === true) {
				$colour = substr($colour, 1);
			}
			$this->colour = strtolower($colour);
			return true;
		} elseif (preg_match("/^#{0,1}[0-9a-f]{3}$/", strtolower($colour))) {
			if (strpos($colour, '#') === true) {
				$colour = substr($colour, 1);
			}
			$this->colour = str_repeat(substr($colour, 0, 1), 2) . str_repeat(substr($colour, 1, 1), 2) . str_repeat(substr($colour, 2, 1), 2);
			return true;
		}
		return false;
	}

	/**
	 * @param string $colour
	 * @return bool
	 */
	public function setSimilarColourHex($colour) {
		if (preg_match("/^#{0,1}[0-9a-f]{6}$/", strtolower($colour))) {
			if (strpos($colour, '#') === true) {
				$colour = substr($colour, 1);
			}
			$this->similarColour = strtolower($colour);
			return true;
		} elseif (preg_match("/^#{0,1}[0-9a-f]{3}$/", strtolower($colour))) {
			if (strpos($colour, '#') === true) {
				$colour = substr($colour, 1);
			}
			$this->similarColour = str_repeat(substr($colour, 0, 1), 2) . str_repeat(substr($colour, 1, 1), 2) . str_repeat(substr($colour, 2, 1), 2);
			return true;
		}
		return false;
	}

	/**
	 * @param float $amount
	 */
	public function setAmount($amount) {
		if (is_numeric($amount) && $amount >= 0 && $amount <= 100) {
			$this->amount = (float) $amount;
		}
	}
}