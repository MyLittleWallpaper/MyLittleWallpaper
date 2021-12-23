<?php

declare(strict_types=1);

use MyLittleWallpaper\classes\Format;
use MyLittleWallpaper\classes\Tag\Tag;

global $response, $user;

$wallpapers = $response->getResponseVariables()->wallpapers;

foreach ($wallpapers as $wallpaper) {
    if ($response->getResponseVariables()->large_wallpaper_thumbs) {
        if ($wallpaper->getHeight() > 700) {
            $imaged = calc_thumb_size($wallpaper->getWidth(), $wallpaper->getHeight(), 457, 342);
        } else {
            $imaged = calc_thumb_size($wallpaper->getWidth(), $wallpaper->getHeight(), 400, 300);
        }
    } else {
        $imaged = calc_thumb_size($wallpaper->getWidth(), $wallpaper->getHeight(), 200, 150);
    }

    // Add to id to prevent two containers from having the same id in random listing
    $rand_id = str_replace('.', '', uniqid());

    $actions = '						<div class="actions">' . "\n";
    $actions .= '							<a class="download piwik_download" id="dld_a_' . $wallpaper->getId() .
        '" href="' . Format::htmlEntities($wallpaper->getDownloadLink()) . '" target="_blank">Download</a>';
    $actions .= '<a href="#info" class="wallinfo" data-id="' . $wallpaper->getId() . '_' . $rand_id . '"">Info</a>';
    $actions .= '<a href="#edit" class="editwall" data-id="' . $wallpaper->getId() . '">Edit</a>' . "\n";

    if (!$user->getIsAnonymous()) {
        $isFav   = $wallpaper->getIsFavourite($user->getId());
        $actions .= '							<br /><a class="favourite fav_active" data-wallpaperid="' .
            $wallpaper->getId() . '" id="fav_a_' . $wallpaper->getId() . '" href="#fav">' .
            ($isFav ? 'Remove from favs' : 'Add to favourites') . '</a>' . "\n";
    } else {
        $actions .= '							<br /><a class="favourite fav_disabled" href="#fav">' .
            'Login to favourite</a>' . "\n";
    }
    $actions .= '						</div>' . "\n";
    if ($response->getResponseVariables()->large_wallpaper_thumbs) {
        $class = ('image_container_large');
    } else {
        $class = ('image_container_small');
    }
    echo '			<div class="image_container ' . $class .
        '" id="image_container_' . $wallpaper->getId() . '_' . $rand_id . '"><div>' . "\n";
    echo '				<div class="image_basicinfo">' . "\n";
    echo '					<div class="imagebox">' . "\n";
    echo '						<a class="image_preview grouped_images" id="a_' . $wallpaper->getId() .
        '" href="#preview" onclick="return image_preview(this, \'image_preview_' . $wallpaper->getId() . '\', \'' .
        Format::escapeQuotes($wallpaper->getImageThumbnailLink(2)) . '\', \'' .
        Format::escapeQuotes($wallpaper->getDownloadLink()) . '\');">' . "\n";
    echo '							<img class="lazy" style="width:' . $imaged[0] . 'px;height:' . $imaged[1] .
        'px" src="' . PUB_PATH . THEME . '/images/gray_small.png" data-original="' . Format::htmlEntities(
            $wallpaper->getImageThumbnailLink($response->getResponseVariables()->large_wallpaper_thumbs ? 3 : 1)
        ) . '" alt="' . Format::htmlEntities($wallpaper->getName()) . '" title="' .
        Format::htmlEntities($wallpaper->getName()) . '" />' . "\n";
    echo '						</a>' . "\n";
    echo '					</div>' . "\n";
    if ($response->getResponseVariables()->large_wallpaper_thumbs) {
        echo $actions;
    }
    echo '					<div class="image_info">' . "\n";
    if ($wallpaper->getHasResolution()) {
        echo '						<div class="size"><label>Size</label>' . $wallpaper->getSize();
        if ($wallpaper->getHasAspect()) {
            echo ' (';
            $aspects = $wallpaper->getAspectTags();
            $first   = true;
            foreach ($aspects as $tag) {
                if ($first) {
                    $first = false;
                } else {
                    echo ', ' . "\n";
                }
                echo '<a href="' . PUB_PATH_CAT . '?search=' . urlencode('aspect:' . $tag->getName()) . '">' .
                    Format::htmlEntities($tag->getName()) . '</a>';
            }
            echo ')';
        }
        echo '</div>' . "\n";
    }
    echo '						<div class="timeadded"><label>Added</label>' .
        date('Y-m-d', $wallpaper->getTimeAdded()) . '</div>' . "\n";
    echo '						<div class="clicks_and_favs"><label>&nbsp;</label>' . $wallpaper->getClicks() .
        ' click' . ($wallpaper->getClicks() != 1 ? 's' : '') . ', <span id="fav_count_' . $wallpaper->getId() . '">' .
        $wallpaper->getFavourites() . ' fav' . ($wallpaper->getFavourites() != 1 ? 's' : '') . '</span></div>' . "\n";
    if (!$response->getResponseVariables()->large_wallpaper_thumbs) {
        echo $actions;
    }
    echo '					</div>' . "\n";
    echo '				</div>' . "\n";
    echo '				<div class="image_extra_info" id="image_info_id' . $wallpaper->getId() . '">' . "\n";

    echo '					<div class="tags">' . "\n";
    echo '						<label>Authors</label>' . "\n";
    echo '						<div class="tags">' . "\n";
    $authors = $wallpaper->getAuthorTags();
    $first   = true;
    foreach ($authors as $tag) {
        if ($first) {
            $first = false;
        } else {
            echo ', ' . "\n";
        }
        echo '							<a href="' . PUB_PATH_CAT . '?search=' .
            urlencode('author:' . $tag->getName()) . '">' . Format::htmlEntities($tag->getName()) . '</a>';
    }
    echo "\n" . '						</div>' . "\n";
    echo '					</div>' . "\n";

    echo '					<div class="tags">' . "\n";
    echo '						<label>Tags</label>' . "\n";
    echo '						<div class="tags">' . "\n";
    $tags  = $wallpaper->getBasicTags();
    $first = true;
    foreach ($tags as $tag) {
        if ($first) {
            $first = false;
        } else {
            echo ', ' . "\n";
        }
        $class = '';
        if ($tag->getType() == Tag::TAG_TYPE_CHARACTER) {
            $class = 'tagtype_character';
        } elseif ($tag->getType() == Tag::TAG_TYPE_STYLE) {
            $class = 'tagtype_style';
        }
        echo '							<a href="' . PUB_PATH_CAT . '?search=' . urlencode($tag->getName()) . '"' .
            ($class != '' ? ' class="' . $class . '"' : '') . '>' . Format::htmlEntities($tag->getName()) . '</a>';
    }
    echo "\n" . '						</div>' . "\n";
    echo '					</div>' . "\n";

    echo '					<div class="tags">' . "\n";
    echo '						<label>Platform</label>' . "\n";
    echo '						<div class="tags">' . "\n";
    $platforms = $wallpaper->getPlatformTags();
    $first     = true;
    foreach ($platforms as $tag) {
        if ($first) {
            $first = false;
        } else {
            echo ', ' . "\n";
        }
        echo '							<a href="' . PUB_PATH_CAT . '?search=' .
            urlencode('platform:' . $tag->getName()) . '">' . Format::htmlEntities($tag->getName()) . '</a>';
    }
    echo "\n" . '						</div>' . "\n";
    echo '					</div>' . "\n";

    echo '				</div>' . "\n";
    echo '			</div></div>' . "\n";
}
