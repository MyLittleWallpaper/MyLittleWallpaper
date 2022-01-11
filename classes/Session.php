<?php

declare(strict_types=1);

namespace MyLittleWallpaper\classes;

use Exception;
use Memcache;
use MyLittleWallpaper\classes\Exception\InvalidParametersException;
use MyLittleWallpaper\classes\Exception\UnableToCreateAuthorizationToken;
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
     * @var string
     */
    private static string $cacheLimiter = 'nocache';

    /**
     * @var int|null
     */
    private static ?int $cacheExpire = null;

    /**
     * @param Database|null $db       If null, looks for $GLOBALS['db']
     * @param Memcache|null $memcache If null, looks for $GLOBALS['memcache']
     *
     * @throws Exception
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
     * Loads currently logged-in user or generic user class for anonymous access.
     * @return User
     * @throws InvalidParametersException
     * @throws UnableToCreateAuthorizationToken
     */
    public function loadUser(): User
    {
        $this->removeOldSessionData();
        $userId = $this->getSessionUserId();

        if ($userId > 0) {
            $user = $this->userRepository->getUserById($userId);
            if ($user !== null) {
                return $user;
            }
        }

        // Not logged in, return empty/anonymous user
        return new User();
    }

    /**
     * @return void
     * @throws InvalidParametersException
     * @throws UnableToCreateAuthorizationToken
     */
    public function logUserOut(): void
    {
        if ($this->getSessionUserId() > 0) {
            $sessionId   = $_SESSION[($_ENV['SESSIONPREFIX'] ?? '') . '_session_id'];
            $memcacheKey = 'session_' . $sessionId . '_' . USER_IP;
            $this->db->query("DELETE FROM user_session WHERE id = ?", [$sessionId]);
            $this->memcache->set($memcacheKey, 0, 0, 3600 * 30);
            Cookie::removeCookie('mlwpjwt');
            session_regenerate_id();
            $_SESSION[($_ENV['SESSIONPREFIX'] ?? '') . '_session_id'] = uid();
        }
    }

    /**
     * @param int $userId
     *
     * @return void
     * @throws InvalidParametersException
     * @throws UnableToCreateAuthorizationToken
     */
    public function logUserIn(int $userId): void
    {
        if ($this->getSessionUserId() === 0) {
            $sessionId   = $_SESSION[($_ENV['SESSIONPREFIX'] ?? '') . '_session_id'];
            $memcacheKey = 'session_' . $sessionId . '_' . USER_IP;
            $this->memcache->set($memcacheKey, $userId, 0, 3600 * 30);
            $this->db->saveArray(
                'user_session',
                ['id' => $sessionId, 'ip' => USER_IP, 'time' => time(), 'user_id' => $userId]
            );
            session_regenerate_id();
            try {
                // Set cookie for keeping user logged in
                Cookie::setCookie(
                    'mlwpjwt',
                    JWT::createAuthorizationToken($sessionId),
                    time() + (3600 * 24 * 14)
                );
            } catch (Exception $ex) {
                // phpcs:disable Squiz.PHP.DiscouragedFunctions.Discouraged
                /** @noinspection ForgottenDebugOutputInspection */
                error_log((string)$ex);
                // phpcse:enable
            }
        }
    }

    /**
     * @param string $cacheLimiter
     *
     * @return void
     */
    public static function setCacheLimiter(string $cacheLimiter): void
    {
        self::$cacheLimiter = $cacheLimiter;
    }

    /**
     * @param int|null $cacheExpire
     *
     * @return void
     */
    public static function setCacheExpire(?int $cacheExpire): void
    {
        self::$cacheExpire = $cacheExpire;
    }

    /**
     * @return void
     */
    public static function startSession(): void
    {
        $secure = (!empty($_SERVER['HTTPS']) && 'off' !== $_SERVER['HTTPS']);
        if ($secure) {
            session_name('__Host-' . session_name());
        }
        session_set_cookie_params(
            [
                'lifetime' => 0,
                'secure'   => $secure,
                'httponly' => true,
                'path'     => '/',
                'samesite' => 'Strict'
            ]
        );
        session_cache_limiter(self::$cacheLimiter);
        if (null !== self::$cacheExpire) {
            session_cache_expire(self::$cacheExpire);
        }

        session_start();
    }

    /**
     * @return int
     * @throws InvalidParametersException
     * @throws UnableToCreateAuthorizationToken
     */
    private function getSessionUserId(): int
    {
        if (empty($_SESSION[($_ENV['SESSIONPREFIX'] ?? '') . '_session_id'])) {
            $sessionIdFromCookie = $this->getSessionIdFromCookieToken();
            if ($sessionIdFromCookie === null) {
                $sessionId = $_SESSION[($_ENV['SESSIONPREFIX'] ?? '') . '_session_id'] = uid();
            } else {
                $sessionId = $sessionIdFromCookie;
                $_SESSION[($_ENV['SESSIONPREFIX'] ?? '') . '_session_id'] = $sessionId;
                // Regenerate cookie
                Cookie::setCookie(
                    'mlwpjwt',
                    JWT::createAuthorizationToken($sessionId),
                    time() + (3600 * 24 * 14)
                );
            }
        } else {
            $sessionId = $_SESSION[($_ENV['SESSIONPREFIX'] ?? '') . '_session_id'];
        }
        $memcacheKey = 'session_' . $sessionId . '_' . USER_IP;
        $userId       = $this->memcache->get($memcacheKey);
        if ($userId !== false) {
            return (int)$userId;
        }

        $result = $this->db->query("SELECT user_id FROM user_session WHERE id = ?", [$sessionId]);
        if (($sessionData = $result->fetch(PDO::FETCH_ASSOC)) !== false) {
            $this->memcache->set($memcacheKey, (int)$sessionData['user_id'], 0, 3600 * 30);
            $this->db->saveArray(
                'user_session',
                ['ip' => USER_IP, 'time' => time()],
                $sessionId
            );
            return (int)$sessionData['user_id'];
        }

        $this->memcache->set($memcacheKey, 0, 0, 3600 * 30);
        return 0;
    }

    /**
     * @return string|null
     */
    private function getSessionIdFromCookieToken(): ?string
    {
        $token = Cookie::getCookie('mlwpjwt');
        if ($token === null) {
            return null;
        }
        try {
            return JWT::getAuthorizationTokenSessionId($token);
        } catch (Exception $ex) {
            return null;
        }
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
