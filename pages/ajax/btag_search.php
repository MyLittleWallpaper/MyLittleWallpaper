<?php
// Check that correct entry point was used
if (!defined('INDEX')) exit();

require_once(ROOT_DIR . 'classes/output/BasicJSON.php');

$return = [];

$sql = "SELECT name, alternate FROM tag WHERE name LIKE ? OR alternate LIKE ? ORDER BY name LIMIT 50";
$srch = (!empty($_GET['term']) ? "%".$_GET['term']."%" : '');
$result = $db->query($sql, Array($srch, $srch));
while($row = $result->fetch(PDO::FETCH_ASSOC)) {
	if (!empty($row['alternate'])) $desc = 'Also known as <b>'.Format::htmlEntities($row['alternate']).'</b>';
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