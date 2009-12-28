<?php
require_once "HTTP.php";
require_once "CookieJar.php";

class User {
	private $http;
	function __construct($user, $pass = null) {
		// if $pass != null, get a session cookie from server.
		$self->http = new HTTP(new CookieJar());
	}

	public static function fromSessionCookie($cookie) {
		// Construct a new User() from a session cookie.
	}

	// modhash, etc
}


?>
