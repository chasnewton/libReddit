<?php
require_once "../User.php";
require_once "../Message.php";
require_once "../Messages.php";

$user = new User("user", "pass");
$msg = new Messages($user);
$prop = $user->getProp();
$msg->sendMessage(new Message(new User("destination"), $user, "subject", "body"));

?>
