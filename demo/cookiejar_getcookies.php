<?php
require_once("HTTP.php");
$cookiejar = new CookieJar();
// HTTP stuff that makes cookies
print_r($cookiejar->getCookies());
?>