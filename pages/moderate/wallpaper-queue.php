<?php

declare(strict_types=1);

global $user, $category_repository;

use MyLittleWallpaper\classes\Format;
use MyLittleWallpaper\classes\GetCommonColours;
use MyLittleWallpaper\classes\output\BasicPage;
use MyLittleWallpaper\classes\Response;

const ACTIVE_PAGE = 'wallpaper-queue';

$wallpaperQueuePage = new BasicPage();
$wallpaperQueuePage->setPageTitleAddition('Submitted wallpapers');

$notFound = false;
if ($user->getIsAdmin()) {
    if (isset($_POST['name'])) {
        if (!empty($_POST['name']) && !empty($_POST['author'])) {
            $sql           = "SELECT * FROM wallpaper_submit WHERE discarded = 0 AND id = ? ORDER BY id LIMIT 1";
            $data          = [$_POST['id']];
            $res           = $db->query($sql, $data);
            $wallpaperData = [];
            while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
                $wallpaperData = $row;
            }
            if (!empty($wallpaperData)) {
                if (
                    rename(
                        ROOT_DIR . FILE_FOLDER . 'moderate/' . $wallpaperData['file'],
                        ROOT_DIR . FILE_FOLDER . $wallpaperData['file']
                    )
                ) {
                    $saveAuthor   = '';
                    $authorList   = explode(',', $_POST['author']);
                    $author_array = [];
                    foreach ($authorList as $tag) {
                        $tag = trim($tag);
                        if (str_replace(' ', '', $tag) != '') {
                            $res   = $db->query("SELECT id, name FROM tag_artist WHERE name = ?", [$tag]);
                            $found = false;
                            while ($rivi = $res->fetch(PDO::FETCH_ASSOC)) {
                                $found          = true;
                                $author_array[] = $rivi['id'];
                                if ($saveAuthor == '') {
                                    $saveAuthor = $tag;
                                }
                            }
                            if (!$found) {
                                $author_array[] = $db->saveArray('tag_artist', ['name' => $tag]);
                            }
                        }
                    }

                    $data    = [
                        'submitter_id'     => $wallpaperData['user_id'],
                        'name'             => $_POST['name'],
                        'url'              => $_POST['url'],
                        'file'             => $wallpaperData['file'],
                        'filename'         => $wallpaperData['filename'],
                        'width'            => $wallpaperData['width'],
                        'height'           => $wallpaperData['height'],
                        'mime'             => $wallpaperData['mime'],
                        'timeadded'        => time(),
                        'no_resolution'    => (!empty($_POST['no_resolution']) &&
                        $_POST['no_resolution'] == '1' ? 1 : 0),
                        'direct_with_link' => 1,
                        'status_check'     => '200',
                        'last_checked'     => gmdate('Y-m-d H:i:s'),
                        'series'           => $_POST['series'],
                    ];
                    $imageId = $db->saveArray('wallpaper', $data);
                    foreach ($author_array as $auth) {
                        $data = [
                            'tag_artist_id' => $auth,
                            'wallpaper_id'  => $imageId,
                        ];
                        $db->saveArray('wallpaper_tag_artist', $data);
                    }
                    $tagList = explode(',', $_POST['tags']);
                    foreach ($tagList as $tag) {
                        $tag = trim($tag);
                        if (str_replace(' ', '', $tag) != '') {
                            $res = $db->query("SELECT id, name FROM tag WHERE name = ?", [$tag]);
                            while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
                                $data = [
                                    'tag_id'       => $row['id'],
                                    'wallpaper_id' => $imageId,
                                ];
                                $db->saveArray('wallpaper_tag', $data);
                            }
                        }
                    }

                    $fields        = [['table' => 'tag', 'field' => 'id']];
                    $join          = [
                        [
                            'table'     => 'wallpaper_tag',
                            'condition' => [
                                [
                                    [
                                        'table' => 'wallpaper_tag',
                                        'field' => 'tag_id',
                                    ],
                                    [
                                        'table' => 'tag',
                                        'field' => 'id',
                                    ],
                                ],
                            ],
                        ],
                    ];
                    $conditions    = [];
                    $conditions[]  = [
                        'table'    => 'wallpaper_tag',
                        'field'    => 'wallpaper_id',
                        'value'    => $imageId,
                        'operator' => '=',
                    ];
                    $conditions[]  = [
                        'table'    => 'tag',
                        'field'    => 'type',
                        'value'    => 'character',
                        'operator' => '=',
                    ];
                    $order         = [['table' => 'tag', 'field' => 'name']];
                    $tagList       = $db->getList('tag', $fields, $conditions, $order, null, $join);
                    $characterTags = '';
                    $ct_count      = 0;
                    foreach ($tagList as $tag) {
                        if ($characterTags != '') {
                            $characterTags .= ',';
                        }
                        $characterTags .= $tag['id'];
                        $ct_count++;
                    }
                    if ($ct_count < 16) {
                        $saveData = ['chartags' => $characterTags];
                        $db->saveArray('wallpaper', $saveData, $imageId);
                    }

                    $noAspect     = false;
                    $platformList = explode(',', $_POST['platform']);
                    foreach ($platformList as $tag) {
                        $tag = trim($tag);
                        if (str_replace(' ', '', $tag) != '') {
                            $res = $db->query("SELECT id, name FROM tag_platform WHERE name = ?", [$tag]);
                            while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
                                if ($row['name'] == 'Mobile') {
                                    $db->saveArray('wallpaper', ['no_aspect' => 1], $imageId);
                                    $noAspect = true;
                                }
                                $data = [
                                    'tag_platform_id' => $row['id'],
                                    'wallpaper_id'    => $imageId,
                                ];
                                $db->saveArray('wallpaper_tag_platform', $data);
                            }
                        }
                    }
                    $colours       = new GetCommonColours();
                    $coloursResult = $colours->getColours(ROOT_DIR . FILE_FOLDER . $wallpaperData['file']);

                    foreach ($coloursResult as $cl) {
                        $colours  = array_keys($cl['colours']);
                        $col      = $colours[0];
                        $amount   = $cl['percent'];
                        $tag_r    = base_convert(substr((string)$col, 0, 2), 16, 10);
                        $tag_g    = base_convert(substr((string)$col, 2, 2), 16, 10);
                        $tag_b    = base_convert(substr((string)$col, 4, 2), 16, 10);
                        $saveData = [
                            'wallpaper_id' => $imageId,
                            'tag_r'        => $tag_r,
                            'tag_g'        => $tag_g,
                            'tag_b'        => $tag_b,
                            'tag_colour'   => $col,
                            'amount'       => round($amount, 2),
                        ];
                        $db->saveArray('wallpaper_tag_colour', $saveData);
                    }

                    if (!$noAspect) {
                        $aspect = $wallpaperData['aspect'];
                        $res    = $db->query("SELECT id, name FROM tag_aspect WHERE name = ?", [$aspect]);
                        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
                            $data = [
                                'tag_aspect_id' => $row['id'],
                                'wallpaper_id'  => $imageId,
                            ];
                            $db->saveArray('wallpaper_tag_aspect', $data);
                        }
                    }
                    $db->query("DELETE FROM wallpaper_submit WHERE id = ?", [$wallpaperData['id']]);
                }
                header('Location: ' . PUB_PATH_CAT . 'moderate/wallpaper-queue');
            } else {
                $notFound = true;
            }
        }
    }
}

