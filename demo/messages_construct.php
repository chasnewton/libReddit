<?php
require_once("Messages.php");
$user = new User("name", "pass");
$messages = new Messages($user);
?>