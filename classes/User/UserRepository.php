<?php

declare(strict_types=1);

namespace MyLittleWallpaper\classes\User;

use Exception;
use MyLittleWallpaper\classes\Database;
use MyLittleWallpaper\classes\Password;
use PDO;
use PDOException;

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
    public function getUserById(int $user_id): ?User
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
    public function getUserByUsernameAndPassword(string $username, string $password): ?User
    {
        $user   = null;
        $result = $this->db->query(
            "SELECT * FROM `user` WHERE username = ? LIMIT 1",
            [$username]
        );
        if (($userRow = $result->fetch(PDO::FETCH_ASSOC)) !== false) {
            if (Password::checkPassword($password, $userRow['password'], $username)) {
                $user = new User($userRow);
            }
            if ($user === null) {
                return null;
            }
            if (Password::doesPasswordNeedRehash($userRow['password'])) {
                $this->updatePassword($userRow['id'], $password);
            }
            return null;
        }
        return null;
    }

    /**
     * @param int    $userId
     * @param string $password
     *
     * @return void
     */
    private function updatePassword(int $userId, string $password): void
    {
        $this->db->query('UPDATE `user` SET password = ? WHERE id = ?', [Password::hashPassword($password), $userId]);
    }
}
