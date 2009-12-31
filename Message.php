<?php
require_once "User.php";

class Message {
	public $after;
	public $kind;
	public $body;
	public $before;
	public $was_comment;
	public $name;
	public $author;
	public $dest;
	public $created;
	public $created_utc;
	public $body_html;
	public $context;
	public $new;
	public $id;
	public $subject;

	public function __construct(User $dest = null, User $author = null, $subject = null, $body = null) {
		$this->dest = $dest;
		$this->author = $author;
		$this->subject = $subject;
		$this->body = $body;
	}
}
?>
