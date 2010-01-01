<?php
require_once("User.php");
$user = new User("name");
echo $user->getUsername();
?>