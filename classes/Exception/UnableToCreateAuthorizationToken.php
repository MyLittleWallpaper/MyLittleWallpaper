<?php

declare(strict_types=1);

namespace MyLittleWallpaper\classes\Exception;

use Exception;

use function sprintf;

final class UnableToCreateAuthorizationToken extends Exception
{
    private const MESSAGE = 'Unable to create authorization token: %s';

    /**
     * @param Exception $previous
     *
     * @return self
     */
    public static function createFromException(Exception $previous): self
    {
        return new self(sprintf(self::MESSAGE, $previous->getMessage()), 0, $previous);
    }
}
