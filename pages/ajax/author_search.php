<?php
// Check that correct entry point was used
if (!defined('INDEX')) exit();

require_once(ROOT_DIR . 'classes/output/BasicJSON.php');

$return = [];

$sql = "SELECT name, oldname FROM tag_artist WHERE (name LIKE ? OR oldname LIKE ?) AND deleted = 0 ORDER BY name LIMIT 50";
$searchString = (!empty($_GET['term']) ? "%".$_GET['term']."%" : '');
$result = $db->query($sql, Array($searchString, $searchString));
while($row = $result->fetch(PDO::FETCH_ASSOC)) {
	if (!empty($row['oldname'])) $desc = 'Formerly known as <b>'.Format::htmlEntities($row['oldname']).'</b>';
	else $desc = '';

	$return[] = Array(
		'id' => $row['name'],
		'label' => $row['name'],
		'value' => $row['name'],
		'desc' => $desc,
	);
}

$searchResult = new BasicJSON($return);

$response = new Response($searchResult);
$response->output();