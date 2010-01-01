<?php
require_once("Messages.php");
$from = new User("user", "pass");
$message = new Message(new User("sendto"), $from, "subject", "body");
?>