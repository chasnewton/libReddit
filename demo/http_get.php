<?php
require_once("HTTP.php");
$http = new HTTP();
print_r($http->get("http://reddit.com/"));
?>