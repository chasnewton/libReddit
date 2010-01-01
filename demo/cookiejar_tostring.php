<?php
require_once("HTTP.php");
$cookiejar = new CookieJar();
// HTTP stuff that makes cookies
echo $cookiejar->toString();
?>