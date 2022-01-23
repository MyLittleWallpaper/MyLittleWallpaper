<?php

declare(strict_types=1);

namespace MyLittleWallpaper\classes\Navigation;

class Navigation
{
    /**
     * @var NavigationElement[]
     */
    private array $navigationElements = [];

    /**
     * Navigation constructor
     */
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
