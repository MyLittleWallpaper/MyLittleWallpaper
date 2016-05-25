<?php
// Check that correct entry point was used
if (!defined('INDEX')) {
	exit();
}

require_once(ROOT_DIR . 'classes/output/BasicPage.php');

define('ACTIVE_PAGE', 'api-v2');
$apiV1Page = new BasicPage();
$apiV1Page->setPageTitleAddition('API v2');

$page_html = '<h1>API v2.0</h1>';
$page_html .= '<p>My Little Wallpaper offers a REST API for fetching wallpaper information with download links. This documentation is for upcoming API version <strong>2.0</strong>.</p>';

$page_html .= '<h2>Favourites</h2>';
$page_html .= '<p>API call to get personal favourites. To use this API call, API token is needed. This can be found on account <a href="' . PUB_PATH_CAT . '/account" target="_blank">settings page</a>.</p>';
$page_html .= '<p>JSON endpoint for this call is <a href="' . PROTOCOL . SITE_DOMAIN . '/c/all/api/v2/favourites.json" target="_blank">' . PROTOCOL . SITE_DOMAIN . '/c/all/api/v2/featured.json</a>. This call wil return <strong>all</strong> user\'s favourites regardless of category in the URL.</p>';
$hyperLight = new Hyperlight\Hyperlight('php');
$page_html .= '<h3 style="margin:24px 0 2px 0;">Request parameters</h3>';
$page_html .= '<table class="parameter-table">';
$page_html .= '<tr>';
$page_html .= '<td class="api-field"><h4>requestId</h4><em>required</em></td>';
$page_html .= '<td class="api-description"><p><strong>Unique request ID.</strong></p><p>Every user\'s API call has to have a unique request ID.</p><p><strong>Max length: 64 characters</strong></p></td>';
$page_html .= '</tr>';
$page_html .= '<tr>';
$page_html .= '<td class="api-field"><h4>userName</h4><em>required</em></td>';
$page_html .= '<td class="api-description"><p><strong>User\'s username.</strong></p><p>This value is case insensitive.</p></td>';
$page_html .= '</tr>';
$page_html .= '<tr>';
$page_html .= '<td class="api-field"><h4>hash</h4><em>required</em></td>';
$page_html .= '<td class="api-description"><p><strong>SHA256 hash generated from string containing userName, API token and requestId.</strong></p><p>For example (PHP):</p><div class="source-code">'.$hyperLight->render('<?php' . "\n" . '$requestHash = hash(\'sha256\', $userName . $apiToken . $requestId);').'</div><p>Unique request ID is used as part of hash generation to keep the string unique on each call.</p></td>';
$page_html .= '</tr>';
$page_html .= '<tr>';
$page_html .= '<td class="api-field"><h4>limit</h4><em>optional</em></td>';
$page_html .= '<td class="api-description"><p>Limits the maximum number of results.</p><p><strong>Default: 10</p><p><strong>Max value:</strong></p><p>• <em>Random sorting</em> - 20<br />• <em>Popularity or date added sorting</em> - 100</p></td>';
$page_html .= '</tr>';
$page_html .= '<tr>';
$page_html .= '<td class="api-field"><h4>sort</h4><em>optional</em></td>';
$page_html .= '<td class="api-description"><p>Result sorting.</p><p>Accepts only two value, which are <em>popularity</em> and <em>random</em>. If left empty, results are sorted by date added, newest first.</p></td>';
$page_html .= '</tr>';
$page_html .= '<tr>';
$page_html .= '<td class="api-field"><h4>offset</h4><em>optional</em><p>Not applicable with random sorting</p></td>';
$page_html .= '<td class="api-description"><p>Result offset.</p><p>Define offset where the result listing starts from.</p></td>';
$page_html .= '</tr>';
$page_html .= '</table>';

$hyperLight = new Hyperlight\Hyperlight('javascript');

$page_html .= '<h3 style="margin:30px 0 2px 0;">The API returns following parameters:</h3>';
$page_html .= '<p>The API call returns the information formatted in JSON.</p>';
$page_html .= '<table class="parameter-table">';
$page_html .= '<tr>';
$page_html .= '<td class="api-field"><h4>amount</h4><em>always present</em></td>';
$page_html .= '<td class="api-description"><p>Amount of wallpapers in result.</p><p>For example:</p><div class="source-code">'.$hyperLight->render('"amount":7').'</div></td>';
$page_html .= '</tr>';
$page_html .= '<tr>';
$page_html .= '<td class="api-field"><h4>result</h4><em>always present</em></td>';
$page_html .= '<td class="api-description"><p>The list of resulted wallpapers as an array, if any. An empty array if no wallpapers found.</p><p>For example (indentation and line wrapping added for readability):</p>';

