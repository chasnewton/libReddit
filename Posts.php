<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once "User.php";

class Posts {
	public function __construct($user, $subreddit = null) {
        if($user == null || !($user instanceof User))
			throw new Exception("Posts::__construct(): $user must be a User object.");
		$this->user = $user;
		if ($subreddit == null)
            $this->r = "";
        else
            $this->r = "r/" . $subreddit;
	}
	
	public function getPosts($rType = "hot", $after = null) {
        if ($rType != "hot" && $rType != "new" && $rType != "controversial" && $rType != "all" && $rType != "month" && $rType != "week" && $rType != "day" && $rType != "hour")
			throw new Exception("Posts::getPosts(): Invalid subreddit type '$rType'.");

        if ($rType == "hot") $ext = ".json";
        else if ($rType == "new") $ext = "new/.json";
        else if ($rType == "controversial") $ext = "controversial/.json";
        else $ext == "top/.json?t=" . $rType;
        
        
        if ($after != null) {
            if ($after instanceof Post)
				$after = $after->name;
			$json = $this->user->httpGet("http://www.reddit.com/" . $this->r . "/$ext");
		}
		else
			$json = $this->user->httpGet("http://www.reddit.com/" . $this->r . "/$ext");
        
        $json = json_decode($json['response']);
        echo "<pre>";
		print_r($json);
		echo "</pre>";
		
		
	}
}

$user = new User();
$posts = new Posts($user, "programming");
$posts->getPosts("new");
?>
