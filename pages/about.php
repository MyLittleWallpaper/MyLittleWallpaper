<?php

// Check that correct entry point was used
if (!defined('INDEX')) {
    exit();
}

require_once(ROOT_DIR . 'classes/output/BasicPage.php');

define('ACTIVE_PAGE', 'about');
$aboutpage = new BasicPage();
$aboutpage->setPageTitleAddition('About');

$page_html = '<h1>About</h1>';
$page_html .= '<p>This website was born because I wanted to have a nice My Little Pony: Friendship is Magic wallpaper but had trouble finding a proper one and didn\'t want to start browsing through huge lists. Ironically I started doing just that get content on the site.</p>';
$page_html .= '<p>Any of the systems (image galleries) I knew weren\'t actually suitable for this site, so I decided to code one of my own from scratch with PHP. I wanted the site to be as lightweight as possible and sadly I really didn\'t (and still don\'t) have the time to ask permission from every artist, so my solution was to redirect downloads to their galleries (mainly deviantART) and didn\'t feel like modding that to any existing gallery systems. So I spent one evening <a href="http://www.youtube.com/watch?v=6zqlVJNsI4o#t=32s" target="_blank">coding furiously</a> and managed to get the site up during that night, with over 100 wallpapers.</p>';
//$page_html .= '<p>The site is hosted on my server which I rent from <a href="http://www.ovh.co.uk/" target="blank">OVH</a>. To keep things going smoothly, I have installed <a href="http://xcache.lighttpd.net/" target="_blank">XCache</a> and spent time optimizing the database structure and SQL-queries. I also have plans to switch to <a href="http://hhvm.com/" target="_blank">HHVM</a> in the near future.</p>';
$page_html .= '<p>As My Little Pony boom isn\'t going to last forever, I eventually decided to start adding new categories, such as <a href="' .
    PUB_PATH . 'c/cartoon-hangover/">Cartoon Hangover</a>, <a href="' . PUB_PATH .
    'c/touhou/">Touhou</a> and <a href="' . PUB_PATH . 'c/vocaloid/">Vocaloid</a>.</p>';
$page_html .= '<h2>Using the search</h2>';
$page_html .= '<p><img src="' . PUB_PATH .
    'search.png" style="float:right;margin:0 0 10px 10px;" alt="Search" title="Search autocomplete" />Since the search might be a bit difficult to use with no instructions, here\'s how you use it:</p>';
$page_html .= '<p>There are four different kind of tags: author, aspect, platform and normal tags. The search field has an autocomplete function which gives suggestions to your input. Other tags than normal tags are prefixed, for example "<i>author:WhiteDiamonds</i>". Autocomplete in the search doesn\'t require prefixes, but you can use prefixes to narrow the autocomplete results down.</p>';
$page_html .= '<p>If you want to search for wallpapers that have only the characters you specify, prefix the tags with <strong><span style="font-size:16px;">=</span></strong> character. Note that characters that aren\'t tagged (for example non-MLP characters) are not taken into account.</p>';
$page_html .= '<p>Some examples:</p>';
$page_html .= '<ul style="padding-left:18px;">';
$page_html .= '<li>Android wallpapers (& themes): <i>platform:Android</i> [<a href="' . PUB_PATH_CAT .
    '?search=platform%3AAndroid" target="_blank">link</a>]</li>';
$page_html .= '<li>Twilight Sparkle wallpapers: <i>Twilight Sparkle</i> [<a href="' . PUB_PATH .
    '?c/my-little-pony/search=Twilight+Sparkle" target="_blank">link</a>]</li>';
$page_html .= '<li>Wallpapers in 16:9 aspect: <i>aspect:16:9</i> [<a href="' . PUB_PATH_CAT .
    '?search=aspect%3A16%3A9" target="_blank">link</a>]';
