<?php

// Check that correct entry point was used
if (!defined('INDEX')) {
    exit();
}

require_once(ROOT_DIR . 'classes/output/BasicPage.php');

$html          = '';
$currentletter = '';

$sql = "SELECT `name`, type FROM tag" . (CATEGORY_ID > 0 ? " WHERE series IS NULL OR series = ?" : "") .
    " ORDER BY `name`";
if (CATEGORY_ID > 0) {
    $data = [CATEGORY_ID];
} else {
    $data = [];
}
$result = $db->query($sql, $data);
while ($tag = $result->fetch(PDO::FETCH_ASSOC)) {
    if (strcasecmp($currentletter, mb_substr($tag['name'], 0, 1, 'utf-8')) !== 0) {
        if ($currentletter != '') {
            $html .= '</div><div style="clear:both;"></div></div>';
        }
        $html          .= '<div class="taglist_select_letter_container"><div class="taglist_select_letter">' .
            mb_strtoupper(mb_substr($tag['name'], 0, 1, 'utf-8'), 'utf-8') . '</div><div class="taglist_letter_tags">';
        $currentletter = mb_substr($tag['name'], 0, 1, 'utf-8');
    }
    $html .= '<div class="taglist_tag"><a title="Tag type: ' . Format::htmlEntities(ucfirst($tag['type'])) .
        '" class="tagtype_' . Format::htmlEntities($tag['type']) . '" href="' . PUB_PATH_CAT . '?search=' .
        urlencode($tag['name']) . '">' . Format::htmlEntities($tag['name']) . '</a></div>';
}
$html .= '</div><div style="clear:both;"></div></div><div style="clear:both;"></div>';

$taglist = new BasicPage();
$taglist->setHtml($html);
$taglist->setNoContainer();

$response = new Response($taglist);
$response->setDisableHeaderAndFooter();
$response->output();