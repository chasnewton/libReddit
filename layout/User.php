<?php
require_once "HTTP.php";

class User {
	private $http;
	function __construct($user = null, $pass = null) {
		// if $pass != null, get a session cookie from server.
		$this->http = new HTTP();
		$this->user = $user;
		$this->pass = $pass;
		if($user != null && $pass != null)
			$this->login($user, $pass);
	}

	public static function fromSessionCookie($cookie) {
		// Construct a new User() from a session cookie.
	}

	// modhash, etc
	private function login($user, $pass) {
		$this->http->post("http://www.reddit.com/api/login/$user", "user=$user&passwd=$pass");
	}

	// Return the user's Session ID, if one exists.
	public function getSessionID() {
		if($this->http->cookiejar == null)
			return null;
		$cookies = $this->http->cookiejar->getCookies();
		if(!array_key_exists('reddit_session', $cookies))
			return null;
		return $cookies['reddit_session'];
	}
}
?>
