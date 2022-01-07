<?php

declare(strict_types=1);

namespace MyLittleWallpaper\classes;

use PDO;

class Helpers
{
    /**
     * @var array|null
     * @psalm-var array<int,array{0:int,1:int}>|null
     */
    private static ?array $aspects = null;

    /**
     * @param int $width
     * @param int $height
     *
     * @return int
     */
    public static function getTagAspectId(int $width, int $height): int
    {
        $differences = [];
        foreach (self::getAspectRatios() as $id => [$aspectX, $aspectY]) {
            $differences[$id] = abs(($aspectX / $aspectY) - ($width / $height));
        }
        asort($differences, SORT_NUMERIC);
        return array_key_first($differences);
    }

    /**
     * @param int $width
     * @param int $height
     *
     * @return string
     */
    public static function getAspectRatio(int $width, int $height): string
    {
        return implode(':', self::getAspectRatios()[self::getTagAspectId($width, $height)]);
    }

    /**
     * @param array<int,array{0:int,1:int}> $aspects
     *
     * @return void
     */
    public static function setAspectRatios(array $aspects): void
    {
        self::$aspects = $aspects;
    }

    /**
     * @return array<int,array{0:int,1:int}>
     */
    private static function getAspectRatios(): array
    {
        if (self::$aspects === null) {
            $db = Database::getInstance();
            $result = $db->query('SELECT id, name FROM tag_aspect ORDER BY id');
            while ([$id, $aspect] = $result->fetch(PDO::FETCH_NUM)) {
                self::$aspects[$id] = explode(':', $aspect);
            }
        }
        return self::$aspects;
    }
}
