<?php
require_once "HTTP.php";

class Reddit {
	public function __construct($user = null, $url = "http://www.reddit.com/") {
		$self->user = $user;
		$self->url = $url;
	}
}
?>