$page_html .= '<li>Desktop wallpapers by CountCarbon: <i>author:CountCarbon</i> & <i>platform:Desktop</i> [<a href="' .
    PUB_PATH . 'c/my-little-pony/?search=author%3ACountCarbon%2C+platform%3ADesktop" target="_blank">link</a>]';
$page_html .= '<li>Wallpapers with only Fluttershy: <i>=Fluttershy</i> [<a href="' . PUB_PATH .
    'c/my-little-pony/?search=%3DFluttershy" target="_blank">link</a>]';
$page_html .= '<li>Wallpapers with only Fluttershy and Pinkie Pie: <i>=Fluttershy</i> & <i>=Pinkie Pie</i> [<a href="' .
    PUB_PATH . 'c/my-little-pony/?search=%3DFluttershy%2C+%3DPinkie+Pie" target="_blank">link</a>]';
$page_html .= '</ul>';
/*
$page_html .= '<h2>Project tracking</h2>';
$page_html .= '<p>Since there are many features planned and I really need to start making the improvements to the system running the site, a <a href="http://redmine.mylittlewallpaper.com/" target="_blank">project management site</a> has been opened to keep track on the progress and plans. You can see all completed and planned features as well as bug fixes there.</p>';
*/
$page_html .= '<h2>Credits etc.</h2>';
$page_html .= '<ul style="padding-left:18px;">';
$page_html .= '<li>jQuery & jQuery UI &copy; <a href="http://jquery.org/" target="_blank">jQuery Foundation</a> and the <a href="http://jqueryui.com/about" target="_blank">jQuery UI Team</a></li>';
$page_html .= '<li>jQuery Lazy Load &copy; <a href="http://www.appelsiini.net/projects/lazyload" target="_blank">Mika Tuupola</a></li>';
$page_html .= '<li>jQuery Tags Input &copy; <a href="http://xoxco.com/clickable/jquery-tags-input" target="_blank">XOXCO, Inc</a></li>';
$page_html .= '<li>Perfect Scrollbar &copy; <a href="http://noraesae.github.com/perfect-scrollbar/" target="_blank">HyeonJe Jun</a></li>';
$page_html .= '<li>vex dialog &copy; <a href="http://github.hubspot.com/vex/docs/welcome/" target="_blank">Adam Schwartz</a></li>';
$page_html .= '<li>jqPlot &copy; <a href="http://www.jqplot.com/" target="_blank">Chris Leonello</a></li>';
//$page_html .= '<li>Modern UI Icons &copy; <a href="http://modernuiicons.com/" target="_blank">Austin Andrews</a>';
$page_html .= '</ul>';

$page_html .= '<h2>Contact</h2>';
//$page_html .= '<p>You can use the <a href="/feedback.php">Feedback from</a> or contact us directly:</p>';
$page_html .= '<ul style="padding-left:18px;">';
$page_html .= '<li><label style="font-weight:bold;display:inline-block;width:80px;">Email</label><a href="mailto:sharkmachine(at)ecxol(dot)net">sharkmachine(at)ecxol(dot)net</a></li>';
$page_html .= '<li><label style="font-weight:bold;display:inline-block;width:80px;">Twitter</label>@<a href="http://twitter.com/petrihaikonen" target="_blank">petrihaikonen</a></li>';
$page_html .= '<li><label style="font-weight:bold;display:inline-block;width:80px;">IRC</label><a href="irc://irc.freenode.net/mylittlewallpaper">#mylittlewallpaper</a> @ <a href="http://freenode.net/" target="_blank">Freenode</a></li>';
$page_html .= '</ul>';

$meta = "\n" . '		<meta name="twitter:card" content="summary" />' . "\n";
$meta .= '		<meta name="twitter:description" content="This website was born because I wanted to have a nice My Little Pony: Friendship is Magic wallpaper but had trouble finding a proper one and didn\'t want to start browsing through huge lists." />' .
    "\n";

$aboutpage->setHtml($page_html);
$aboutpage->setMeta($meta);

$response = new Response($aboutpage);
$response->output();