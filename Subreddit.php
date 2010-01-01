<?php
require_once "User.php";

class Subreddit {
    private $html;
    private $json;
    
    public function __construct($name, $user = null) {
		if ($user == null) {
            $this->user = new User();
        }
        else {
            $this->user = $user;
        }
        $this->name = $name;
	}
	
	private function getHtml() {
	   if (isset($this->html)) {
	       return $this->html;
	   }
	   else {
	       $html = $this->user->httpGet("http://www.reddit.com/r/" . $this->name);
	       $this->html = $html['response'];
	       return $this->html;
	   }
	}
	
	private function getJson() {
	   if (isset($this->json)) {
	       return $this->json;
	   }
	   else {
	       $json = $this->user->httpGet("http://www.reddit.com/r/" . $this->name . "/.json");
	       $this->json = json_decode($json['response'], true);
	       return $this->json;
	   }
	}
	
	public function clearHtml() {
	   unset($this->html);
	}
	
	public function clearJson() {
	   unset($this->json);
	}
	
	public function getInfo() {
	   if (isset($this->html))
	       return new _SubredditInfo($this->getAge(), $this->getCreator(), $this->getDescription());
	   else
	       echo "Error: $html is not set.";
	}
	
    public function getAge() {
        $result = $this->getHtml();
        $match = array();
        
        $results = preg_match("@<span class=\"age\">a community for (.+?)</span>@m", $result, $match);
        
        if ($results <= 0)
            return null;
            
        return $match[1];
    }
    
    public function getCreator() {
        $result = $this->getHtml();
        $match = array();
		
        $results = preg_match_all("@created by &#32;<a href=\"(.+?)\" class=\"author\" >(.+?)</a>@m", $result, $match);
		
        if ($results <= 0)
            return null;
        
        return $match[1][0];
    }
    
    public function getDescription() {
        $result = $this->getHtml();
        $match = array();
        
        $results = preg_match_all("@<div class=\"usertext-body\"><div class=\"md\"><p>(.+?)</p></div>@m", $result, $match);
        
        if ($results <= 0)
            return null;
        
        return $match[0];
    }
    
    public function getPosts($limit = 5) {
        $result = $this->getJson();
        $posts = $result['data']['children'];
        $output = array();
        
        if ($limit > 5)
            $limit = 5;
        else if ($limit < 1)
            $limit = 1; 
            
        for ($i = 0; $i < $limit; $i++) {
            $post = $posts[$i]['data'];
            $output[] = array(
                  'id' => $post['id'],
                  'title' => $post['title'],
                  'author' => $post['author'],
                  'description' => $post['selftext'],
                  'url' => $post['url'],
                  'up' => $post['ups'],
                  'down' => $post['downs'],
                  'score' => $post['score'],
                  'age' => $post['created'],
                  'comments' => $post['num_comments'],
                  'subreddit' => $post['subreddit']
                 );
        }
        
        return $output;
    }
}

class _Subredditinfo {
    public $age;
    public $creator;
    public $description;
    
    public __construct($age = null, $creator = null, $description = null) {
        $this->age = $age;
        $this->creator = $creator;
        $this->description = $description;
    }
}
?>
