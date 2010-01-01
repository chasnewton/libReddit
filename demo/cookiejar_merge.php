<?php
require_once("HTTP.php");
$thosecookies = new CookieJar();
// HTTP stuff that makes cookies
$thesecookies = new CookieJar();
$thesecookies->merge($thosecookies);
?>