<?php

// Exception inspections not applicable in test classes
/** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyLittleWallpaperTests\classes;

use MyLittleWallpaper\classes\Exception\InvalidAuthorizationTokenException;
use MyLittleWallpaper\classes\JWT;
use MyLittleWallpaperTests\AbstractUnitTestCase;
use ParagonIE\ConstantTime\Base64;
use ParagonIE\ConstantTime\Base64UrlSafe;

use function json_encode;

use const OPENSSL_RAW_DATA;

class JWTTest extends AbstractUnitTestCase
{
    /**
     * @var string
     */
    private string $key;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->key = Base64::decode(trim(file_get_contents(ROOT_DIR . '.ENCRYPTION_KEY')));
    }

    /**
     * @covers \MyLittleWallpaper\classes\JWT::createAuthorizationToken
     * @covers \MyLittleWallpaper\classes\JWT::getAuthorizationTokenSessionId
     * @return void
     */
    public function testGenerationAndValidation(): void
    {
        $sessionId = uniqid('', true);
        $token = JWT::createAuthorizationToken($sessionId);
        self::assertSame($sessionId, JWT::getAuthorizationTokenSessionId($token));
    }

    /**
     * @dataProvider provideFailureClaims
     * @param array $claimsArray
     *
     * @covers \MyLittleWallpaper\classes\JWT::getAuthorizationTokenSessionId
     * @return void
     */
    public function testValidationFailures(array $claimsArray): void
    {
        $header = json_encode(['alg' => 'dir', 'enc' => 'A256GCM'], JSON_THROW_ON_ERROR);
        $claims = json_encode($claimsArray, JSON_THROW_ON_ERROR);

        $iv         = random_bytes(12);
        $cipherText = openssl_encrypt(
            $claims,
            'aes-256-gcm',
            $this->key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            Base64UrlSafe::encode($header)
        );

        $token = Base64UrlSafe::encode($header) . '..' . Base64UrlSafe::encode($iv);
        $token .= '.' . Base64UrlSafe::encode($cipherText);
        $token .= '.' . Base64UrlSafe::encode($tag);

        $this->expectException(InvalidAuthorizationTokenException::class);
        JWT::getAuthorizationTokenSessionId($token);
        $this->expectNotToPerformAssertions();
    }

    /**
     * @return array
     */
    public function provideFailureClaims(): array
    {
        return [
            'noIssuedAt' => [
                [
                    'exp'        => strtotime('+14 days'),
                    'iss'        => 'MyLittleWallpaper',
                    'aud'        => 'CookieToken',
                    'session_id' => 'b86acd4d-9264-40da-b0a2-0cd058cbf576'
                ]
            ],
            'issuedAtInFuture' => [
                [
                    'iat'        => time() + (3600 * 900),
                    'exp'        => strtotime('+14 days'),
                    'iss'        => 'MyLittleWallpaper',
                    'aud'        => 'CookieToken',
                    'session_id' => 'b86acd4d-9264-40da-b0a2-0cd058cbf576'
                ]
            ],
            'noExpiration' => [
                [
                    'iat'        => time(),
                    'iss'        => 'MyLittleWallpaper',
                    'aud'        => 'CookieToken',
                    'session_id' => 'b86acd4d-9264-40da-b0a2-0cd058cbf576'
                ]
            ],
            'expirationInPast' => [
                [
                    'iat'        => time(),
                    'exp'        => strtotime('-1 minute'),
                    'iss'        => 'MyLittleWallpaper',
                    'aud'        => 'CookieToken',
                    'session_id' => 'b86acd4d-9264-40da-b0a2-0cd058cbf576'
                ]
            ],
            'noIssuer' => [
                [
                    'iat'        => time(),
                    'exp'        => strtotime('+14 days'),
                    'aud'        => 'CookieToken',
                    'session_id' => 'b86acd4d-9264-40da-b0a2-0cd058cbf576'
                ]
            ],
            'incorrectIssuer' => [
                [
                    'iat'        => time(),
                    'exp'        => strtotime('+14 days'),
                    'iss'        => 'Incorrect',
                    'aud'        => 'CookieToken',
                    'session_id' => 'b86acd4d-9264-40da-b0a2-0cd058cbf576'
                ]
            ],
            'noAudience' => [
                [
                    'iat'        => time(),
                    'exp'        => strtotime('+14 days'),
                    'iss'        => 'MyLittleWallpaper',
                    'session_id' => 'b86acd4d-9264-40da-b0a2-0cd058cbf576'
                ]
            ],
            'incorrectAudience' => [
                [
                    'iat'        => time(),
                    'exp'        => strtotime('+14 days'),
                    'iss'        => 'MyLittleWallpaper',
                    'aud'        => 'Incorrect',
                    'session_id' => 'b86acd4d-9264-40da-b0a2-0cd058cbf576'
                ]
            ],
            'noSessionId' => [
                [
                    'iat' => time(),
                    'exp' => strtotime('+14 days'),
                    'iss' => 'MyLittleWallpaper',
                    'aud' => 'CookieToken',
                ]
            ],
        ];
    }
}
