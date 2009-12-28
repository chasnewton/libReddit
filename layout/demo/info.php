<?php
include "../User.php";
$user = new User("mindbrane", "mindbrane");
$prop = $user->getProp();
echo "<pre>";
print_r($prop);
print $prop->name . "\n";
print $prop->modhash . "\n";
echo "</pre>";
?>
