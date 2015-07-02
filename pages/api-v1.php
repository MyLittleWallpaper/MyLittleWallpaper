<?php
// Check that correct entry point was used
if (!defined('INDEX')) {
	exit();
}

require_once(ROOT_DIR . 'classes/output/BasicPage.php');

DEFINE('ACTIVE_PAGE', 'api-v1');
$apiV1Page = new BasicPage();
$apiV1Page->setPageTitleAddition('API v1');

$page_html = '<h1>API v1</h1>';
$page_html .= '<p>My Little Wallpaper offers a REST API for fetching wallpaper information with download links. The current API version is <strong>1.0</strong>.</p><p>Please note that version 2.0 with documentation will be released after My Little Wallpaper 2.0 is out of beta.</p>';
$page_html .= '<p>There are currently two kinds of requests:</p>';
$page_html .= '<ul>';
$page_html .= '<li>List: <a class="external" href="http://www.mylittlewallpaper.com' . PUB_PATH_CAT . '/c/all/api/v1/list.json">http://www.mylittlewallpaper.com' . PUB_PATH_CAT . '/c/all/api/v1/list.json</a></li>';
$page_html .= '<li>Random list: <a class="external" href="http://www.mylittlewallpaper.com' . PUB_PATH_CAT . '/c/all/api/v1/random.json">http://www.mylittlewallpaper.com' . PUB_PATH_CAT . '/c/all/api/v1/random.json</a></li>';
$page_html .= '</ul>';
$page_html .= '<h2>Request parameters</h2>';
$page_html .= '<p>You can give certain parameters to the calls in the URL, for example <a class="external" href="http://www.mylittlewallpaper.com/c/all/api/v1/random.json?search=Fluttershy">http://www.mylittlewallpaper.com/c/all/api/v1/random.json?search=Fluttershy</a></p>';
$page_html .= '<h3 style="margin:24px 0 2px 0;">The accepted parameters are the following:</h3>';
$page_html .= '<table class="parameter-table">';
$page_html .= '<tr>';
$page_html .= '<td class="api-field"><h4>search</h4><em>optional</em></td>';
$page_html .= '<td class="api-description"><p>Search input (tags).</p><p>This parameter takes the same input you put to the search field on the website. For example:</p><p><a class="external" href="http://www.mylittlewallpaper.com/c/all/api/v1/random.json?search=author%3Aharwicks-art%2C+Rarity">http://www.mylittlewallpaper.com/c/all/api/v1/random.json?search=author%3Aharwicks-art%2C+Rarity</a><br /><a class="external" href="http://www.mylittlewallpaper.com/c/all/api/v1/list.json?search=platform%3AMobile%2C+%3DPinkie+Pie">http://www.mylittlewallpaper.com/c/all/api/v1/list.json?search=platform%3AMobile%2C+%3DPinkie+Pie</a></p></td>';
$page_html .= '</tr>';
$page_html .= '<tr>';
$page_html .= '<td class="api-field"><h4>searchAny</h4><em>optional</em></td>';
$page_html .= '<td class="api-description"><p>Search input (tags), match any.</p><p>This parameter takes the same input you put to the search field on the website. For example:</p><p><a class="external" href="http://www.mylittlewallpaper.com/c/all/api/v1/random.json?searchAny=aspect%3A16%3A10%2Caspect%3A16%3A9">http://www.mylittlewallpaper.com/c/all/api/v1/random.json?searchAny=aspect%3A16%3A10%2Caspect%3A16%3A9</a></p></td>';
$page_html .= '</tr>';
$page_html .= '<tr>';
$page_html .= '<td class="api-field"><h4>searchExclude</h4><em>optional</em></td>';
$page_html .= '<td class="api-description"><p>Search input (tags), exclude from search.</p><p>This parameter takes the same input you put to the search field on the website. For example:</p><p><a class="external" href="http://www.mylittlewallpaper.com/c/all/api/v1/random.json?searchExclude=Princess+Twilight">http://www.mylittlewallpaper.com/c/all/api/v1/random.json?search=author%3Aharwicks-art%2C+Rarity</a></p></td>';
$page_html .= '</tr>';
$page_html .= '<tr>';
$page_html .= '<td class="api-field"><h4>size</h4><em>optional</em></td>';
$page_html .= '<td class="api-description"><p>Search input (size).</p><p>Used to search for size equal or greater than. Only for desktop wallpapers.</p><p><strong>Accepted values are the following:</strong></p><p>• <strong>1</strong> - equeal or greater than <em>1920x1200</em><br />• <strong>2</strong> - equeal or greater than <em>1920x1080</em><br />• <strong>3</strong> - equeal or greater than <em>1680x1050</em><br />• <strong>4</strong> - equeal or greater than <em>1366x768</em><br />• <strong>5</strong> - equeal or greater than <em>2560x1600</em><br />• <strong>6</strong> - equeal or greater than <em>2560x1440</em></p><p>For example:</p><p><a class="external" href="http://www.mylittlewallpaper.com/c/all/api/v1/random.json?size=5">http://www.mylittlewallpaper.com/c/all/api/v1/random.json?size=5</a><br /><a class="external" href="http://www.mylittlewallpaper.com/c/all/api/v1/list.json?search=author%3Aharwicks-art%2C+Rarity&#38;size=1">http://www.mylittlewallpaper.com/c/all/api/v1/list.json?search=author%3Aharwicks-art%2C+Rarity&#38;size=1</a></p></td>';
$page_html .= '</tr>';
$page_html .= '<tr>';
$page_html .= '<td class="api-field"><h4>date</h4><em>optional</em></td>';
$page_html .= '<td class="api-description"><p>Search input (date added).</p><p>Searches for wallpapers added on specific date (EET/EEST). Date format <em>YYYY-MM-DD</em>. For example:</p><p><a class="external" href="http://www.mylittlewallpaper.com/c/all/api/v1/random.json?date=2013-01-24">http://www.mylittlewallpaper.com/c/all/api/v1/random.json?date=2013-01-24</a><br /><a class="external" href="http://www.mylittlewallpaper.com/c/all/api/v1/list.json?search=%3DRarity&#38;date=2013-01-24">http://www.mylittlewallpaper.com/c/all/api/v1/list.json?search=%3DRarity&#38;date=2013-01-24</a></p></td>';
$page_html .= '</tr>';
$page_html .= '<tr>';
$page_html .= '<td class="api-field"><h4>limit</h4><em>optional</em></td>';
$page_html .= '<td class="api-description"><p>Limits the maximum number of results.</p><p><strong>Default:</strong></p><p>• <em>random.json</em> - 1<br />• <em>list.json</em> - 10</p><p><strong>Max value:</strong></p><p>• <em>random.json</em> - 20<br />• <em>list.json</em> - 100</p><p>For example:</p><p><a class="external" href="http://www.mylittlewallpaper.com/c/all/api/v1/random.json?date=2013-01-24&#38;limit=5">http://www.mylittlewallpaper.com/c/all/api/v1/random.json?date=2013-01-24&#38;limit=5</a><br /><a class="external" href="http://www.mylittlewallpaper.com/c/all/api/v1/list.json?search=%3DPrincess+Luna&#38;limit=20">http://www.mylittlewallpaper.com/c/all/api/v1/list.json?search=%3DPrincess+Luna&#38;limit=20</a></p></td>';
$page_html .= '</tr>';
$page_html .= '<tr>';
$page_html .= '<td class="api-field"><h4>sort</h4><em>optional</em><p>Not applicable with random.json</p></td>';
$page_html .= '<td class="api-description"><p>Result sorting.</p><p>Accepts only one value, which is <em>popularity</em>. If left empty, results are sorted by date added, newest first. For example:</p><p><a class="external" href="http://www.mylittlewallpaper.com/c/all/api/v1/list.json?date=2013-01-24&#38;sort=popularity">http://www.mylittlewallpaper.com/c/all/api/v1/list.json?date=2013-01-24&#38;sort=popularity</a></p></td>';
$page_html .= '</tr>';
$page_html .= '<tr>';
$page_html .= '<td class="api-field"><h4>offset</h4><em>optional</em><p>Not applicable with random.json</p></td>';
$page_html .= '<td class="api-description"><p>Result offset.</p><p>Define offset where the result listing starts from. For example:</p><p><a class="external" href="http://www.mylittlewallpaper.com/c/all/api/v1/list.json?date=2013-01-24&#38;offset=10">http://www.mylittlewallpaper.com/c/all/api/v1/list.json?date=2013-01-24&#38;offset=10</a></p></td>';
$page_html .= '</tr>';
$page_html .= '</table>';
$page_html .= '<h2>Response</h2>';
$page_html .= '<p>The API call returns the information formatted in JSON.</p>';

