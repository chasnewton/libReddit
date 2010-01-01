<?php
require_once("HTTP.php");
$response = HTTP::_post("http://reddit.com/", "name=value");
print_r($response);
?>