$jsonCode = '"result":[
  {
    "title":"\u307f\u3087\u3093",
    "imageId":"5551868684f4a1.44660109",
    "downloadURL":' . json_encode(PROTOCOL . SITE_DOMAIN . '/c/all/download/5551868684f4a1.44660109') . ',
    "fullImageURL":' . json_encode(PROTOCOL . SITE_DOMAIN . '/images/o_5551868684f4a1.44660109.jpg') . ',
    "dimensions":{"width":3150,"height":2150},
    "authors":["Masaharu (\u96c5\u6625)"],
    "clicks":1,
    "favourites":0
  },
  {
    "title":"89\u306e\u65e52012",
    "imageId":"5551844622e039.44447209",
    "downloadURL":' . json_encode(PROTOCOL . SITE_DOMAIN . '/c/all/download/5551844622e039.44447209') . ',
    "fullImageURL":' . json_encode(PROTOCOL . SITE_DOMAIN . '/images/o_5551844622e039.44447209.jpg') . ',
    "dimensions":{"width":2560,"height":1920},
    "authors":["CAFFEIN"],
    "clicks":2,
    "favourites":0
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
$page_html .= '<td class="api-field"><h4>imageId</h4><em>always present</em></td>';
$page_html .= '<td class="api-description"><p>Wallpaper image identifier.</p><p>This identifier can be used to get thumbnails from the site: <code>' . PROTOCOL . SITE_DOMAIN . '/image.php?image=[imageid]</code>, for example</p><div class="source-code">'.$hyperLight->render('"imageId":"504726d1982d86.18038044"').'</div></td>';
$page_html .= '</tr>';
$page_html .= '<tr>';
$page_html .= '<td class="api-field"><h4>downloadURL</h4><em>always present</em></td>';
$page_html .= '<td class="api-description"><p>Wallpaper download URL.</p><p>For example:</p><div class="source-code">'.$hyperLight->render('"downloadURL":"http:\/\/softfang.deviantart.com\/art\/Minimalist-Wallpaper-43-Wonderbolts-287463909"').'</div></td>';
$page_html .= '</tr>';
$page_html .= '<tr>';
$page_html .= '<td class="api-field"><h4>fullImageURL</h4><em>always present</em></td>';
$page_html .= '<td class="api-description"><p>Direct link to wallpaper image.</p><p>For example:</p><div class="source-code">'.$hyperLight->render('"fullImageURL":' . json_encode(PROTOCOL . SITE_DOMAIN . '/images/o_5551868684f4a1.44660109.jpg')).'</div></td>';
$page_html .= '</tr>';
$page_html .= '<tr>';
$page_html .= '<td class="api-field"><h4>dimensions</h4><em>present if applicable</em></td>';
$page_html .= '<td class="api-description"><p>Wallpaper dimensions. Not present if not applicable, like with Android live wallpapers and themes.</p><p>For example:</p><div class="source-code">'.$hyperLight->render('"dimensions":{"width":1920,"height":1080}').'</div></td>';
$page_html .= '</tr>';
$page_html .= '<tr>';
$page_html .= '<td class="api-field"><h4>authors</h4><em>always present</em></td>';
$page_html .= '<td class="api-description"><p>Wallpaper authors as an array.</p><p>For example:</p><div class="source-code">'.$hyperLight->render('"authors":["CaNoN-lb","impala99"]').'</div></td>';
$page_html .= '</tr>';
$page_html .= '<tr>';
$page_html .= '<td class="api-field"><h4>clicks</h4><em>always present</em></td>';
$page_html .= '<td class="api-description"><p>Amount of wallpaper download link clicks.</p><p>For example:</p><div class="source-code">'.$hyperLight->render('"clicks":35').'</div></td>';
$page_html .= '</tr>';
$page_html .= '<tr>';
$page_html .= '<td class="api-field"><h4>favourites</h4><em>always present</em></td>';
$page_html .= '<td class="api-description"><p>Amount of wallpaper favourites.</p><p>For example:</p><div class="source-code">'.$hyperLight->render('"favourites":35').'</div></td>';
$page_html .= '</tr>';
$page_html .= '</table>';


$meta = "\n".'		<meta name="twitter:card" content="summary" />'."\n";
$meta .= '		<meta name="twitter:description" content="My Little Wallpaper offers a REST API for fetching wallpaper information with download links. This documentation is for upcoming API version 2.0." />'."\n";

$apiV1Page->setHtml($page_html);
$apiV1Page->setMeta($meta);

$response = new Response($apiV1Page);
$response->output();