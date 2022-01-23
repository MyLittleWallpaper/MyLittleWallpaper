<?php

declare(strict_types=1);

use MyLittleWallpaper\classes\Format;
use MyLittleWallpaper\classes\output\BasicXML;
use MyLittleWallpaper\classes\output\WallpaperList;
use MyLittleWallpaper\classes\Response;

$wallpaperList = new WallpaperList();
$wallpaperList->loadSearchFromRequest();
if (CATEGORY_ID > 0) {
    $wallpaperList->setCategory(CATEGORY_ID);
}
$wallpaperList->setWallpapersPerPage(150);
$wallpaperList->loadWallpapers();
$wallpapers = $wallpaperList->getWallpapers();

$timeAdded = (!empty($wallpapers[0]) ? $wallpapers[0]->getTimeAdded() : time());

$output = '<rss xmlns:media="http://search.yahoo.com/mrss/" xmlns:atom="http://www.w3.org/2005/Atom" version="2.0">';
$output .= '<channel>';
$output .= '<title>My Little Wallpaper feed</title>';
$output .= '<link>' . Format::xmlEntities(PROTOCOL . SITE_DOMAIN . PUB_PATH) . '</link>';
$output .= '<description>Latest wallpapers from mylittlewallpaper.com</description>';
$output .= '<atom:link href="' . Format::xmlEntities(PROTOCOL . SITE_DOMAIN . $_SERVER['REQUEST_URI']) .
    '" rel="self"></atom:link>';
$output .= '<language>en-us</language>';
$output .= '<lastBuildDate>' . gmdate('Y-m-d', $timeAdded) . 'T' . gmdate('H:i:s', $timeAdded) . 'Z</lastBuildDate>';

if (!empty($wallpapers)) {
    foreach ($wallpapers as $wallpaper) {
        $author     = '';
        $tagAuthors = $wallpaper->getAuthorTags();
        foreach ($tagAuthors as $tagAuthor) {
            if ($author != '') {
                $author .= ', ';
            }
            $author .= $tagAuthor->getName();
        }

        $output  .= '<item>';
        $output  .= '<title>' . Format::xmlEntities($wallpaper->getName() . ' by ' . $author) . '</title>';
        $output  .= '<link>' . Format::xmlEntities($wallpaper->getDownloadLink()) . '</link>';
        $output  .= '<description>' .
            Format::xmlEntities(
                '<img src="' . Format::xmlEntities($wallpaper->getImageThumbnailLink(2)) . '" alt="" />'
            ) . '</description>';
        $output  .= '<pubDate>' . gmdate('Y-m-d', $wallpaper->getTimeAdded()) . 'T' .
            gmdate('H:i:s', $wallpaper->getTimeAdded()) . 'Z</pubDate>';
        $output  .= '<guid>' . Format::xmlEntities($wallpaper->getFileId()) . '</guid>';
        $output  .= '<media:title type="plain">' . Format::xmlEntities($wallpaper->getName()) . '</media:title>';
        $authors = $wallpaper->getAuthorTags();
        if (!empty($authors)) {
            foreach ($authors as $author) {
                $output .= '<media:credit role="author" scheme="urn:ebu">' . Format::xmlEntities($author->getName()) .
                    '</media:credit>';
            }
        }
        $output .= '<media:content url="' . Format::xmlEntities($wallpaper->getImageLink()) . '" height="' .
            $wallpaper->getHeight() . '" width="' . $wallpaper->getWidth() . '" medium="image" />';
        $output .= '</item>';
    }
}

$output .= '</channel></rss>';

$xmlOutput = new BasicXML($output);
$response  = new Response($xmlOutput);
$response->output();
