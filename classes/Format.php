<?php

declare(strict_types=1);

namespace MyLittleWallpaper\classes;

/**
 * Format class.
 * Used for formatting user input etc.
 */
class Format
{
    public const QUOTE_TYPES_SINGLE = 1;
    public const QUOTE_TYPES_DOUBLE = 2;
    public const QUOTE_TYPES_BOTH = 3;

    /**
     * @param string $string
     *
     * @return string
     */
    public static function htmlEntities(string $string): string
    {
        return htmlentities($string, ENT_QUOTES, 'utf-8');
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public static function xmlEntities(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES | ENT_XML1, 'utf-8');
    }

    /**
     * @param string $string
     * @param int    $quoteTypes see Format::QUOTE_TYPES_*
     *
     * @return string
     */
    public static function escapeQuotes(string $string, int $quoteTypes = self::QUOTE_TYPES_SINGLE): string
    {
        $replace = ['\\\\'];
        $pattern = ['\\'];
        if ($quoteTypes === self::QUOTE_TYPES_SINGLE || $quoteTypes === self::QUOTE_TYPES_BOTH) {
            $replace[] = '\'';
            $pattern[] = '\\\'';
        }
        if ($quoteTypes === self::QUOTE_TYPES_DOUBLE || $quoteTypes === self::QUOTE_TYPES_BOTH) {
            $replace[] = '"';
            $pattern[] = '\"';
        }
        return str_replace($pattern, $replace, $string);
    }

    /**
     * Returns given filename's extension.
     *
     * @param string $string
     *
     * @return string
     */
    public static function fileExtension(string $string): string
    {
        return pathinfo($string, PATHINFO_EXTENSION);
    }

    /**
     * Returns filename without extension
     *
     * @param string $string
     *
     * @return string
     */
    public static function fileWithoutExtension(string $string): string
    {
        return pathinfo($string, PATHINFO_FILENAME);
    }
}
