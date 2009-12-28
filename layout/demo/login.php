<?
include "../User.php";
$user = new User("mindbrane", "mindbrane");
print "My session cookie is: " . $user->getSessionID() . "\n";
?>
