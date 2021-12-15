<?php

declare(strict_types=1);

namespace MyLittleWallpaper\classes;

use function strpos;

class Password
{
    /**
     * @param string $plainTextPassword
     * @param string $hash
     * @param string $salt
     *
     * @return bool
     */
    public static function checkPassword(string $plainTextPassword, string $hash, string $salt): bool
    {
        if (password_verify($plainTextPassword, $hash)) {
            return true;
        }
        return hash_equals($hash, self::legacyPasswordHash($plainTextPassword, $salt));
    }

    /**
     * @param string $plainTextPassword
     *
     * @return string
     */
    public static function hashPassword(string $plainTextPassword): string
    {
        return password_hash($plainTextPassword, PASSWORD_ARGON2ID);
    }

    /**
     * @param string $hash
     *
     * @return bool
     */
    public static function doesPasswordNeedRehash(string $hash): bool
    {
        // @todo Check parameters
        return strpos($hash, '$argon2id$') === 0;
    }

    /**
     * @param string $password
     * @param string $salt
     *
     * @return string
     */
    private static function legacyPasswordHash(string $password, string $salt): string
    {
        $saltProcess = '';
        for ($counter = 0, $max = mb_strlen($salt, 'utf-8'); $counter < $max; $counter++) {
            if ($counter % 3 === 0 || $counter % 5 === 0) {
                $saltProcess .= mb_strtolower(mb_substr($salt, $counter, 1, 'utf-8'));
            }
        }
        $data = base64_encode(gzcompress($password . $saltProcess));
        return hash_hmac('whirlpool', $data, HASHKEY);
    }
}
