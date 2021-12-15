<?php

/**
 * User repository class.
 * Used for loading users.
 */
class UserRepository
{
    /**
     * @var Database
     */
    private Database $db;

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
     * @param int $user_id
     *
     * @return User|null
     * @throws PDOException
     */
    public function getUserById(int $user_id): ?\User
    {
        $user = $this->db->getRecord('user', ['field' => 'id', 'value' => $user_id]);
        if (!empty($user['id'])) {
            return new User($user);
        }
        return null;
    }

    /**
     * @param string $username
     * @param string $password
     *
     * @return User|null
     * @throws PDOException
     */
    public function getUserByUsernameAndPassword(string $username, string $password): ?\User
    {
        $user   = null;
        $result = $this->db->query(
            "SELECT * FROM user WHERE username = ? AND password = ? LIMIT 1",
            [$username, Format::passwordHash($password, $username)]
        );
        while ($userRow = $result->fetch(PDO::FETCH_ASSOC)) {
            $user = new User($userRow);
        }
        return $user;
    }
}

/**
 * User class.
 */
class User
{
    /**
     * @var int
     */
    private int $id = 0;

    /**
     * @var string
     */
    private string $username = '';

    /**
     * @var string
     */
    private string $email = '';

    /**
     * @var string|null
     */
    private ?string $token = null;

    /**
     * @var bool[]|null
     */
    private ?array $permissions = null;

    /**
     * @var bool[]|null
     */
    private ?array $virtualPermissions = null;

    /**
     * @var bool
     */
    private bool $isAdmin = false;

    /**
     * @var bool
     */
    private bool $isBanned = false;

    /**
     * @var bool
     */
    private bool $isAnonymous = true;

    /**
     * @var string
     */
    private string $passwordHash = '';

    /**
     * @var Database
     */
    private Database $db;

    /**
     * @param array|null    $data
     * @param Database|null $db If null, looks for $GLOBALS['db']
     */
    public function __construct(?array $data = null, ?Database $db = null)
    {
        if (!($db instanceof Database)) {
            if (!isset($GLOBALS['db']) || !($GLOBALS['db'] instanceof Database)) {
                throw new Exception('No database connection found');
            }

            $this->db =& $GLOBALS['db'];
        } else {
            $this->db = $db;
        }
        if (!empty($data) && is_array($data)) {
            $this->bind($data);
        }
    }

    /**
     * @param array $data
     *
     * @return void
     */
    public function bind(array $data): void
    {
        if (!empty($data['id']) && filter_var($data['id'], FILTER_VALIDATE_INT) !== false) {
            $this->id = (int)$data['id'];
            $this->setIsAnonymous(false);
        }
        if (!empty($data['username'])) {
            $this->username = (string)$data['username'];
        }
        if (isset($data['admin'])) {
            $this->isAdmin = $data['admin'] == '1';
        }
        if (isset($data['banned'])) {
            $this->isBanned = $data['banned'] == '1';
        }
        if (!empty($data['password'])) {
            $this->passwordHash = (string)$data['password'];
        }
        if (!empty($data['email'])) {
            $this->email = (string)$data['email'];
        }
        if (isset($data['token'])) {
            if (empty($data['token'])) {
                $this->token = null;
            } else {
                $this->token = (string)$data['token'];
            }
        }
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return bool
     */
    public function getIsAdmin(): bool
    {
        return $this->isAdmin;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string|null
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * @return bool
     */
    public function getIsAnonymous(): bool
    {
        return $this->isAnonymous;
    }

    /**
     * @return string
     */
    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    /**
     * @return bool
     */
    public function getIsBanned(): bool
    {
        return $this->isBanned;
    }

    /**
     * @param int $id
     *
     * @return void
     */
    public function setId(int $id): void
    {
        if (empty($id)) {
            $this->id = 0;
        } else {
            $this->id = (int)$id;
        }
    }

    /**
     * @param string $val
     *
     * @return void
     */
    public function setUsername(string $val): void
    {
        $this->username = (string)$val;
    }

    /**
     * @param bool $val
     *
     * @return void
     */
    public function setIsAdmin(bool $val): void
    {
        $this->isAdmin = (bool)$val;
    }

    /**
     * @param string $email
     *
     * @return void
     */
    public function setEmail(string $email): void
    {
        $this->email = (string)$email;
    }

    /**
     * @param string|null $token
     *
     * @return void
     */
    public function setToken(?string $token): void
    {
        if ($token === null) {
            $this->token = null;
        } else {
            $this->token = (string)$token;
        }
    }

    /**
     * @param bool $isAnonymous
     *
     * @return void
     */
    public function setIsAnonymous(bool $isAnonymous): void
    {
        $this->isAnonymous = (bool)$isAnonymous;
    }

    /**
     * @param string $passwordHash
     *
     * @return void
     */
    public function setPasswordHash(string $passwordHash): void
    {
        $this->passwordHash = (string)$passwordHash;
    }

    /**
     * @param bool $isBanned
     *
     * @return void
     */
    public function setIsBanned(bool $isBanned): void
    {
        $this->isBanned = $isBanned;
    }


    /**
     * @param string $val
     *
     * @return bool
     */
    public function getPermission(string $val): bool
    {
        if ($this->permissions === null) {
            $ban = $this->db->getRecord('ban', ['field' => 'ip', 'value' => USER_IP]);
            if (!empty($ban['ip']) && $ban['ip'] == USER_IP) {
                $this->isBanned = true;
            }

            $this->loadVirtualPermissions();
            $this->loadPermissions();
        }
        if (isset($this->virtualPermissions[$val])) {
            return $this->virtualPermissions[$val];
        }

        if ($this->isAdmin) {
            return true;
        }
        if (!empty($this->permissions[$val])) {
            return true;
        }
        return false;
    }

    /**
     * Loads permissions
     *
     * @return void
     */
    private function loadPermissions(): void
    {
        // Don't even bother checking permissions from database if user is banned
        if (!$this->isBanned) {
            // Do SQL magic, not needed before actual permissions are added
        }
    }

    /**
     * Loads virtual built in permissions such as login, viewing profile page, etc.
     *
     * @return void
     */
    private function loadVirtualPermissions(): void
    {
        if (!$this->isBanned) {
            $this->virtualPermissions['submit']   = true;
            $this->virtualPermissions['feedback'] = true;
            if ($this->id === null) {
                $this->virtualPermissions['login']        = true;
                $this->virtualPermissions['register']     = true;
                $this->virtualPermissions['profile']      = false;
                $this->virtualPermissions['hide_captcha'] = false;
            } else {
                $this->virtualPermissions['login']        = false;
                $this->virtualPermissions['register']     = false;
                $this->virtualPermissions['profile']      = true;
                $this->virtualPermissions['hide_captcha'] = true;
            }
        } else {
            $this->virtualPermissions['submit']       = false;
            $this->virtualPermissions['feedback']     = false;
            $this->virtualPermissions['login']        = false;
            $this->virtualPermissions['register']     = false;
            $this->virtualPermissions['profile']      = false;
            $this->virtualPermissions['hide_captcha'] = false;
        }
    }
}