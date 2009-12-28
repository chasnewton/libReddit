<?
include "../User.php";
$user = new User("mindbrane", "mindbrane");
$prop = $user->getProp();
print_r($prop);
print $prop->modhash . "\n";
?>
