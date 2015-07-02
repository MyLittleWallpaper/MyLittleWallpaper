<?php
/**
 * @author Petri Haikonen <sharkmachine@ecxol.net>
 * @package MyLittleWallpaper
 * @subpackage Classes
 */

// Check that correct entry point was used
if (!defined('INDEX')) {
	exit();
}

/**
 * User repository class.
 * Used for loading users.
 */
class UserRepository {
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
	 * @param int $user_id
	 * @return User|null
	 * @throws PDOException
	 */
	public function getUserById($user_id) {
		$user = $this->db->getRecord('user', Array('field' => 'id', 'value' => $user_id));
		if (!empty($user['id'])) {
			return new User($user);
		}
		return null;
	}

	/**
	 * @param string $username
	 * @param string $password
	 * @return User|null
	 * @throws PDOException
	 */
	public function getUserByUsernameAndPassword($username, $password) {
		$user = null;
		$result = $this->db->query("SELECT * FROM user WHERE username = ? AND password = ? LIMIT 1", array($username, Format::passwordHash($password, $username)));
		while ($userRow = $result->fetch(PDO::FETCH_ASSOC)) {
			$user = new User($userRow);
		}
		return $user;
	}
}

/**
 * User class.
 */
class User {
	/**
	 * @var int
	 */
	private $id = 0;

	/**
	 * @var string
	 */
	private $username = '';

	/**
	 * @var string
	 */
	private $email = '';

	/**
	 * @var string|null
	 */
	private $token = null;

	/**
	 * @var bool[]|null
	 */
	private $permissions = null;

	/**
	 * @var bool[]|null
	 */
	private $virtualPermissions = null;

	/**
	 * @var bool
	 */
	private $isAdmin = false;

	/**
	 * @var bool
	 */
	private $isBanned = false;

	/**
	 * @var bool
	 */
	private $isAnonymous = true;

	/**
	 * @var string
	 */
	private $passwordHash = '';

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
			$this->bind($data);
		}
	}

	/**
	 * @param array $data
	 */
	public function bind($data) {
		if (!empty($data['id']) && filter_var($data['id'], FILTER_VALIDATE_INT) !== FALSE) {
			$this->id = (int) $data['id'];
			$this->setIsAnonymous(false);
		}
		if (!empty($data['username'])) {
			$this->username = (string) $data['username'];
		}
		if (isset($data['admin'])) {
			$this->isAdmin = ($data['admin'] == '1' ? true : false);
		}
		if (isset($data['banned'])) {
			$this->isBanned = ($data['banned'] == '1' ? true : false);
		}
		if (!empty($data['password'])) {
			$this->passwordHash = (string) $data['password'];
		}
		if (!empty($data['email'])) {
			$this->email = (string) $data['email'];
		}
		if (isset($data['token'])) {
			if (empty($data['token'])) {
				$this->token = null;
			} else {
				$this->token = (string) $data['token'];
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
	public function getUsername() {
		return $this->username;
	}

	/**
	 * @return bool
	 */
	public function getIsAdmin() {
		return $this->isAdmin;
	}

	/**
	 * @return string
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * @return string|null
	 */
	public function getToken() {
		return $this->token;
	}

	/**
	 * @return bool
	 */
	public function getIsAnonymous() {
		return $this->isAnonymous;
	}

	/**
	 * @return string
	 */
	public function getPasswordHash() {
		return $this->passwordHash;
	}

	/**
	 * @return boolean
	 */
	public function getIsBanned() {
		return $this->isBanned;
	}

	/**
	 * @param int $id
	 */
	public function setId($id) {
		if (empty($id)) {
			$this->id = 0;
		} else {
			$this->id = (int) $id;
		}
	}

	/**
	 * @param string $val
	 */
	public function setUsername($val) {
		$this->username = (string) $val;
	}

	/**
	 * @param bool $val
	 */
	public function setIsAdmin($val) {
		$this->isAdmin = (bool) $val;
	}

	/**
	 * @param string $email
	 */
	public function setEmail($email) {
		$this->email = (string) $email;
	}

	/**
	 * @param string|null $token
	 */
	public function setToken($token) {
		if ($token === null) {
			$this->token = null;
		} else {
			$this->token = (string) $token;
		}
	}

	/**
	 * @param bool $isAnonymous
	 */
	public function setIsAnonymous($isAnonymous) {
		$this->isAnonymous = (bool) $isAnonymous;
	}

	/**
	 * @param string $passwordHash
	 */
	public function setPasswordHash($passwordHash) {
		$this->passwordHash = (string) $passwordHash;
	}

	/**
	 * @param boolean $isBanned
	 */
	public function setIsBanned($isBanned) {
		$this->isBanned = $isBanned;
	}


	/**
	 * @param string $val
	 * @return bool
	 */
	public function getPermission($val) {
		if ($this->permissions === null) {
			$ban = $this->db->getRecord('ban', Array('field' => 'ip', 'value' => USER_IP));
			if (!empty($ban['ip']) && $ban['ip'] == USER_IP)
				$this->isBanned = true;

			$this->loadVirtualPermissions();
			$this->loadPermissions();
		}
		if (isset($this->virtualPermissions[$val])) {
			return $this->virtualPermissions[$val];
		} else {
			if ($this->isAdmin)
				return true;
			if (!empty($this->permissions[$val]))
				return true;
		}
		return false;
	}

	/**
	 * Loads permissions
	 */
	private function loadPermissions() {
		// Don't even bother checking permissions from database if user is banned
		if (!$this->isBanned) {
			// Do SQL magic, not needed before actual permissions are added
		}
	}

	/**
	 * Loads virtual built in permissions such as login, viewing profile page, etc.
	 */
	private function loadVirtualPermissions() {
		if (!$this->isBanned) {
			$this->virtualPermissions['submit'] = true;
			$this->virtualPermissions['feedback'] = true;
			if ($this->id === null) {
				$this->virtualPermissions['login'] = true;
				$this->virtualPermissions['register'] = true;
				$this->virtualPermissions['profile'] = false;
				$this->virtualPermissions['hide_captcha'] = false;
			} else {
				$this->virtualPermissions['login'] = false;
				$this->virtualPermissions['register'] = false;
				$this->virtualPermissions['profile'] = true;
				$this->virtualPermissions['hide_captcha'] = true;
			}
		} else {
			$this->virtualPermissions['submit'] = false;
			$this->virtualPermissions['feedback'] = false;
			$this->virtualPermissions['login'] = false;
			$this->virtualPermissions['register'] = false;
			$this->virtualPermissions['profile'] = false;
			$this->virtualPermissions['hide_captcha'] = false;
		}
	}
}