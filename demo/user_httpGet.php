<?php
require_once("User.php");
$user = new User();
print_r($user->httpGet("http://reddit.com/"));
?>