$pageContents = '<div id="content"><div>';
$pageContents .= '<h1>Submitted wallpapers</h1>';
$javaScript   = '';
if ($user->getIsAdmin()) {
    $javaScript .= '$(document).ready(function(){' . "\n";
    if ($notFound) {
        $javaScript .= '	$("#error_dialog span.dialogtext")' .
            '.html("Wallpaper not found. Maybe someone else accepted or rejected it already.");' .
            "\n";
    }
    $javaScript .= '	$("#error_dialog").dialog({
			' . (!$notFound ? 'autoOpen: false,' : '') . '
			modal: true,
			resizable: false,
			buttons: [
				{
					text: "Ok",
					click: function() {
						$(this).dialog("close");
					}
				}
			],
			close: function() {
				window.location.href = "' . PUB_PATH_CAT . 'moderate/wallpaper-queue";
			}
		});
	});';

    $pageContents  .= '<div id="error_dialog" title="Error" style="display:none;">' .
        '<p style="font-size:12px;"><span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 20px 0;">' .
        '</span><span class="dialogtext"></span></p></div>';
    $sql           = "SELECT * FROM wallpaper_submit WHERE discarded = ? ORDER BY id LIMIT 1";
    $data          = [0];
    $res           = $db->query($sql, $data);
    $wallpaperData = [];
    while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
        $wallpaperData = $row;
    }
    if (!empty($wallpaperData)) {
        $theUrl           = '';
        $authorList       = explode(',', $wallpaperData['author']);
        $author_array     = [];
        $new_author_array = [];
        foreach ($authorList as $tag) {
            $tag = trim($tag);
            if (str_replace(' ', '', $tag) != '') {
                $res   = $db->query("SELECT id, name FROM tag_artist WHERE name = ?", [$tag]);
                $found = false;
                while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
                    $found          = true;
                    $author_array[] = $row['name'];
                }
                if (!$found) {
                    $new_author_array[] = $tag;
                }
            }
        }

        $tagList       = explode(',', $wallpaperData['tags']);
        $tag_array     = [];
        $new_tag_array = [];
        foreach ($tagList as $tag) {
            $tag = trim($tag);
            if (str_replace(' ', '', $tag) != '') {
                $res   = $db->query("SELECT id, name FROM tag WHERE name = ?", [$tag]);
                $found = false;
                while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
                    $found       = true;
                    $tag_array[] = $row['name'];
                }
                if (!$found) {
                    $new_tag_array[] = $tag;
                }
            }
        }

        $url = $wallpaperData['url'];
        if (preg_match("/^http:\\/\\/[^.]*\\.deviantart\\.com\\/art\\/.*$/", $url)) {
            $theUrl = $url;
        } elseif (
            preg_match("/^http:\\/\\/[^.]*\\.deviantart\\.com\\/.*\\/d.*$/", $url) ||
            preg_match("/^http:\\/\\/fav\\.me\\/.*$/", $url)
        ) {
            if (preg_match("/^http:\\/\\/[^.]*\\.deviantart\\.com\\/.*\\/d.*$/", $url)) {
                $url = preg_replace('/^http:\/\/[^.]*\.deviantart\.com\/.*\/(d.*$)/', 'http://fav.me/$1', $url);
            }
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
            $header = "Location: ";
            $pos    = strpos($response, $header);
            if ($pos !== false) {
                $pos    += strlen($header);
                $theUrl = substr($response, $pos, strpos($response, "\r\n", $pos) - $pos);
            }
        }

        // Check if found on the database
        $foundWallpapers = [];
        if ($theUrl == '') {
            if (!empty($wallpaperData['url'])) {
                $sim_res = $db->query(
                    "SELECT file FROM `wallpaper` WHERE deleted = 0 AND url = ?",
                    [$wallpaperData['url']]
                );
                while ($inDbRow = $sim_res->fetch(PDO::FETCH_ASSOC)) {
                    $foundWallpapers[] = $inDbRow['file'];
                }
            }
        } else {
            $id      = preg_replace("/^http:\\/\\/[^.]*\\.deviantart\\.com\\/art\\/.*-([0-9]*?)$/", "$1", $theUrl);
            $sim_res = $db->query(
                "SELECT file FROM `wallpaper` WHERE deleted = 0 AND url LIKE ?",
                ['http://%.deviantart.com/art/%-' . $id]
            );
            while ($inDbRow = $sim_res->fetch(PDO::FETCH_ASSOC)) {
                $foundWallpapers[] = $inDbRow['file'];
            }
        }
        $javaScript   .= '
		$(document).ready(function(){
			$("#tags").bind("keydown", function(event) {
				if (event.keyCode === $.ui.keyCode.TAB && $(this).data("autocomplete").menu.active) {
					event.preventDefault();
				}
			}).autocomplete({
				source: function(request, response) {
					$.getJSON("' . PUB_PATH_CAT . 'ajax/btag_search", {
						term: extractLast(request.term)
					}, response);
				},
				search: function() {
					var term = extractLast(this.value);
					if (term.length < 2) {
						return false;
					}
				},
				focus: function() {
					return false;
				},
				select: function(event, ui) {
					var terms = split(this.value);
					terms.pop();
					terms.push(ui.item.value);
					terms.push("");
					this.value = terms.join(", ");
					return false;
				}
			}).autocomplete("instance")._renderItem = function(ul, item) {
				if (item.desc != "") {
					return $("<li>")
						.data("item.autocomplete", item)
						.append("<a style=\"line-height:1.1;\">" + item.label + "<br>"+
						  "<span class=\"autocomplete_extra\">" + item.desc + "</span></a>")
						.appendTo(ul);
				} else {
					return $("<li>")
						.data("item.autocomplete", item)
						.append("<a>" + item.label + "</a>")
						.appendTo(ul);
				}
			};
			$("#author").bind("keydown", function(event) {
				if (event.keyCode === $.ui.keyCode.TAB && $(this).data("autocomplete").menu.active) {
					event.preventDefault();
				}
			}).autocomplete({
				source: function(request, response) {
					$.getJSON("' . PUB_PATH_CAT . 'ajax/author_search", {
						term: extractLast(request.term)
					}, response);
				},
				search: function() {
					var term = extractLast(this.value);
					if (term.length < 2) {
						return false;
					}
				},
				focus: function() {
					return false;
				},
				select: function(event, ui) {
					var terms = split(this.value);
					terms.pop();
					terms.push(ui.item.value);
					terms.push("");
					this.value = terms.join(", ");
					return false;
				}
			}).autocomplete("instance")._renderItem = function(ul, item) {
				if (item.desc != "") {
					return $("<li>")
						.data("item.autocomplete", item)
						.append("<a style=\"line-height:1.1;\">" + item.label + "<br>" +
						  "<span class=\"autocomplete_extra\">" + item.desc + "</span></a>")
						.appendTo(ul);
				} else {
					return $("<li>")
						.data("item.autocomplete", item)
						.append("<a>" + item.label + "</a>")
						.appendTo(ul);
				}
			};
			$("#platform").bind("keydown", function(event) {
				if (event.keyCode === $.ui.keyCode.TAB && $(this).data("autocomplete").menu.active) {
					event.preventDefault();
				}
			}).autocomplete({
				source: function(request, response) {
					$.getJSON("' . PUB_PATH_CAT . 'ajax/platform_search", {
						term: extractLast(request.term)
					}, response);
				},
				search: function() {
					var term = extractLast(this.value);
					if (term.length < 2) {
						return false;
					}
				},
				focus: function() {
					return false;
				},
				select: function(event, ui) {
					var terms = split(this.value);
					terms.pop();
					terms.push(ui.item.value);
					terms.push("");
					this.value = terms.join(", ");
					return false;
				}
			});
			$("#deny_dialog").dialog({
				autoOpen: false,
				modal: true,
				resizable: false,
				width: 400,
				buttons: [
					{
						text: "Ok",
						click: function() {
							$.ajax({
								url: "' . PUB_PATH . 'ajax/denysubmission?id=' . $wallpaperData['id'] .
                                  '&reason=" + encodeURIComponent($("#denyreason option:selected").val()),
								success: function(data) {
									if (data.result != "OK") {
										if (data.result == "Not found") {
											$("#error_dialog span.dialogtext")
											  .html("Wallpaper not found. " +
											    "Maybe someone else accepted or rejected it already.");
										} else {
											$("#error_dialog span.dialogtext").html("Permission denied.");
										}
										$("#deny_dialog").dialog("close");
										$("#error_dialog").dialog("open");
									} else {
										window.location.href = "' . PUB_PATH_CAT . 'moderate/wallpaper-queue";
									}
								}
							});
						}
					},
					{
						text: "Cancel",
						click: function() {
							$(this).dialog("close");
						}
					}
				]
			});
		});';
        $pageContents .= '<div id="deny_dialog" title="Deny submission" style="display:none;">' .
            '<p>Deny reason:<br /><br /><select id="denyreason">';
        $pageContents .= '<option value="quality">Wallpaper isn\'t good enough</option>';
        $pageContents .= '<option value="duplicate"' . (!empty($foundWallpapers) ? ' selected="selected"' : '') .
            '>Wallpaper already in database</option>';
        $pageContents .= '<option value="size"' .
            ($wallpaperData['width'] < 1366 || $wallpaperData['height'] < 768 ? ' selected="selected"' : '') .
            '>Wallpaper doesn\'t meet the size requirements</option>';
        $pageContents .= '<option value="transparent">Transparent PNG</option>';
        $pageContents .= '<option value="unknown"' .
            (empty($author_array) && trim($wallpaperData['url']) == '' ? ' selected="selected"' : '') .
            '>Unknown source / no author</option>';
        $pageContents .= '</select></p></div>';
        $pageContents .= '<form class="labelForm" style="padding:25px 0 0 0;" method="post" action="' . PUB_PATH_CAT .
            'moderate/wallpaper-queue" enctype="multipart/form-data" accept-charset="utf-8">';
        $pageContents .= '<input type="hidden" name="id" value="' . $wallpaperData['id'] . '" />';
        $pageContents .= sprintf(
            '<div><label>%s</label><input type="text" autocomplete="off" name="name" style="%s" value="%s"/></div>',
            'Title:',
            'width:300px;',
            !empty($_POST['name']) ? Format::htmlEntities($_POST['name']) : Format::htmlEntities(
                $wallpaperData['name']
            )
        );
        $pageContents .= '<div><label>Author(s):</label>';
        $pageContents .= sprintf(
            '<input type="text" autocomplete="off" name="author" id="author" style="%s" value="%s, " /></div>',
            'width:300px;',
            !empty($_POST['author']) ? Format::htmlEntities($_POST['author']) : Format::htmlEntities(
                implode(', ', $author_array)
            )
        );
        $pageContents .= '<div><label>Unknown author(s):</label>' .
            Format::htmlEntities(implode(', ', $new_author_array)) . '</div>';
        $pageContents .= '<div><label>Tags:</label>';
        $pageContents .= sprintf(
            '<input type="text" autocomplete="off" name="tags" id="tags" style="width:300px;" value="%s, " /></div>',
            !empty($_POST['tags']) ? Format::htmlEntities($_POST['tags']) : Format::htmlEntities(
                implode(', ', $tag_array)
            )
        );
        $pageContents .= '<div><label>Unknown tags:</label>' . Format::htmlEntities(implode(', ', $new_tag_array)) .
            '</div>';
        $pageContents .= '<div><label>Platform:</label>';
        $pageContents .= sprintf(
            '<input type="text" autocomplete="off" name="platform" id="platform" style="%s" value="%s" /></div>',
            'width:300px;',
            !empty($_POST['platform']) ? Format::htmlEntities($_POST['platform']) : 'Desktop, '
        );
        $pageContents .= '<div><label>Don\'t show resolution</label>';
        $pageContents .= sprintf(
            '<input type="checkbox" value="1" name="no_resolution" %s/></div>',
            !empty($_POST['no_resolution']) ? 'checked="checked" ' : ''
        );
        $pageContents .= '<div><label>Source URL:</label>';
        $pageContents .= sprintf(
            '<input type="text" autocomplete="off" name="url" style="%s" value="%s" /><br /></div>',
            'width:300px;',
            !empty($_POST['url']) ? Format::htmlEntities($_POST['url']) : Format::htmlEntities(
                $wallpaperData['url']
            )
        );
        $pageContents .= '<div><label>Resolution:</label>' . $wallpaperData['width'] . 'x' . $wallpaperData['height'] .
            '</div>';
        $pageContents .= '<div><label>Series:</label><select name="series">';

        $category_list = $category_repository->getCategoryList();
        foreach ($category_list as $category) {
            $pageContents .= '<option value="' . $category->getId() . '"' .
                ((int)$wallpaperData['series'] === $category->getId() ? ' selected="selected"' : '') . '>' .
                Format::htmlEntities($category->getName()) . '</option>';
        }
        $pageContents .= '</select></div>';
        if (!empty($foundWallpapers)) {
            $pageContents .= '<div style="width:100%;">' .
                '<label style="display:block;float:left;">Found wallpapers with the same URL:</label>' .
                '<span style="display:inline-block;overflow:auto;max-height:150px;white-space:nowrap;width:825px;">';
            foreach ($foundWallpapers as $foundWallpaper) {
                $pageContents .= '<img style="box-shadow:0 1px 4px #aaa;margin:3px;" src="' . PUB_PATH . 'image/r2_' .
                    urlencode($foundWallpaper) . '.jpg" alt="' . Format::htmlEntities($foundWallpaper) .
                    '" style="" /> ';
            }
            $pageContents .= '</span></div>';
        }
        $pageContents .= '<br /><input type="submit" value="Accept" name="accept" /> ' .
            '<input type="button" value="Deny" onclick="$(\'#deny_dialog\').dialog(\'open\');" />';
        $pageContents .= '</form>';
        $pageContents .= '<img style="margin-top:25px;" src="' . PUB_PATH_CAT . 'moderate/queue-image/' .
            urlencode($wallpaperData['file']) . '.jpg" alt="' . Format::htmlEntities($wallpaperData['name']) . '" />';
    } else {
        $pageContents .= '<p>No wallpapers in the queue.</p>';
    }
} else {
    $pageContents .= '<p>You do not have permission to access this page!</p>';
}
$pageContents .= '</div></div>';

$wallpaperQueuePage->setJavascript($javaScript);
$wallpaperQueuePage->setHtml($pageContents);

$response = new Response($wallpaperQueuePage);
$response->output();
