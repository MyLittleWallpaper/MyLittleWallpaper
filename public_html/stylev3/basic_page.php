<?php

declare(strict_types=1);

global $response;

echo '<div class="basic_page_container">';
echo $response->getResponseVariables()->html;
echo '</div>';
