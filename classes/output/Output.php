<?php

declare(strict_types=1);

namespace MyLittleWallpaper\classes\output;

/**
 * Abstract class for output classes used by Response class.
 */
abstract class Output
{
    /**
     * @return string
     */
    abstract public function output(): string;

    /**
     * @return string
     */
    abstract public function getHeaderType(): string;

    /**
     * @return bool
     */
    abstract public function getIncludeHeaderAndFooter(): bool;
}