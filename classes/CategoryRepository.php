<?php
/**
 * @author Petri Haikonen <sharkmachine@ecxol.net>
 * @package MyLittleWallpaper
 * @subpackage Classes
 */

// Check that correct entry point was used
if (!defined('INDEX')) exit();

/**
 * Category Repository class.
 * Used for loading categories
 */
class CategoryRepository {
	/**
	 * @var Database
	 */
	private $db;

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
	 * @return Category[]
	 */
	public function getCategoryList() {
		$return = array();
		$sql = "SELECT * FROM category ORDER BY name";
		$res = $this->db->query($sql);
		while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
			$return[(int) $row['id']] = new Category($row, $this->db);
		}
		return $return;
	}
	
	/**
	 * @param string $urlName
	 * @return Category|null
	 */
	public function getCategoryByUrlName($urlName) {
		$sql = "SELECT * FROM category WHERE urlname = ?";
		$data = array((string) $urlName);
		
		return $this->selectCategory($sql, $data);
	}
	
	/**
	 * @param int $id
	 * @return Category|null
	 */
	public function getCategoryById($id) {
		$sql = "SELECT * FROM category WHERE id = ?";
		$data = array((int) $id);
		
		return $this->selectCategory($sql, $data);
	}
	
	/**
	 * @param string $sql
	 * @param array $data
	 * @return Category|null
	 */
	private function selectCategory($sql, $data) {
		$category = null;
		$res = $this->db->query($sql, $data);
		while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
			$category = $row;
		}
		
		if ($category !== null) {
			return new Category($category, $this->db);
		} else {
			return null;
		}
	}
}

/**
 * Category object.
 */
class Category {
	/**
	 * @var int
	 */
	private $id;
	
	/**
	 * @var string
	 */
	private $name;
	
	/**
	 * @var string
	 */
	private $urlName;
	
	/**
	 * @var string
	 */
	private $footerDescription;
	
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
	 * @param int $val
	 */
	public function setId($val) {
		$this->id = (int) $val;
	}
	
	/**
	 * @return int|null
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * @param string $val
	 */
	public function setName($val) {
		$this->name = (string) $val;
	}
	
	/**
	 * @return string|null
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * @param string $val
	 */
	public function setUrlName($val) {
		$this->urlName = (string) $val;
	}
	
	/**
	 * @return string|null
	 */
	public function getUrlName() {
		return $this->urlName;
	}
	
	/**
	 * @param string $val
	 */
	public function setFooterDescription($val) {
		$this->footerDescription = (string) $val;
	}
	
	/**
	 * @return string|null
	 */
	public function getFooterDescription() {
		return $this->footerDescription;
	}
	
	/**
	 * @param array $data category data
	 */
	public function bindData($data) {
		if (!empty($data['id']) && filter_var($data['id'], FILTER_VALIDATE_INT) !== FALSE) {
			$this->id = (int) $data['id'];
		}
		if (!empty($data['name'])) {
			$this->name = (string) $data['name'];
		}
		if (!empty($data['urlname'])) {
			$this->urlName = (string) $data['urlname'];
		}
		if (!empty($data['footerdescription'])) {
			$this->footerDescription = (string) $data['footerdescription'];
		}
	}
}