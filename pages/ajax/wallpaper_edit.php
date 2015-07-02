<?php
// Check that correct entry point was used
if (!defined('INDEX')) exit();
global $user;

require_once(ROOT_DIR . 'classes/output/BasicJSON.php');

$return = Array('success' => 0, 'error' => 'Wallpaper not found.');

$banned = FALSE;
$sql = "SELECT ip FROM ban WHERE ip = ?";
$result = $db->query($sql, Array(USER_IP));
while($row = $result->fetch(PDO::FETCH_ASSOC)) {
	$banned = TRUE;
}

if ($banned) {
	$return['error'] = 'Your IP is on the blacklist.';
} else {
	if (!empty($_POST['id'])) {
		$sql = "SELECT id, name, url, width, height, no_resolution FROM wallpaper WHERE id = ? LIMIT 1";
		$result = $db->query($sql, Array($_POST['id']));
		while($row = $result->fetch(PDO::FETCH_ASSOC)) {
			unset($return['error']);
			if ($user->getIsAnonymous()) {
				$resp = recaptcha_check_answer (RECAPTCHA_PRIVATE, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
				if (!$resp->is_valid) {
					$return['error'] = 'Invalid captcha.';
				}
			}
			if (empty($return['error'])) {
				$notchanged = TRUE;
				
				$newauthors = Array();
				$authorstmp = explode(',', $_POST['author']);
				foreach($authorstmp as $k => $v) {
					if (trim($v) != '') {
						$newauthors[mb_strtolower(trim($v), 'utf-8')] = trim($v);
					}
				}
				unset($authorstmp);
				asort($newauthors);
				$cleanauthors = implode(', ', $newauthors);
				
				$newtags = Array();
				$tagstmp = explode(',', $_POST['tags']);
				foreach($tagstmp as $k => $v) {
					if (trim($v) != '') {
						$newtags[mb_strtolower(trim($v), 'utf-8')] = trim($v);
					}
				}
				unset($tagstmp);
				asort($newtags);
				$cleantags = implode(', ', $newtags);
				
				$newplatforms = Array();
				$platformstmp = explode(',', $_POST['platform']);
				foreach($platformstmp as $k => $v) {
					if (trim($v) != '') {
						$newplatforms[mb_strtolower(trim($v), 'utf-8')] = trim($v);
					}
				}
				unset($platformstmp);
				asort($newplatforms);
				$cleanplatforms = implode(', ', $newplatforms);
				
				if (trim($_POST['name']) == '' || empty($newauthors) || empty($newtags) || empty($newplatforms)) {
					$return['error'] = 'Please fill all the fields.';
				} else {
					if (strcmp(trim($row['name']), trim($_POST['name'])) !== 0) $notchanged = FALSE;
					if (strcmp(trim($row['url']), trim($_POST['url'])) !== 0) $notchanged = FALSE;
					
					$tags = Array();
					$sql = "SELECT t.name FROM tag t JOIN wallpaper_tag wt ON (t.id = wt.tag_id) WHERE wt.wallpaper_id = ? ORDER BY t.name";
					$res = $db->query($sql, Array($row['id']));
					while($tag = $res->fetch(PDO::FETCH_ASSOC)) {
						$tags[] = mb_strtolower($tag['name'], 'utf-8');
					}
					
					$authors = Array();
					$sql = "SELECT t.name FROM tag_artist t JOIN wallpaper_tag_artist wt ON (t.id = wt.tag_artist_id) WHERE wt.wallpaper_id = ? ORDER BY t.name";
					$res = $db->query($sql, Array($row['id']));
					while($tag = $res->fetch(PDO::FETCH_ASSOC)) {
						$authors[] = mb_strtolower($tag['name'], 'utf-8');
					}
					
					$platforms = Array();
					$sql = "SELECT t.name FROM tag_platform t JOIN wallpaper_tag_platform wt ON (t.id = wt.tag_platform_id) WHERE wt.wallpaper_id = ? ORDER BY t.name";
					$res = $db->query($sql, Array($row['id']));
					while($tag = $res->fetch(PDO::FETCH_ASSOC)) {
						$platforms[] = mb_strtolower($tag['name'], 'utf-8');
					}
					
					foreach($authors as $k => $v) {
						if (!empty($newauthors[$v])) {
							unset($authors[$k]);
							unset($newauthors[$v]);
						}
					}
					if (!empty($authors) || !empty($newauthors)) $notchanged = FALSE;

					foreach($tags as $k => $v) {
						if (!empty($newtags[$v])) {
							unset($tags[$k]);
							unset($newtags[$v]);
						}
					}
					if (!empty($tags) || !empty($newtags)) $notchanged = FALSE;
					
					foreach($platforms as $k => $v) {
						if (!empty($newplatforms[$v])) {
							unset($platforms[$k]);
							unset($newplatforms[$v]);
						}
					}
					if (!empty($platforms) || !empty($newplatforms)) $notchanged = FALSE;
					if ($user->getIsAdmin()) {
						if ((!empty($_POST['no_resolution']) && $_POST['no_resolution'] == '1' && $row['no_resolution'] == '0') || (empty($_POST['no_resolution']) && $row['no_resolution'])) $notchanged = FALSE;
					}
					if ($notchanged) {
						$return['error'] = 'Please change some information before submitting.';
					} else {
						if ($user->getIsAdmin()) {
							$saveauthor = '';
							$authorlist = explode(',', $_POST['author']);
							$author_array = Array();
							foreach($authorlist as $tag) {
								$tag = trim($tag);
								if (str_replace(' ', '', $tag) != '') {
									$res = $db->query("SELECT id, name FROM tag_artist WHERE name = ?", Array($tag));
									$found = false;
									while($rivi = $res->fetch(PDO::FETCH_ASSOC)) {
										$found = true;
										$author_array[] = $rivi['id'];
										if ($saveauthor == '') $saveauthor = $tag;
									}
									if (!$found) {
										$author_array[] = $db->saveArray('tag_artist', Array('name' => $tag));
									}
								}
							}
							
							$data = Array(
								'name' => $_POST['name'],
								'url' => $_POST['url'],
								'no_resolution' => (!empty($_POST['no_resolution']) && $_POST['no_resolution'] == '1' ? 1 : 0),
							);
								
							$olddata = Array();
							$olddata['name'] = $row['name'];
							$olddata['tags'] = '';
							$sql = "SELECT t.name FROM tag t JOIN wallpaper_tag wt ON (t.id = wt.tag_id) WHERE wt.wallpaper_id = ? ORDER BY t.name";
							$res = $db->query($sql, Array($row['id']));
							while($tag = $res->fetch(PDO::FETCH_ASSOC)) {
								$olddata['tags'] .= $tag['name'];
								$olddata['tags'] .= ', ';
							}
							$olddata['author'] = '';
							$sql = "SELECT t.name FROM tag_artist t JOIN wallpaper_tag_artist wt ON (t.id = wt.tag_artist_id) WHERE wt.wallpaper_id = ? ORDER BY t.name";
							$res = $db->query($sql, Array($row['id']));
							while($tag = $res->fetch(PDO::FETCH_ASSOC)) {
								$olddata['author'] .= $tag['name'];
								$olddata['author'] .= ', ';
							}
							$olddata['platform'] = '';
							$sql = "SELECT t.name FROM tag_platform t JOIN wallpaper_tag_platform wt ON (t.id = wt.tag_platform_id) WHERE wt.wallpaper_id = ? ORDER BY t.name";
							$res = $db->query($sql, Array($row['id']));
							while($tag = $res->fetch(PDO::FETCH_ASSOC)) {
								$olddata['platform'] .= $tag['name'];
								$olddata['platform'] .= ', ';
							}
							$olddata['url'] = $row['url'];
							$historydata = Array(
								'wallpaper_id' => $row['id'],
								'user_id' => (!$user->getIsAnonymous() ? $user->getId() : 0),
								'time' => gmdate('Y-m-d H:i:s'),
								'data_before' => serialize($olddata),
							);
							$db->saveArray('wallpaper_history', $historydata);

							$db->query("DELETE FROM wallpaper_tag WHERE wallpaper_id = ?", Array($row['id']));
							$db->query("DELETE FROM wallpaper_tag_artist WHERE wallpaper_id = ?", Array($row['id']));
							$db->query("DELETE FROM wallpaper_tag_platform WHERE wallpaper_id = ?", Array($row['id']));
							$db->query("DELETE FROM wallpaper_tag_aspect WHERE wallpaper_id = ?", Array($row['id']));
							
							$imageid = $db->saveArray('wallpaper', $data, $row['id']);
							
							foreach($author_array as $auth) {
								$data = Array(
									'tag_artist_id' => $auth,
									'wallpaper_id' => $imageid,
								);
								$db->saveArray('wallpaper_tag_artist', $data);
							}
							$taglist = explode(',', $_POST['tags']);
							
							foreach($taglist as $tag) {
								$tag = trim($tag);
								if (str_replace(' ', '', $tag) != '') {
									$res = $db->query("SELECT id, name, type FROM tag WHERE name = ?", Array($tag));
									while($rivi = $res->fetch(PDO::FETCH_ASSOC)) {
										$data = Array(
											'tag_id' => $rivi['id'],
											'wallpaper_id' => $imageid,
										);
										$db->saveArray('wallpaper_tag', $data);
									}
								}
							}
							
							$fields = Array(Array('table' => 'tag', 'field' => 'id'));
							$join = Array(
								Array(
									'table' => 'wallpaper_tag',
									'condition' => Array(
										Array(
											Array(
												'table' => 'wallpaper_tag',
												'field' => 'tag_id',
											),
											Array(
												'table' => 'tag',
												'field' => 'id',
											),
										),
									),
								),
							);
							$conditions = Array();
							$conditions[] = Array(
								'table' => 'wallpaper_tag',
								'field' => 'wallpaper_id',
								'value' => $imageid,
								'operator' => '=',
							);
							$conditions[] = Array(
								'table' => 'tag',
								'field' => 'type',
								'value' => 'character',
								'operator' => '=',
							);
							$order = Array(Array('table' => 'tag', 'field' => 'name'));
							$taglist = $db->getList('tag', $fields, $conditions, $order, NULL, $join);
							$chartags = '';
							$ct_count = 0;
							foreach($taglist as $tag) {
								if ($chartags != '') $chartags .= ',';
								$chartags .= $tag['id'];
								$ct_count ++;
							}
							if ($ct_count < 16) {
								$savedata = Array('chartags' => $chartags);
								$db->saveArray('wallpaper', $savedata, $imageid);
							}
							
							$noaspect = FALSE;
							$platformlist = explode(',', $_POST['platform']);
							foreach($platformlist as $tag) {
								$tag = trim($tag);
								if (str_replace(' ', '', $tag) != '') {
									$res = $db->query("SELECT id, name FROM tag_platform WHERE name = ?", Array($tag));
									while($rivi = $res->fetch(PDO::FETCH_ASSOC)) {
										if ($rivi['name'] == 'Mobile') {
											$db->saveArray('wallpaper', Array('no_aspect' => 1), $imageid);
											$noaspect = TRUE;
										}
										$data = Array(
											'tag_platform_id' => $rivi['id'],
											'wallpaper_id' => $imageid,
										);
										$db->saveArray('wallpaper_tag_platform', $data);
									}
								}
							}
							if (!$noaspect) {
								$aspect = aspect($row['width'], $row['height']);
								$res = $db->query("SELECT id, name FROM tag_aspect WHERE name = ?", Array($aspect));
								while($rivi = $res->fetch(PDO::FETCH_ASSOC)) {
									$data = Array(
										'tag_aspect_id' => $rivi['id'],
										'wallpaper_id' => $imageid,
									);
									$db->saveArray('wallpaper_tag_aspect', $data);
								}
							}
							$return['novalidate'] = 1;
						} else {
							$savedata = Array(
								'user_id' => (!$user->getIsAnonymous() ? $user->getId() : 0),
								'wallpaper_id' => $row['id'],
								'name' => $_POST['name'],
								'author' => $cleanauthors,
								'tags' => $cleantags,
								'platform' => $cleanplatforms,
								'url' => $_POST['url'],
								'reason' => $_POST['reason'],
								'ip' => USER_IP,
							);
							$db->saveArray('wallpaper_edit', $savedata);
						}
						$return['success'] = 1;
					}
				}
			}
		}
	}
}

$wallpaperEditResult = new BasicJSON($return);

$response = new Response($wallpaperEditResult);
$response->output();