<?php

declare(strict_types=1);

use MyLittleWallpaper\classes\Database;
use MyLittleWallpaper\classes\Format;
use MyLittleWallpaper\classes\output\BasicPage;
use MyLittleWallpaper\classes\Response;

$html       = '';
$fields     = [['table' => 'tag_artist', 'field' => 'name']];
$order      = [['table' => 'tag_artist', 'field' => 'name']];
$conditions = [['table' => 'tag_artist', 'field' => 'deleted', 'value' => '0', 'operator' => '=']];
$db         = Database::getInstance();

$taglist = $db->getList('tag_artist', $fields, $conditions, $order);

$currentletter = '';
foreach ($taglist as $tag) {
    if (strcasecmp($currentletter, mb_substr($tag['name'], 0, 1, 'utf-8')) !== 0) {
        if ($currentletter != '') {
            $html .= '</div><div style="clear:both;"></div></div>';
        }
        $html          .= '<div class="taglist_select_letter_container"><div class="taglist_select_letter">' .
            mb_strtoupper(mb_substr($tag['name'], 0, 1, 'utf-8'), 'utf-8') . '</div><div class="taglist_letter_tags">';
        $currentletter = mb_substr($tag['name'], 0, 1, 'utf-8');
    }
    $html .= '<div class="taglist_tag"><a href="' . PUB_PATH . '?search=' . urlencode('author:' . $tag['name']) . '">' .
        Format::htmlEntities($tag['name']) . '</a></div>';
}
$html .= '</div><div style="clear:both;"></div></div><div style="clear:both;"></div>';

$taglist = new BasicPage();
$taglist->setHtml($html);
$taglist->setNoContainer();

$response = new Response($taglist);
$response->setDisableHeaderAndFooter();
$response->output();
