<?php
require_once "HTTP.php";
require_once "Properties.php";

class User {
	private $http;
	private $_modhash;
	private $_json;

	function __construct($user = null, $pass = null) {
		// if $pass != null, get a session cookie from server.
		$this->http = new HTTP();
		$this->user = $user;
		$this->pass = $pass;
		if($user != null && $pass != null)
			$this->login($user, $pass);
	}

	public static function fromSessionCookie($cookie) {
		// TODO: Construct a new User() from a session cookie.
	}

	public function httpGet($url) {
		return $this->http->get($url);
	}

	public function httpPost($url, $data) {
		return $this->http->post($url, $data);
	}

	private function login($user, $pass) {
		$this->http->post("http://www.reddit.com/api/login/$user", "user=$user&passwd=$pass");
	}

	// Returns an array containing the settings needed to run the client Javascript.
	private function jsPropBag($url = "http://www.reddit.com/") {
		$result = $this->http->get($url);
		$array = array();
		$result = preg_match("/var reddit = \\{(.+?)\\};/m", $result['response'], $array);
		
		// This is pretty close to JSON, so massage
		if($result > 0 && strlen($array[1]) > 0) {
			// Strip out comments.
			$json = preg_replace("@/\\*(.*?)\\*/@", "", $array[1]);

			// Add quotes around properties
			$json = preg_replace("/([\\{,])+\\s+(.*?):/", '$1 "$2":', "," . $json);
			$json = trim($json, ", ");

			// Replace 'str' with "str"
			$json = preg_replace("/'(.*?)'/", '"$1"', $json);

			// Replace [str] with {str} (Bracket-list form to curley bracket)
			$json = preg_replace("/\\[(.*?)\\]/", '{$1}', $json);

			// Replace "prop": false and "prop": true with "false" and "true"
			// TODO: This is wrong - JSON supports true/false boolean types.
			$json = preg_replace("/:\\s+false/", ': "false"', $json);
			$json = preg_replace("/:\\s+true/", ': "true"', $json);

			$json = "{" . $json . "}";
			return json_decode($json);
		}

		return null;
	}
	
	// Returns a modhash for the current user. If cached is true, then a
	// potentially cached modhash that is less than one hour old is returned.
	private function modhash($cache = true, $url = "http://www.reddit.com/") {
		// It's unclear when a modhash expires, so just refresh every hour.
		if ($cache == true && $this->_modhash != null && (time() - $this->_modhash['time']) < 3600)
			return $this->_modhash['hash'];

		$propBag = $this->jsPropBag($url);

		if($propBag == null && is_object($propBag))
			return null;
		if($propBag->logged == "false" || strlen($propBag->modhash) == 0)
			return null;

		$this->_modhash = array('hash' => $propBag->modhash, 'time' => time());
		return $this->_modhash['hash'];
	}	

	// Returns a given element of a user's JSON-accessible properties page.
	// If $cache is true, the element will be returned from a cached copy
	// of the JSON page, if applicable.
	private function getJSONProp($path, $cache = false) {
		if($this->user == null)
			return null;
		
		// TODO: Handle 404s
		if($cache == false || $this->_json == null || (time() - $this->_json['time']) < 15)
			$this->_json = array( 'time' => time(), 'json' => $this->http->get("http://www.reddit.com/user/{$this->user}/about.json"));

		$json = $this->_json['json'];	
		$json = json_decode($json['response']);
		
		// Walk $path 
		// Note to coder: Future versions of PHP may let you dynamically walk a class without doing this.
		// TODO: Handle bad paths.
		$path = preg_split("/->/", $path);
		foreach($path as $step)
			$json = $json->$step;

		return $json;
	}

	public function getProp() {
		if($this->user == null)
			return null;

		$prop = new _UserProperties($this);
		$prop->sessionID = $this->getSessionID();
		return $prop;
	}


	public function getUsername() {
		return $this->user;
	}

	// Return the user's Session ID, if one exists.
	private function getSessionID() {
		if($this->http->cookiejar == null)
			return null;
		$cookies = $this->http->cookiejar->getCookies();
		if(!array_key_exists('reddit_session', $cookies))
			return null;
		return $cookies['reddit_session'];
	}

	// Return true if the user is logged in and false otherwise.
	private function isLoggedOn() {
		if($this->getSessionID() == null)
			return false;
		return $this->modhash(false) != null;
	}

	public function call($name, $arguments = array()) {
		$backtrace = debug_backtrace();
		if($backtrace[1]['class'] == '_UserProperties')
			return call_user_func_array(array($this, $name), $arguments);
		throw new Exception("User:call() is inacessible from this context.");
	}

	public function __toString() {
		return $this->getUsername();
	}

}

class _UserProperties extends Properties {
	private $created;
	private $createdUtc;
	private $commentKarma;
	private $id;
	private $isLoggedIn;
	private $kind;
	private $linkKarma;
	private $name;
	private $modhash;

	public $sessionID;

	public function __construct($target) {
		parent::construct($target);
		parent::setChild($this);
	}

	protected function _created() {
		return parent::getTarget()->call("getJSONProp", array("data->created", true));
	}

	protected function _createdUtc() {
		return parent::getTarget()->call("getJSONProp", array("data->created_utc", true));
	}

	protected function _commentKarma() {
		return parent::getTarget()->call("getJSONProp", array("data->comment_karma", false));
	}

	protected function _id() {
		return parent::getTarget()->call("getJSONProp", array("data->id", true));
	}

	protected function _isLoggedIn() {
		return parent::getTarget()->call("isLoggedOn");
	}

	protected function _kind() {
		return parent::getTarget()->call("getJSONProp", array("kind", true));
	}

	protected function _linkKarma() {
		return parent::getTarget()->call("getJSONProp", array("data->link_karma", false));
	}

	protected function _name() {
		return parent::getTarget()->call("getJSONProp", array("data->name", true));
	}

	protected function _modhash() {
		return parent::getTarget()->call("modhash");
	}

}

/*class _UserProperties {
	// Set by User()
	public $created ;
	public $created_utc;
	public $comment_karma;
	public $id;
	public $kind;
	public $link_karma;
	public $name;

	private $_target;
	public function __construct($target) {
		$this->_target = $target;
	}

	public function __get($name) {
		if(method_exists($this, "_" . $name))
			return call_user_func(array($this, "_" . $name));
		return null;
	}

	private function _modhash() {
		return $this->_target->call("modhash");
	}
}*/
?>
