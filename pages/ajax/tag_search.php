<?php
// Check that correct entry point was used
if (!defined('INDEX')) exit();

require_once(ROOT_DIR . 'classes/output/BasicJSON.php');

$return = Array();
$srch = (!empty($_GET['term']) ? "%".$_GET['term']."%" : '');
$srch2 = (!empty($_GET['term']) ? $_GET['term']."%" : '');
$assigned = Array(
	$srch,
	$srch,
	$srch,
	$srch,
	$srch,
	$srch,
	$srch2,
	$srch2,
);

$sql = "(SELECT name, '' previous, alternate FROM tag WHERE name LIKE ? OR alternate LIKE ?)
UNION ALL
(SELECT CONCAT('author:', name) name, oldname previous, '' alternate FROM tag_artist WHERE (CONCAT('author:', name) LIKE ? OR CONCAT('author:', oldname) LIKE ?) AND deleted = 0)
UNION ALL
(SELECT CONCAT('aspect:', name) name, '' previous, '' alternate FROM tag_aspect WHERE CONCAT('aspect:', name) LIKE ?)
UNION ALL
(SELECT CONCAT('platform:', name) name, '' previous, '' alternate FROM tag_platform WHERE CONCAT('platform:', name) LIKE ?)
UNION ALL
(SELECT CONCAT('=', name) name, '' previous, alternate FROM tag WHERE (CONCAT('=', name) LIKE ? OR CONCAT('=', alternate) LIKE ?) AND type = 'character')

ORDER BY name
LIMIT 50";

$result = $db->query($sql, $assigned);
while($row = $result->fetch(PDO::FETCH_ASSOC)) {
	if (!empty($row['previous'])) $desc = 'Formerly known as <b>'.Format::htmlEntities($row['previous']).'</b>';
	elseif (!empty($row['alternate'])) $desc = 'Also known as <b>'.Format::htmlEntities($row['alternate']).'</b>';
	else $desc = '';
	$return[] = Array(
		'id' => $row['name'],
		'label' => $row['name'],
		'value' => $row['name'],
		'desc' => $desc,
	);
}

$tagsearchresult = new BasicJSON($return);

$response = new Response($tagsearchresult);
$response->output();