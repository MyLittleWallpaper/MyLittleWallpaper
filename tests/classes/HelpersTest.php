<?php

declare(strict_types=1);

namespace MyLittleWallpaperTests\classes;

use MyLittleWallpaper\classes\Helpers;
use MyLittleWallpaperTests\AbstractUnitTestCase;

class HelpersTest extends AbstractUnitTestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        Helpers::setAspectRatios(
            [
                1 => [16, 9],
                2 => [16, 10],
                3 => [4, 3],
                4 => [21, 9],
                5 => [32, 9],
            ]
        );
    }

    /**
     * @dataProvider provideTestData
     * @covers \MyLittleWallpaper\classes\Helpers::getTagAspectId
     *
     * @param int $width
     * @param int $height
     * @param int $aspectRatioId
     *
     * @return void
     */
    public function testGetTagAspectId(int $width, int $height, int $aspectRatioId): void
    {
        self::assertSame($aspectRatioId, Helpers::getTagAspectId($width, $height));
    }

    /**
     * @dataProvider provideTestData
     * @covers \MyLittleWallpaper\classes\Helpers::getAspectRatio
     *
     * @param int    $width
     * @param int    $height
     * @param int    $notUSed
     * @param string $aspectRatio
     *
     * @return void
     */
    public function testGetAspectRatio(int $width, int $height, int $notUSed, string $aspectRatio): void
    {
        self::assertSame($aspectRatio, Helpers::getAspectRatio($width, $height));
    }

    /**
     * @return array[]
     */
    public function provideTestData(): array
    {
        return [
            // Basic 16:9 resolutions
            [1280, 720, 1, '16:9'],
            [1366, 768, 1, '16:9'],
            [1600, 900, 1, '16:9'],
            [1920, 1080, 1, '16:9'],
            [2560, 1440, 1, '16:9'],
            [3200, 1800, 1, '16:9'],
            [3840, 2160, 1, '16:9'],
            [5120, 2880, 1, '16:9'],
            [7680, 4320, 1, '16:9'],

            // Basic 16:10 resolutions
            [1280, 800, 2, '16:10'],
            [1440, 900, 2, '16:10'],
            [1680, 1050, 2, '16:10'],
            [1920, 1200, 2, '16:10'],
            [2560, 1600, 2, '16:10'],
            [3840, 2400, 2, '16:10'],

            // Basic 4:3 resolutions
            [640, 480, 3, '4:3'],
            [1024, 768, 3, '4:3'],
            [1600, 1200, 3, '4:3'],

            // Basic 21:9 resolutions
            [2560, 1080, 4, '21:9'],
            [5120, 2160, 4, '21:9'],

            // Basic 32:9 resolutions
            [3840, 1080, 5, '32:9'],
            [5120, 1440, 5, '32:9'],

            // Resolutions close to an aspect ratio
            [10591, 6179, 1, '16:9'],
            [11549, 6000, 1, '16:9'],
            [10427, 7054, 2, '16:10'],
            [8008, 4836, 2, '16:10'],
            [8200, 6386, 3, '4:3'],
            [11293, 7727, 3, '4:3'],
            [12000, 5212, 4, '21:9'],
            [3440, 1440, 4, '21:9'],
            [5160, 2160, 4, '21:9'],
        ];
    }
}
