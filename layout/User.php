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

	private function getJSONProp($cache = false) {
		if($this->user == null)
			return null;

		// TODO: Handle 404s
		if($cache == false || $this->_json == null)
			$this->_json = $this->http->get("http://www.reddit.com/user/{$this->user}/about.json");

		return json_decode($this->_json['response']);
	}

	public function getProp() {
		if($this->user == null)
			return null;

		$json = $this->getJSONProp();
		if($json == null)
			return null;

		$prop = new _UserProperties($this);
		$prop->created = $json->data->created;
		$prop->created_utc = $json->data->created_utc;
		$prop->comment_karma = $json->data->comment_karma;
		$prop->id = $json->data->id;
		$prop->kind = $json->kind;
		$prop->link_karma = $json->data->link_karma;
		$prop->name = $json->data->name;
		return $prop;
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

	public function isLoggedOn() {
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

}

class _UserProperties extends Properties {
	public function __construct($target) {
		parent::construct($target);
		parent::setChild($this);
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
