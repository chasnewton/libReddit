<?php
require_once("Messages.php");
$user = new User("name", "pass");
$msg = new Messages($user);
$last = null;
do {
	$pms = $msg->getMessages("messages", $last);
	foreach($pms as $p) {
		print "'{$p->subject}' from {$p->author->getUsername()}: {$p->body}<br>\n";
	}
	$last = end($pms);
} while(count($pms) != 0);
?>
