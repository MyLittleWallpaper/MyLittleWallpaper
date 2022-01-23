<?php

declare(strict_types=1);

namespace MyLittleWallpaper\classes;

use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\TokenStorage\ClearableTokenStorageInterface;
use Symfony\Component\Security\Csrf\TokenStorage\NativeSessionTokenStorage;

class CSRF
{
    /**
     * @var CsrfTokenManagerInterface|null
     */
    private static ?CsrfTokenManagerInterface $csrfTokenManager = null;

    /**
     * @var ClearableTokenStorageInterface|null
     */
    private static ?ClearableTokenStorageInterface $csrfTokenStorage = null;

    /**
     * @param string $tokenId
     *
     * @return string
     */
    public static function getToken(string $tokenId): string
    {
        return self::getCsrfTokenManager()->refreshToken($tokenId)->getValue();
    }

    /**
     * @param string $tokenId
     *
     * @return void
     */
    public static function removeToken(string $tokenId): void
    {
        self::getCsrfTokenManager()->removeToken($tokenId);
    }

    /**
     * @return void
     */
    public static function clearTokens(): void
    {
        self::getCsrfTokenStorage()->clear();
    }

    /**
     * @param string $tokenId
     * @param string $value
     *
     * @return bool
     */
    public static function isTokenValid(string $tokenId, string $value): bool
    {
        return self::getCsrfTokenManager()->isTokenValid(new CsrfToken($tokenId, $value));
    }

    /**
     * @return CsrfTokenManagerInterface
     */
    private static function getCsrfTokenManager(): CsrfTokenManagerInterface
    {
        if (self::$csrfTokenManager === null) {
            self::$csrfTokenManager = new CsrfTokenManager(null, self::getCsrfTokenStorage());
        }
        return self::$csrfTokenManager;
    }

    /**
     * @param CsrfTokenManagerInterface $csrfTokenManager
     *
     * @return void
     */
    public static function setCsrfTokenManager(CsrfTokenManagerInterface $csrfTokenManager): void
    {
        self::$csrfTokenManager = $csrfTokenManager;
    }

    /**
     * @return ClearableTokenStorageInterface
     */
    private static function getCsrfTokenStorage(): ClearableTokenStorageInterface
    {
        if (self::$csrfTokenStorage === null) {
            self::$csrfTokenStorage = new NativeSessionTokenStorage();
        }
        return self::$csrfTokenStorage;
    }

    /**
     * @param ClearableTokenStorageInterface|null $csrfTokenStorage
     *
     * @return void
     */
    public static function setCsrfTokenStorage(?ClearableTokenStorageInterface $csrfTokenStorage): void
    {
        self::$csrfTokenStorage = $csrfTokenStorage;
    }
}
