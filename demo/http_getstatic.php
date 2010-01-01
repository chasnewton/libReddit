<?php
require_once("HTTP.php");
$response = HTTP::_get("http://reddit.com/");
print_r($response);
?>