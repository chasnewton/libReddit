<?php
include("../Subreddit.php");
$subreddit = new Subreddit("programming");
print_r($subreddit->getPosts());
?>