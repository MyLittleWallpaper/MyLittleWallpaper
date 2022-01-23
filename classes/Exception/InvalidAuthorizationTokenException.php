<?php

declare(strict_types=1);

namespace MyLittleWallpaper\classes\Exception;

use Exception;

final class InvalidAuthorizationTokenException extends Exception
{
    /**
     * @return self
     */
    public static function create(): self
    {
        return new self();
    }
}
