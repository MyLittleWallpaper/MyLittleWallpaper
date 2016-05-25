<?php
// Check that correct entry point was used
if (!defined('INDEX')) {
	exit();
}

global $db, $page;

$html = '';
$javaScript = '';

if (!empty($page)) {
	$file = $db->getRecord('wallpaper', Array('field' => 'file', 'value' => $page));
	if (!empty($file['id']) && $file['deleted'] == '0') {
		if (file_exists(ROOT_DIR . FILE_FOLDER . $file['file'])) {
			if ($file['direct_with_link'] == '1') {
				require_once(ROOT_DIR . 'classes/Wallpaper.php');
				require_once(ROOT_DIR . 'classes/output/BasicPage.php');
				$downloadPage = new BasicPage();

				$wallpaper = new Wallpaper($file);

				$first_tag = true;
				$artists = '';
				$artists_header = '';
				$tag_authors = $wallpaper->getAuthorTags();
				foreach ($tag_authors as $k => $tag_author) {
					$artists .= '<a href="' . PUB_PATH_CAT . '?search=author%3A' . urlencode($tag_author->getName()) . '" target="_blank">' . Format::htmlEntities($tag_author->getName()) . '</a>';

					if ($artists_header != '') {
						if ($k < count($tag_authors) - 1) {
							$artists_header .= ', ';
						} else {
							$artists_header .= ' and ';
						}
					}
					$artists_header .= Format::htmlEntities($tag_author->getName());
				}
				$downloadPage->setPageTitleAddition(Format::htmlEntities($wallpaper->getName()) . ' by ' . $artists_header);

				$javaScript = '$(function() {
					var favButtons = $("a.fav_active");
					favButtons.click(function(e) {
						var wallpaperId = $(this).data("wallpaperid");
						$.ajax({
							url: "' . PUB_PATH_CAT . 'ajax/wallpaper-fav?wallpaperId=" + encodeURIComponent(wallpaperId),
							success: function (data) {
								$("#fav_count_" + wallpaperId).text(data.favCountNumber);
								$("#fav_a_" + wallpaperId).text(data.favButtonText);
							}
						});
						e.preventDefault();
					});
					checkIfImageResizable();
				});
				$(window).resize(function() {
					checkIfImageResizable();
				});
				function checkIfImageResizable() {
					var imageElement = $(".wallpaperdld>img");
					if ($(window).width() < ' . ($wallpaper->getWidth() + 270) . ') {
						if (imageElement.css("width") == "' . $wallpaper->getWidth() . 'px") {
							imageElement.removeClass("resizableZoomIn");
							imageElement.addClass("resizableZoomOut");
						} else {
							imageElement.removeClass("resizableZoomOut");
							imageElement.addClass("resizableZoomIn");
						}
						imageElement.addClass("resizable");
					} else {
						imageElement.removeClass("resizable resizableZoomIn resizableZoomOut");
					}
				}';

				$html = '		<div id="content"><div>' . "\n";
				$html .= '			<h1>' . Format::htmlEntities($wallpaper->getName()) . '</h1>' . "\n";
				$html .= '			<div class="dldartists">' . "\n";
				$html .= '				<div class="dldartists-by">by</div>' . "\n";
				$html .= '				<div class="dldartists-list">' . $artists . '</div>' . "\n";
				$html .= '			</div>' . "\n";
				$aspects = $wallpaper->getAspectTags();
				$html .= '			<div class="dldsize">' . $wallpaper->getWidth() . 'x' . $wallpaper->getHeight() . (!empty($aspects) ? ' (' . Format::htmlEntities($aspects[0]->getName()) . ')' : '') . '</div>' . "\n";
				$html .= '				<div class="dldbuttons">' . "\n";
				$html .= '					<a class="download button" href="' . Format::htmlEntities($wallpaper->getImageLink()) . '?download=1">Download</a>';
				if ($wallpaper->getUrl() !== '') {
					$html .= '					<a class="button" style="margin-left:2px;" href="' . $wallpaper->getUrl() . '" target="_blank">Visit author\'s website</a>';
				}
				if (!$user->getIsAnonymous()) {
					$isFav = $wallpaper->getIsFavourite($user->getId());
					$html .= '					<a style="margin-left:2px;" class="button favourite fav_active" data-wallpaperid="'.$wallpaper->getId().'" id="fav_a_' . $wallpaper->getId() . '" href="#fav">' . ($isFav ? 'Remove from favs' : 'Add to favourites') . '</a>' . "\n";
				} else {
					$html .= '					<a style="margin-left:2px;" class="button favourite fav_disabled" href="#fav">Login to favourite</a>' . "\n";
				}

				$html .= '				</div>' . "\n";
				$html .= '			<div style="clear:both;"></div>' . "\n";
				$html .= '			<div class="dldtags">' . "\n";

				$html .= '				<h2>Tags</h2>' . "\n";
				$tags = $wallpaper->getBasicTags();
				foreach ($tags as $tag) {
					$class = '';
					if ($tag->getType() == Tag::TAG_TYPE_CHARACTER) {
						$class = 'tagtype_character';
					} elseif ($tag->getType() == Tag::TAG_TYPE_STYLE) {
						$class = 'tagtype_style';
					}
					$html .= '				<a href="' . PUB_PATH_CAT . '?search=' . urlencode($tag->getName()) . '"' . ($class != '' ? ' class="' . $class . '"' : '') . '>' . Format::htmlEntities($tag->getName()) . '</a><br />' . "\n";
				}

				$html .= '				<h2>Platform</h2>' . "\n";
				$tags = $wallpaper->getPlatformTags();
				foreach ($tags as $tag) {
					$html .= '				<a href="' . PUB_PATH_CAT . '?search=' . urlencode($tag->getName()) . '">' . Format::htmlEntities($tag->getName()) . '</a><br />' . "\n";
				}

				$html .= '				<h2>Information</h2>' . "\n";
				$html .= '				<span><label>Clicks:</label> '.$wallpaper->getClicks().'</span><br />';
				$html .= '				<span><label>Favs:</label> <span id="fav_count_' . $wallpaper->getId() . '">'.$wallpaper->getFavourites().'</span></span><br />';

				$html .= '			</div>' . "\n";
				$html .= '			<div class="dldimage">' . "\n";
				$html .= '				<div class="wallpaperdld">' . "\n";
				$html .= '					<img src="' . Format::htmlEntities($wallpaper->getImageLink()) . '" alt="' . Format::htmlEntities($wallpaper->getName()) . ' by ' . $artists_header . '" onclick="download_image_enlarge(this, ' . $wallpaper->getWidth() . ');" />' . "\n";
				$html .= '				</div>' . "\n";
				$html .= '			</div>' . "\n";

				$html .= '		</div></div>' . "\n";

				$meta = "\n" . '		<meta name="twitter:card" content="photo" />' . "\n";
				$meta .= '		<meta name="twitter:image:src" content="' . Format::htmlEntities($wallpaper->getImageThumbnailLink(2)) . '" />' . "\n";
			} else {
				header('Location: ' . PUB_PATH_CAT);
			}
		}
	}
}
if ($html == '') {
	require_once(ROOT_DIR . 'pages/errors/404.php');
} else {
	$downloadPage->setHtml($html);
	$downloadPage->setMeta($meta);
	$downloadPage->setJavascript($javaScript);

	$response = new Response($downloadPage);
	$response->output();
}