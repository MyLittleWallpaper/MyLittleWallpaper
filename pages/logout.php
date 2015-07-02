<?php
// Check that correct entry point was used
if (!defined('INDEX')) exit();

$session->logUserOut();
header('Location: '.PUB_PATH_CAT);