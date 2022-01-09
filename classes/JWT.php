<?php

declare(strict_types=1);

namespace MyLittleWallpaper\classes;

use Exception;
use Jose\Component\Checker\AudienceChecker;
use Jose\Component\Checker\ClaimCheckerManager;
use Jose\Component\Checker\ExpirationTimeChecker;
use Jose\Component\Checker\InvalidClaimException;
use Jose\Component\Checker\IssuedAtChecker;
use Jose\Component\Checker\IssuerChecker;
use Jose\Component\Checker\MissingMandatoryClaimException;
use Jose\Component\Checker\NotBeforeChecker;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWK;
use Jose\Component\Encryption\Algorithm\ContentEncryption\A256GCM;
use Jose\Component\Encryption\Algorithm\KeyEncryption\Dir;
use Jose\Component\Encryption\Compression\CompressionMethodManager;
use Jose\Component\Encryption\Compression\Deflate;
use Jose\Component\Encryption\JWEBuilder;
use Jose\Component\Encryption\JWEDecrypter;
use Jose\Component\Encryption\Serializer\CompactSerializer;
use Jose\Component\Encryption\Serializer\JWESerializerManager;
use JsonException;
use MyLittleWallpaper\classes\Exception\InvalidAuthorizationTokenException;
use MyLittleWallpaper\classes\Exception\UnableToCreateAuthorizationToken;
use ParagonIE\ConstantTime\Base64;
use ParagonIE\ConstantTime\Base64UrlSafe;

use function json_encode;

class JWT
{
    /**
     * @var string|null
     */
    private static ?string $key = null;

    /**
     * @var JWEBuilder|null
     */
    private static ?JWEBuilder $jweBuilder = null;

    /**
     * @var JWEDecrypter|null
     */
    private static ?JWEDecrypter $jweDecrypter = null;

    /**
     * @param string $sessionId
     *
     * @return string
     * @throws UnableToCreateAuthorizationToken
     */
    public static function createAuthorizationToken(string $sessionId): string
    {
        try {
            $jwk     = new JWK(['kty' => 'oct', 'k' => self::getKey()]);
            $payload = json_encode(
                [
                    'iat'        => time(),
                    'exp'        => strtotime('+14 days'),
                    'iss'        => 'MyLittleWallpaper',
                    'aud'        => 'CookieToken',
                    'session_id' => $sessionId
                ],
                JSON_THROW_ON_ERROR
            );

            $jwe = self::getJweBuilder()
                ->create()
                ->withPayload($payload)
                ->withSharedProtectedHeader(['alg' => 'dir', 'enc' => 'A256GCM'])
                ->addRecipient($jwk)
                ->build();
            return (new CompactSerializer())->serialize($jwe);
        } catch (Exception $ex) {
            throw UnableToCreateAuthorizationToken::createFromException($ex);
        }
    }

    /**
     * @param string $token
     *
     * @return string
     * @throws InvalidAuthorizationTokenException
     */
    public static function getAuthorizationTokenSessionId(string $token): string
    {
        $jwk = new JWK(['kty' => 'oct', 'k' => self::getKey()]);
        $serializerManager = new JWESerializerManager([new CompactSerializer()]);
        $jwe = $serializerManager->unserialize($token);
        if (!$jwe->isEncrypted()) {
            throw InvalidAuthorizationTokenException::create();
        }
        if (!self::getJweDecrypter()->decryptUsingKey($jwe, $jwk, 0)) {
            throw InvalidAuthorizationTokenException::create();
        }
        try {
            $claims = json_decode($jwe->getPayload(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $ex) {
            throw InvalidAuthorizationTokenException::create();
        }
        $claimCheckerManager = new ClaimCheckerManager(
            [
                new IssuedAtChecker(),
                new NotBeforeChecker(),
                new ExpirationTimeChecker(),
                new IssuerChecker(['MyLittleWallpaper']),
                new AudienceChecker('CookieToken'),
            ]
        );
        try {
            $claimCheckerManager->check($claims, ['iat', 'exp', 'iss', 'aud', 'session_id']);
        } catch (InvalidClaimException | MissingMandatoryClaimException $ex) {
            throw InvalidAuthorizationTokenException::create();
        }

        return $claims['session_id'];
    }

    /**
     * @return string
     */
    private static function getKey(): string
    {
        if (self::$key === null) {
            self::$key = Base64UrlSafe::encode(
                Base64::decode(trim(file_get_contents(ROOT_DIR . '.ENCRYPTION_KEY')))
            );
        }
        return self::$key;
    }

    /**
     * @return JWEBuilder
     */
    private static function getJweBuilder(): JWEBuilder
    {
        if (self::$jweBuilder === null) {
            self::$jweBuilder = new JWEBuilder(
                new AlgorithmManager([new Dir()]),
                new AlgorithmManager([new A256GCM()]),
                new CompressionMethodManager([new Deflate()])
            );
        }
        return self::$jweBuilder;
    }

    /**
     * @param JWEBuilder $jweBuilder
     *
     * @return void
     */
    public static function setJweBuilder(JWEBuilder $jweBuilder): void
    {
        self::$jweBuilder = $jweBuilder;
    }

    /**
     * @return JWEDecrypter
     */
    private static function getJweDecrypter(): JWEDecrypter
    {
        if (self::$jweDecrypter === null) {
            self::$jweDecrypter = new JWEDecrypter(
                new AlgorithmManager([new Dir()]),
                new AlgorithmManager([new A256GCM()]),
                new CompressionMethodManager([new Deflate()])
            );
        }
        return self::$jweDecrypter;
    }
}
