<?php

declare(strict_types=1);

namespace MyLittleWallpaper\classes;

class Cookie
{
    /**
     * @var bool|null
     */
    private static ?bool $isSecure = null;

    /**
     * @param string $name
     * @param string $value
     * @param int    $expires
     *
     * @return void
     */
    public static function setCookie(string $name, string $value, int $expires): void
    {
        if (self::getIsSecure()) {
            $name = '__Host-' . $name;
        }
        setcookie($name, $value, self::getCookieParams($expires));
    }

    /**
     * @param string $name
     *
     * @return string|null
     */
    public static function getCookie(string $name): ?string
    {
        if (self::getIsSecure()) {
            $name = '__Host-' . $name;
        }
        return $_COOKIE[$name] ?? null;
    }

    /**
     * @param int $expires
     *
     * @return array
     */
    private static function getCookieParams(int $expires): array
    {
        return [
            'expires'  => $expires,
            'secure'   => self::getIsSecure(),
            'httponly' => true,
            'path'     => '/',
            'samesite' => 'Strict'
        ];
    }

    /**
     * @return bool
     */
    private static function getIsSecure(): bool
    {
        if (null === self::$isSecure) {
            self::$isSecure = (!empty($_SERVER['HTTPS']) && 'off' !== $_SERVER['HTTPS']);
        }
        return self::$isSecure;
    }
}
