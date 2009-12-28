<?php
class CookieJar {
	
	private $cookies = array();
	function __construct($rhs="") {
		if(is_string($rhs)) {
			$this->parse($rhs);
		}	
	}
	
	public function merge($rhs) {
		if(is_object($rhs) && get_class($rhs) == "CookieJar")
			$this->cookies = array_merge($this->cookies, $rhs->getCookies());
	}
	
	public function getCookies() {
		return $this->cookies;
	}
	
	public function toString() {
		$ret = "";
		foreach ($this->cookies as $key => $value)
			$ret .= "$key=$value; ";
		return trim($ret, "; ");
	}
	
	private function parse($str) {
		$matches = array();
		$result = preg_match("/^Set-Cookie: (.+?)=(.+?);(.*)?/", $str, $matches);
		
		if($result <= 0)
			return;
		
		$this->cookies[$matches[1]] = $matches[2];
	}
}
?>