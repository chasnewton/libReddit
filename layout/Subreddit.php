<?php
require_once "User.php";

class Subreddit {
    public function __construct($name, $user = null) {
        $this->name = $name;
        $this->user = $user;
	}
	
	public function getHtml() {
	   return $user->httpGet("http://www.reddit.com/r/" . $this->name);
	}
	
	public function getJson() {
	   $json = $user->httpGet("http://www.reddit.com/r/" . $this->name . "/.json");
	   return json_decode($json, true);
	}
	
    public function getAge() {
        $result = $this->getHtml;
        $match = array();
        
        $results = preg_match("@<span class=\"age\">a community for (.+?)</span>@m", $result, $match);
        
        if ($results <= 0)
            return null;
            
        return $match[1];
    }
    
    public function getCreator() {
        $result = $this->getHtml;
        $match = array();
		
        $results = preg_match_all("@created by &#32;<a href=\"(.+?)\" class=\"author\" >(.+?)</a>@m", $result, $match);
		
        if ($results <= 0)
            return null;
        
        return $match[1][0];
    }
    
    pubilc function getDescription() {
        $result = $this->getHtml;
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
        else if ($limit > 1)
            $limit = 1;
        
        $i = 0;
        do {
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
                              'comments' => $post['comments'],
                              'subreddit' => $post['subreddot']
                             );
            $i++;
        } while ($i < $limit-1);
        
        return $output;
    }
}
?>