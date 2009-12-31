<?php
require_once("User.php");
$user = new User("name", "pass");
$anotheruser = new User("name");
$anonuser = new User();
?>