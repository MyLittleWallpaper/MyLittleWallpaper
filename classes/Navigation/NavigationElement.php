<?php

declare(strict_types=1);

namespace MyLittleWallpaper\classes\Navigation;

use MyLittleWallpaper\classes\Format;

/**
 * Navigation element class.
 */
class NavigationElement
{
    /**
     * @var string
     */
    private string $url;
    /**
     * @var string
     */
    private string $title;

    /**
     * @var NavigationElement[]
     */
    private array $subMenuItems = [];

    /**
     * @param string $url
     * @param string $title
     */
    public function __construct(string $url, string $title)
    {
        $this->url   = $url;
        $this->title = $title;
    }

    /**
     * @param string            $key
     * @param NavigationElement $navigationElement
     *
     * @return void
     */
    public function addSubMenuItem(string $key, NavigationElement $navigationElement): void
    {
        $this->subMenuItems[$key] = $navigationElement;
    }

    /**
     * @return NavigationElement[]
     */
    public function getSubMenuItems(): array
    {
        return $this->subMenuItems;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return '<a href="' . $this->url . '">' . Format::htmlEntities($this->title) . '</a>';
    }
}
