<?php
require_once "HTTP.php";
require_once "CookieJar.php";

class Reddit {
	private $cj;
	private $_modhash;
	
	function __construct($user, $pass = null) {
		$this->_modhash = null;
		if($pass != null) {
			$this->cj = new CookieJar();
			$this->login($user, $pass);
		}
		else {
			$this->cj = new CookieJar("Set-Cookie: reddit_session=$user;");
		}
	}
	
	private function login($user, $pass) {
		HTTP::post("http://www.reddit.com/api/login/$user", "user=$user&passwd=$pass", $this->cj);
	}
	
	private function propBag($url = "http://www.reddit.com/") {
		$result = HTTP::get($url, $this->cj);
		$array = array();
		$result = preg_match("/var reddit = \\{(.+?)\\};/m", $result['response'], $array);
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
			$json = preg_replace("/:\\s+false/", ': "false"', $json);
			$json = preg_replace("/:\\s+true/", ': "true"', $json);
			
			$json = "{" . $json . "}";
			return json_decode($json);
		}
		
		return null;
	}
	
	private function modhash($cache = true, $url = "http://www.reddit.com/") {
		// It's unclear when a modhash expires, so just refresh every hour.
		if ($cache == true && $this->_modhash != null && (time() - $this->_modhash['time']) < 3600)
			return $this->_modhash['hash'];
		
		$propBag = $this->propBag($url);
		
		if($propBag == null && is_object($propBag))
			return null;
		if($propBag->logged == "false" || strlen($propBag->modhash) == 0)
			return null;
			
		$this->_modhash = array('hash' => $propBag->modhash, 'time' => time());
		return $this->_modhash['hash'];
	}
	
	private function ban($r, $id) {
		$resp =  HTTP::post("http://www.reddit.com/api/ban", "id=$id&executed=banned&r=$r&uh=" . $this->modhash(), $this->cj);
	}
	
	private function unban($r, $id) {
		$resp =  HTTP::post("http://www.reddit.com/api/unban", "id=$id&executed=unbanned&r=$r&uh=" . $this->modhash(), $this->cj);
	}
	
	public function ban_comment($r, $commentid) {
		$this->ban($r, $commentid);
	}
	
	public function unban_comment($r, $commentid) {
		$this->unban($r, $commentid);
	}
	
	public function ban_thread($r, $threadid) {
		$this->ban($r, $threadid);
	}
	
	public function unban_thread($r, $threadid) {
		$this->unban($r, $threadid);
	}
	
	public function isLoggedOn() {
		return $this->modhash(false) != null;
	}
	
	public function username() {	
		$propBag = $this->propBag();
		
		if($propBag == null && is_object($propBag))
			return null;
		if($propBag->logged == "false" || strlen($propBag->logged) == 0)
			return null;
			
		return $propBag->logged;
	}
	
	public function userStats($user = null) {
		if($user == null)
			$user = $this->username();
		if($user == null)
			return null;	
		$result = HTTP::get("http://www.reddit.com/user/$user/about.json", $this->cj);
		return json_decode($result['response']);
	}
	
	public function moderators($r) {
		$result = HTTP::get("http://www.reddit.com/r/$r/about/moderators", $this->cj);
		$match = array();
		$results = preg_match("@<h1>MODERATORS</h1>(.+?)</div>@m", $result['response'], $match);
		
		if($results <= 0)
			return null;

		$results = preg_match_all("@<a href=\"http://www.reddit.com/user/.+?>(.+?)</a>@m", $match[1], $match);
		
		if($results <= 0)
			return null;

		return $match[1];
	}
	
	public function isModerator($r, $user) {
		$mods = $this->moderators($r);
		
		if($mods == null)
			return false;
		
		return (array_search($user, $mods) != false); 
	}
	
	// Check if the current, logged in, user is banned from a Reddit
	public function isBanned($r) {
		try {
			$result = HTTP::get("http://www.reddit.com/r/$r/submit", $this->cj);
		} catch(Exception $e) {
			return true;
		}
		$match = array();
		$results = preg_match("@<title>reddit.com: forbidden (reddit.com)</title>@m", $result['response'], $match);
		if ($results <= 0)
			return false;
		return true;
	}
	
	// Types: all, messages, comments, selfreply
	public function getInbox($type = "all") {
		if($type == "all")
			$url = "http://www.reddit.com/message/inbox/.json";
		else if($type == "inbox" || $type == "messages" || $type == "comments" || $self == "selfreply")
			$url = "http://www.reddit.com/message/$type/.json";
		else
			throw new Exception("Invalid type \"$type\" $php_errormsg");
			
		$result = HTTP::get($url, $this->cj);
		return json_decode($result['response']);
	}
	
	public function sendMessage($to, $subject, $text) {
		$subject = urlencode($subject);
		$text = urlencode($text);
		HTTP::post("http://www.reddit.com/api/compose", "uh=" . $this->modhash() . "&to=$to&subject=$subject&thing_id=&text=$text&id=%23compose-message", $this->cj);
	}
	
	public function getReadercount($r) {
		$result = HTTP::get("http://www.reddit.com/r/$r/", $this->cj);
		$match = array();
		$results = preg_match("@<span class=\"number\">(.+?)</span>@m", $result['response'], $match);
		
		if ($results <= 0)
			return null;

		return $match[0];
	}
	
    public function getSubredditage($r) {
		$result = HTTP::get("http://www.reddit.com/r/$r/", $this->cj);
		$match = array();
		$results = preg_match("@<span class=\"age\">a community for (.+?)</span>@m", $result['response'], $match);
		
		if ($results <= 0)
			return null;

		return $match[1];
	}
	
	public function getSubredditcreator($r) {
		$result = HTTP::get("http://www.reddit.com/r/$r/", $this->cj);
		$match = array();
		
		$results = preg_match_all("@created by &#32;<a href=\"(.+?)\" class=\"author\" >(.+?)</a>@m", $result['response'], $match);
		
		if ($results <= 0)
			return null;
        
        return $match[1][0];
	}
}
?>