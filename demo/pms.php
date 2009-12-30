<?php
// Prints out an entire PM inbox regardless of the number of messages
// contained in it.
include "../User.php";
include "../Messages.php";

$user = new User("user", "pass");
$msg = new Messages($user);
$last = null;
print "<pre>";
do {
	$pms = $msg->getMessages("messages", $last);
	foreach($pms as $p) {
		print "'{$p->subject}' from {$p->author->getUsername()}: {$p->body}<br>\n";
	}
	$last = end($pms);
} while(count($pms) != 0);
print "</pre>";
?>
