<?php
require_once("User.php");
$user = new User();
$user->fromSessionCookie("cookie");
?>