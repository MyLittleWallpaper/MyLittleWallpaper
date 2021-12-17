<?php

declare(strict_types=1);

namespace MyLittleWallpaperTests\classes;

use MyLittleWallpaper\classes\Format;
use MyLittleWallpaperTests\AbstractUnitTestCase;

final class FormatTest extends AbstractUnitTestCase
{
    /**
     * @covers \MyLittleWallpaper\classes\Format::htmlEntities
     * @return void
     */
    public function testHtmlEntities(): void
    {
        self::assertEquals(
            '&lt;&gt;&amp;&quot;&#039;&ouml;&auml;&Oslash;&AElig;は複',
            Format::htmlEntities('<>&"\'öäØÆは複')
        );
    }

    /**
     * @covers \MyLittleWallpaper\classes\Format::xmlEntities
     * @return void
     */
    public function testXmlEntities(): void
    {
        self::assertEquals(
            '&lt;&gt;&amp;&quot;&apos;öäØÆは複',
            Format::xmlEntities('<>&"\'öäØÆは複')
        );
    }

    /**
     * @covers \MyLittleWallpaper\classes\Format::escapeQuotes
     * @return void
     */
    public function testEscapeQuotes(): void
    {
        self::markTestIncomplete('Not implemented');
    }

    /**
     * @covers \MyLittleWallpaper\classes\Format::fileExtension
     * @return void
     */
    public function testFileExtension(): void
    {
        self::markTestIncomplete('Not implemented');
    }

    /**
     * @covers \MyLittleWallpaper\classes\Format::fileWithoutExtension
     * @return void
     */
    public function testFileWithoutExtension(): void
    {
        self::markTestIncomplete('Not implemented');
    }
}
