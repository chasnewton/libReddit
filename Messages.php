<?php
require_once "User.php";
require_once "Message.php";

class Messages {
	public function __construct($user) {
		if($user == null || !($user instanceof User))
			throw new Exception("Messages::__construct(): $user must be a User object.");
		if(!$user->getProp()->isLoggedIn)
			throw new Exception("Messages::__contstruct(): $user is not logged in.");
		$this->user = $user;
	}

	// Read messages from a user's inbox/sentbox.
	//
	// Boxtypes:
	// 	all - reads every message from a user's inbox
	// 	messages - reads only private messages sent to a user
	// 	comments - reads only comment replies
	// 	selfreply - reads only replies to self posts.
	// 	sent - reads only private messages that a user has sent.
	//
	// If $after is specified, only messages after message $after will be returned. (Useful for paging).
	// $after may be specified as a string containing a message's name (e.g., "t4_3byo9") or as a Message object.
	public function getMessages($boxType = "all", $after = null) { 
		if($boxType != "all" && $boxType != "messages" && $boxType != "comments" && $boxType != "selfreply" && $boxType != "inbox" && $boxType != "sent")
			throw new Exception("Messages::getMessages(): Invalid box type '$boxType'.");
		if($boxType == "all")
			$boxType = "inbox";

		// Handle after-based paging.
		if($after != null) {
			if($after instanceof Message)
				$after = $after->name;
			$json = $this->user->httpGet("http://www.reddit.com/message/$boxType/.json?after=$after");
		}
		else
			$json = $this->user->httpGet("http://www.reddit.com/message/$boxType/.json");

		$json = json_decode($json['response']);

		$before = $json->data->before;
		$after = $json->data->after;

		$ret = array();
		foreach($json->data->children as $c) {
			$msg = new Message();

			$msg->kind = $c->kind;
			$msg->body = $c->data->body;
			$msg->was_comment = $c->data->was_comment;
			$msg->name = $c->data->name;

			if ($boxType == "sent")
				$msg->author = $this->user;
			else
				$msg->author = new User($c->data->author);

			if ($boxType != "sent")
				$msg->dest = $this->user;
			else
				$msg->dest = new User($c->data->dest);

			$msg->created = $c->data->created;
			$msg->created_utc = $c->data->created_utc;
			$msg->body_html = $c->data->body_html;
			$msg->context = $c->data->context;
			$msg->new = $c->data->new;
			$msg->id = $c->data->id;
			$msg->subject = $c->data->subject;

			$ret[] = $msg;
		}

		return $ret;
	}
}
?>
