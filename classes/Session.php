<?php

declare(strict_types=1);

namespace MyLittleWallpaper\classes;

use Exception;
use Memcache;
use MyLittleWallpaper\classes\User\User;
use MyLittleWallpaper\classes\User\UserRepository;
use PDO;

/**
 * Session class.
 * Used for loading and saving session information.
 */
class Session
{
    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;

    /**
     * @var Database
     */
    private Database $db;

    /**
     * @var Memcache
     */
    private Memcache $memcache;

    /**
     * @param Database|null $db       If null, looks for $GLOBALS['db']
     * @param Memcache|null $memcache If null, looks for $GLOBALS['memcache']
     */
    public function __construct(?Database $db = null, ?Memcache $memcache = null)
    {
        if (!($db instanceof Database)) {
            if (!isset($GLOBALS['db']) || !($GLOBALS['db'] instanceof Database)) {
                throw new Exception('No database connection found');
            }

            $this->db =& $GLOBALS['db'];
        } else {
            $this->db = $db;
        }
        if (!($memcache instanceof Memcache)) {
            if (!isset($GLOBALS['memcache']) || !($GLOBALS['memcache'] instanceof Memcache)) {
                throw new Exception('Memcache is missing');
            }

            $this->memcache =& $GLOBALS['memcache'];
        } else {
            $this->memcache = $memcache;
        }

        $this->userRepository = new UserRepository($this->db);
    }

    /**
     * Loads currently logged in user or generic user class for anonymous access.
     * @return User
     */
    public function loadUser(): User
    {
        $this->removeOldSessionData();
        $user_id = $this->getSessionUserId();

        if ($user_id > 0) {
            $user = $this->userRepository->getUserById($user_id);
            if ($user !== null) {
                return $user;
            }
        }

        // Not logged in, return empty/anonymous user
        return new User();
    }

    /**
     * @return void
     */
    public function logUserOut(): void
    {
        if ($this->getSessionUserId() > 0) {
            $session_id   = $_SESSION[SESSIONPREFIX . '_session_id'];
            $memcache_key = 'session_' . $session_id . '_' . USER_IP;
            $this->db->query("DELETE FROM user_session WHERE id = ? AND ip = ?", [$session_id, USER_IP]);
            $this->memcache->set($memcache_key, 0, 0, 3600 * 30);
        }
    }

    /**
     * @param int $userId
     *
     * @return void
     */
    public function logUserIn(int $userId): void
    {
        if ($this->getSessionUserId() == 0) {
            $session_id   = $_SESSION[SESSIONPREFIX . '_session_id'];
            $memcache_key = 'session_' . $session_id . '_' . USER_IP;
            $this->memcache->set($memcache_key, (int)$userId, 0, 3600 * 30);
            $this->db->saveArray('user_session', ['id' => $session_id, 'ip' => USER_IP, 'time' => time()]);
        }
    }

    /**
     * @return int
     */
    private function getSessionUserId(): int
    {
        if (empty($_SESSION[SESSIONPREFIX . '_session_id'])) {
            $session_id = $_SESSION[SESSIONPREFIX . '_session_id'] = uid();
        } else {
            $session_id = $_SESSION[SESSIONPREFIX . '_session_id'];
        }
        $memcache_key = 'session_' . $session_id . '_' . USER_IP;
        $userId       = $this->memcache->get($memcache_key);
        if ($userId !== false) {
            return (int)$userId;
        }

        $result = $this->db->query("SELECT user_id FROM user_session WHERE id = ? AND ip = ?", [$session_id, USER_IP]);
        while ($session_data = $result->fetch(PDO::FETCH_ASSOC)) {
            $this->memcache->set($memcache_key, (int)$session_data['user_id'], 0, 3600 * 30);
            return (int)$session_data['user_id'];
        }

        $this->memcache->set($memcache_key, 0, 0, 3600 * 30);
        return 0;
    }

    /**
     * Removes session data that is older than a month from database.
     *
     * @return void
     */
    private function removeOldSessionData(): void
    {
        $this->db->query("DELETE FROM user_session WHERE time < ?", [strtotime('-30 days')]);
    }
}
