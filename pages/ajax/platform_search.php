<?php
// Check that correct entry point was used
if (!defined('INDEX')) exit();

require_once(ROOT_DIR . 'classes/output/BasicJSON.php');

$return = [];

$sql = "SELECT name FROM tag_platform WHERE name LIKE ? ORDER BY name LIMIT 50";
$srch = (!empty($_GET['term']) ? "%".$_GET['term']."%" : '');
$result = $db->query($sql, Array($srch));
while($row = $result->fetch(PDO::FETCH_ASSOC)) {
	$return[] = Array(
		'id' => $row['name'],
		'label' => $row['name'],
		'value' => $row['name']
	);
}

$searchResult = new BasicJSON($return);

$response = new Response($searchResult);
$response->output();
