<?php
require_once("User.php");
$user = new User("name");
print_r($user->getProp());
?>