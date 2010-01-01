<?php
require_once("User.php");
$user = new User();
print_r($user->httpPost("http://reddit.com/", "name=value"));
?>