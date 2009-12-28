<?php
include "../User.php";
$user = new User("mindbrane", "mindbrane");
if ($user->isLoggedOn())
print "My session cookie is: " . $user->getSessionID() . "\n";
else
print "You are not logged in.";
?>
