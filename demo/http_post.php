<?php
require_once("HTTP.php");
$http = new HTTP();
print_r($http->post("http://reddit.com/", "name=value"));
?>