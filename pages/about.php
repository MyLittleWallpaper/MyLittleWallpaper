<?php

declare(strict_types=1);

use MyLittleWallpaper\classes\output\BasicPage;
use MyLittleWallpaper\classes\Response;

const ACTIVE_PAGE = 'about';

$aboutPage = new BasicPage();
$aboutPage->setPageTitleAddition('About');

$pageHtml = '<h1>About</h1>';
$pageHtml .= '<p>This website was born because I wanted to have a nice My Little Pony: Friendship is Magic ' .
    'wallpaper but had trouble finding a proper one and didn\'t want to start browsing through huge lists. ' .
    'Ironically I started doing just that get content on the site.</p>';
$pageHtml .= '<p>Any of the systems (image galleries) I knew weren\'t actually suitable for this site, so I ' .
    'decided to code one of my own from scratch with PHP. I wanted the site to be as lightweight as possible and ' .
    'sadly I really didn\'t (and still don\'t) have the time to ask permission from every artist, so my solution ' .
    'was to redirect downloads to their galleries (mainly deviantART) and didn\'t feel like modding that to any ' .
    'existing gallery systems. So I spent one evening ' .
    '<a href="https://www.youtube.com/watch?v=6zqlVJNsI4o#t=32s" target="_blank">coding furiously</a> ' .
    'and managed to get the site up during that night, with over 100 wallpapers.</p>';
$pageHtml .= '<p>As My Little Pony boom isn\'t going to last forever, I eventually decided to start adding new ' .
    'categories, such as <a href="' .
    PUB_PATH . 'c/cartoon-hangover/">Cartoon Hangover</a>, <a href="' . PUB_PATH .
    'c/touhou/">Touhou</a> and <a href="' . PUB_PATH . 'c/vocaloid/">Vocaloid</a>.</p>';
$pageHtml .= '<h2>Using the search</h2>';
$pageHtml .= sprintf(
    '<p><img src="%ssearch.png" style="%s" alt="Search" title="Search autocomplete" />%s</p>',
    PUB_PATH,
    'float:right;margin:0 0 10px 10px;',
    'Since the search might be a bit difficult to use with no instructions, here\'s how you use it:'
);
$pageHtml .= '<p>There are four different kind of tags: author, aspect, platform and normal tags. The search field ' .
    'has an autocomplete function which gives suggestions to your input. Other tags than normal tags are prefixed, ' .
    'for example "<i>author:WhiteDiamonds</i>". Autocomplete in the search doesn\'t require prefixes, but you can ' .
    'use prefixes to narrow the autocomplete results down.</p>';
$pageHtml .= '<p>If you want to search for wallpapers that have only the characters you specify, prefix the tags ' .
    'with <strong><span style="font-size:16px;">=</span></strong> character. Note that characters that aren\'t ' .
    'tagged (for example non-MLP characters) are not taken into account.</p>';
$pageHtml .= '<p>Some examples:</p>';
$pageHtml .= '<ul style="padding-left:18px;">';
$pageHtml .= '<li>Android wallpapers (& themes): <i>platform:Android</i> [<a href="' . PUB_PATH_CAT .
    '?search=platform%3AAndroid" target="_blank">link</a>]</li>';
$pageHtml .= '<li>Twilight Sparkle wallpapers: <i>Twilight Sparkle</i> [<a href="' . PUB_PATH .
    '?c/my-little-pony/search=Twilight+Sparkle" target="_blank">link</a>]</li>';
$pageHtml .= '<li>Wallpapers in 16:9 aspect: <i>aspect:16:9</i> [<a href="' . PUB_PATH_CAT .
    '?search=aspect%3A16%3A9" target="_blank">link</a>]';
$pageHtml .= '<li>Desktop wallpapers by CountCarbon: <i>author:CountCarbon</i> & <i>platform:Desktop</i> [<a href="' .
    PUB_PATH . 'c/my-little-pony/?search=author%3ACountCarbon%2C+platform%3ADesktop" target="_blank">link</a>]';
$pageHtml .= '<li>Wallpapers with only Fluttershy: <i>=Fluttershy</i> [<a href="' . PUB_PATH .
    'c/my-little-pony/?search=%3DFluttershy" target="_blank">link</a>]';
$pageHtml .= '<li>Wallpapers with only Fluttershy and Pinkie Pie: <i>=Fluttershy</i> & <i>=Pinkie Pie</i> [<a href="' .
    PUB_PATH . 'c/my-little-pony/?search=%3DFluttershy%2C+%3DPinkie+Pie" target="_blank">link</a>]';
$pageHtml .= '</ul>';
$pageHtml .= '<h2>Credits etc.</h2>';
$pageHtml .= '<ul style="padding-left:18px;">';
$pageHtml .= sprintf(
    '<li>%s<a href="%s" target="_blank">jQuery Foundation</a> and the <a href="%s" target="_blank">%s</a></li>',
    'jQuery & jQuery UI &copy; ',
    'https://jquery.org/',
    'https://jqueryui.com/about',
    'jQuery UI Team'
);
$pageHtml .= sprintf(
    '<li>jQuery Lazy Load &copy; <a href="%s" target="_blank">Mika Tuupola</a></li>',
    'https://appelsiini.net/projects/lazyload/'
);
$pageHtml .= sprintf(
    '<li>jQuery Tags Input &copy; <a href="%s" target="_blank">XOXCO, Inc</a></li>',
    'https://github.com/xoxco/jQuery-Tags-Input'
);
$pageHtml .= sprintf(
    '<li>Perfect Scrollbar &copy; <a href="%s" target="_blank">HyeonJe Jun</a></li>',
    'https://github.com/noraesae/perfect-scrollbar-bower'
);
$pageHtml .= sprintf(
    '<li>vex dialog &copy; <a href="%s" target="_blank">Adam Schwartz</a></li>',
    'https://github.hubspot.com/vex/docs/welcome/'
);
$pageHtml .= '<li>jqPlot &copy; <a href="https://www.jqplot.com/" target="_blank">Chris Leonello</a></li>';
$pageHtml .= '</ul>';

$pageHtml .= '<h2>Contact</h2>';
$pageHtml .= '<ul style="padding-left:18px;">';
$pageHtml .= sprintf(
    '<li><label style="%s">Email</label><a href="mailto:sharkmachine(at)ecxol(dot)net">%s</a></li>',
    'font-weight:bold;display:inline-block;width:80px;',
    'sharkmachine(at)ecxol(dot)net'
);
$pageHtml .= '</ul>';

$meta = "\n" . '		<meta name="twitter:card" content="summary" />' . "\n";
$meta .= '		<meta name="twitter:description" content="This website was born because I wanted to have a nice ' .
    'My Little Pony: Friendship is Magic wallpaper but had trouble finding a proper one and didn\'t want to start ' .
    'browsing through huge lists." />' . "\n";

$aboutPage->setHtml($pageHtml);
$aboutPage->setMeta($meta);

$response = new Response($aboutPage);
$response->output();
