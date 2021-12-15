<?php

/**
 * Navigation class.
 * Used for constructing the main menu.
 */
class Navigation
{
    /**
     * @var NavigationElement[]
     */
    private array $navigationElements = [];

    public function __construct()
    {
        global $user;

        $this->navigationElements['index'] = new NavigationElement(PUB_PATH_CAT, 'Home');
        $this->navigationElements['index']->addSubMenuItem('index', new NavigationElement(PUB_PATH_CAT, 'Browse'));
        $this->navigationElements['index']->addSubMenuItem(
            'featured',
            new NavigationElement(PUB_PATH_CAT . 'featured', 'Featured')
        );
        $this->navigationElements['index']->addSubMenuItem(
            'random',
            new NavigationElement(PUB_PATH_CAT . 'random', 'Randoms')
        );
        if (!$user->getIsAnonymous()) {
            $this->navigationElements['index']->addSubMenuItem(
                'favourites',
                new NavigationElement(
                    PUB_PATH_CAT . 'favourites',
                    'My Favourites'
                )
            );
        }
        $this->navigationElements['index']->addSubMenuItem(
            'upload',
            new NavigationElement(PUB_PATH_CAT . 'upload', 'Submit')
        );
        $this->navigationElements['software'] = new NavigationElement(PUB_PATH_CAT . 'software', 'Software');
        $this->navigationElements['about']    = new NavigationElement(PUB_PATH_CAT . 'about', 'About');
        $this->navigationElements['api-v1']   = new NavigationElement(PUB_PATH_CAT . 'api-v1', 'API');
        $this->navigationElements['stats']    = new NavigationElement(PUB_PATH_CAT . 'stats', 'Stats');
        if ($user->getIsAnonymous()) {
            $this->navigationElements['login'] = new NavigationElement(PUB_PATH_CAT . 'login', 'Login');
        } else {
            $this->navigationElements['logout'] = new NavigationElement(PUB_PATH_CAT . 'logout', 'Logout');
        }
    }

    /**
     * @return NavigationElement[]
     */
    public function getNavigationElements(): array
    {
        return $this->navigationElements;
    }
}

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

    public function __toString()
    {
        return '<a href="' . $this->url . '">' . Format::htmlEntities($this->title) . '</a>';
    }
}