$jsonCode = '{
    "search_tags":[],
    "amount":1,
    "offset":0,
    "search_total":7156,
    "result":[
        {
            "title":"slumber",
            "imageid":"510129af5dabe3.17214352",
            "downloadurl":"http:\/\/rublegun.deviantart.com\/art\/slumber-347275656",
            "dimensions":
                {
                    "width":"2000",
                    "height":"1000"
                },
            "authors":[
                "RubleGun"
            ],
            "clicks":"0"
        }
    ]
}';

$page_html .= '<p>Example of list.json response (indentation and line wrapping added for readability):<br /></p>';

$hyperLight = new Hyperlight\Hyperlight('javascript');
$page_html .= '<p class="source-code">' . $hyperLight->render($jsonCode) . '</p>';

$page_html .= '<h3 style="margin:30px 0 2px 0;">The API returns following parameters:</h3>';
$page_html .= '<table class="parameter-table">';
$page_html .= '<tr>';
$page_html .= '<td class="api-field"><h4>search_tags</h4><em>always present</em></td>';
$page_html .= '<td class="api-description"><p>Tags used in search as an array.</p><p>For example:</p><div class="source-code">'.$hyperLight->render('"search_tags":["Fluttershy","Minimalistic","author:vexx3"]').'</div></td>';
$page_html .= '</tr>';
$page_html .= '<tr>';
$page_html .= '<td class="api-field"><h4>amount</h4><em>always present</em></td>';
$page_html .= '<td class="api-description"><p>Amount of wallpapers in result.</p><p>For example:</p><div class="source-code">'.$hyperLight->render('"amount":7').'</div></td>';
$page_html .= '</tr>';
$page_html .= '<tr>';
$page_html .= '<td class="api-field"><h4>offset</h4><em>always present in list.json response, never in random.json response</em></td>';
$page_html .= '<td class="api-description"><p>Offset of the listing.</p><p>For example:</p><div class="source-code">'.$hyperLight->render('"offset":80').'</div></td>';
$page_html .= '</tr>';
$page_html .= '<tr>';
$page_html .= '<td class="api-field"><h4>search_total</h4><em>always present in list.json response, never in random.json response</em></td>';
$page_html .= '<td class="api-description"><p>Amount of total results of the search.</p><p>For example:</p><div class="source-code">'.$hyperLight->render('"search_total":132').'</div></td>';
$page_html .= '</tr>';
$page_html .= '<tr>';
$page_html .= '<td class="api-field"><h4>result</h4><em>always present</em></td>';
$page_html .= '<td class="api-description"><p>The list of resulted wallpapers as an array, if any. An empty array if no wallpapers found.</p><p>For example (indentation and line wrapping added for readability):</p>';

