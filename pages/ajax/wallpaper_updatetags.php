<?php

// Check that correct entry point was used
if (!defined('INDEX')) {
    exit();
}

require_once(ROOT_DIR . 'classes/output/BasicPage.php');
require_once(ROOT_DIR . 'classes/Wallpaper.php');

$updateTags = new BasicPage();
$updateTags->setNoContainer();

$pageContents = '';

if (!empty($_GET['id'])) {
    $sql    = "SELECT * FROM wallpaper WHERE id = ? LIMIT 1";
    $result = $db->query($sql, [$_GET['id']]);
    while ($image = $result->fetch(PDO::FETCH_ASSOC)) {
        $wallpaper = new Wallpaper($image);

        $pageContents = '					<div class="tags">' . "\n";
        $pageContents .= '						<label>Authors</label>' . "\n";
        $pageContents .= '						<div class="tags">' . "\n";
        $authors      = $wallpaper->getAuthorTags();
        $first        = true;
        foreach ($authors as $tag) {
            if ($first) {
                $first = false;
            } else {
                $pageContents .= ', ' . "\n";
            }
            $pageContents .= '							<a href="' . PUB_PATH_CAT . '?search=' .
                urlencode('author:' . $tag->getName()) . '">' . Format::htmlEntities($tag->getName()) . '</a>';
        }
        $pageContents .= "\n" . '						</div>' . "\n";
        $pageContents .= '					</div>' . "\n";

        $pageContents .= '					<div class="tags">' . "\n";
        $pageContents .= '						<label>Tags</label>' . "\n";
        $pageContents .= '						<div class="tags">' . "\n";
        $tags         = $wallpaper->getBasicTags();
        $first        = true;
        foreach ($tags as $tag) {
            if ($first) {
                $first = false;
            } else {
                $pageContents .= ', ' . "\n";
            }
            $class = '';
            if ($tag->getType() == Tag::TAG_TYPE_CHARACTER) {
                $class = 'tagtype_character';
            } elseif ($tag->getType() == Tag::TAG_TYPE_STYLE) {
                $class = 'tagtype_style';
            }
            $pageContents .= '							<a href="' . PUB_PATH_CAT . '?search=' .
                urlencode($tag->getName()) . '"' . ($class != '' ? ' class="' . $class . '"' : '') . '>' .
                Format::htmlEntities($tag->getName()) . '</a>';
        }
        $pageContents .= "\n" . '						</div>' . "\n";
        $pageContents .= '					</div>' . "\n";

        $pageContents .= '					<div class="tags">' . "\n";
        $pageContents .= '						<label>Platform</label>' . "\n";
        $pageContents .= '						<div class="tags">' . "\n";
        $platforms    = $wallpaper->getPlatformTags();
        $first        = true;
        foreach ($platforms as $tag) {
            if ($first) {
                $first = false;
            } else {
                $pageContents .= ', ' . "\n";
            }
            $pageContents .= '							<a href="' . PUB_PATH_CAT . '?search=' .
                urlencode('platform:' . $tag->getName()) . '">' . Format::htmlEntities($tag->getName()) . '</a>';
        }
        $pageContents .= "\n" . '						</div>' . "\n";
        $pageContents .= '					</div>' . "\n";
        $updateTags->setHtml($pageContents);
    }
}

$response = new Response($updateTags);
$response->setDisableHeaderAndFooter();
$response->output();