$jsonCode = '"result":[
    {
        "title":"SO SPARKLY ! - Wallpaper",
        "imageid":"50a33f5d6e3d51.20451078",
        "downloadurl":"http:\/\/tzolkine.deviantart.com\/art\/SO-SPARKLY-Wallpaper-337170873",
        "dimensions":{"width":"1920","height":"1080"},
        "authors":["LazyPixel","Tzolkine"],
        "clicks":"22"
    },
    {
        "title":"Crystal Twilight - Wallpaper",
        "imageid":"50a33f3936d055.87249674",
        "downloadurl":"http:\/\/tzolkine.deviantart.com\/art\/Crystal-Twilight-Wallpaper-337170887",
        "dimensions":{"width":"1920","height":"1080"},
        "authors":["Pony-Vectors","Tzolkine"],
        "clicks":"26"
    }
]';

$page_html .= '<div class="source-code">' . $hyperLight->render($jsonCode) . '</div>';
$page_html .= '</tr>';
$page_html .= '</table>';

$page_html .= '<h3 style="margin:24px 0 2px 0;">The parameters of an individual wallpaper in result array:</h3>';
$page_html .= '<table class="parameter-table">';
$page_html .= '<tr>';
$page_html .= '<td class="api-field"><h4>title</h4><em>always present</em></td>';
$page_html .= '<td class="api-description"><p>Wallpaper title.</p><p>For example:</p><div class="source-code">'.$hyperLight->render('"title":"Rainbow Dash wallpaper pack"').'</div></td>';
$page_html .= '</tr>';
$page_html .= '<tr>';
$page_html .= '<td class="api-field"><h4>imageid</h4><em>always present</em></td>';
$page_html .= '<td class="api-description"><p>Wallpaper image identifier.</p><p>This identifier can be used to get thumbnails from the site: <code>http://www.mylittlewallpaper.com/image.php?image=[imageid]</code>, for example</p><div class="source-code">'.$hyperLight->render('"imageid":"504726d1982d86.18038044"').'</div></td>';
$page_html .= '</tr>';
$page_html .= '<tr>';
$page_html .= '<td class="api-field"><h4>downloadurl</h4><em>always present</em></td>';
$page_html .= '<td class="api-description"><p>Wallpaper download URL.</p><p>For example:</p><div class="source-code">'.$hyperLight->render('"downloadurl":"http:\/\/softfang.deviantart.com\/art\/Minimalist-Wallpaper-43-Wonderbolts-287463909"').'</div></td>';
$page_html .= '</tr>';
$page_html .= '<tr>';
$page_html .= '<td class="api-field"><h4>dimensions</h4><em>present if applicable</em></td>';
$page_html .= '<td class="api-description"><p>Wallpaper dimensions. Not present if not applicable, like with Android live wallpapers and themes.</p><p>For example:</p><div class="source-code">'.$hyperLight->render('"dimensions":{"width":"1920","height":"1080"}').'</div></td>';
$page_html .= '</tr>';
$page_html .= '<tr>';
$page_html .= '<td class="api-field"><h4>authors</h4><em>always present</em></td>';
$page_html .= '<td class="api-description"><p>Wallpaper authors as an array.</p><p>For example:</p><div class="source-code">'.$hyperLight->render('"authors":["CaNoN-lb","impala99"]').'</div></td>';
$page_html .= '</tr>';
$page_html .= '<tr>';
$page_html .= '<td class="api-field"><h4>clicks</h4><em>always present</em></td>';
$page_html .= '<td class="api-description"><p>Amount of wallpaper download link clicks.</p><p>For example:</p><div class="source-code">'.$hyperLight->render('"clicks":"35"').'</div></td>';
$page_html .= '</tr>';
$page_html .= '</table>';

$meta = "\n".'		<meta name="twitter:card" content="summary" />'."\n";
$meta .= '		<meta name="twitter:description" content="My Little Wallpaper offers a REST API for fetching wallpaper information with download links. The current API version is 1.0. Please note that version 2.0 will be released after it is out of beta." />'."\n";

$apiV1Page->setHtml($page_html);
$apiV1Page->setMeta($meta);

$response = new Response($apiV1Page);
$response->output();