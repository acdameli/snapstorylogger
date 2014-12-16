<?php

require_once "vendor/autoload.php";

define('TWO_DAYS', 172800); //60*60*24*2 = 172800

$username = $argv[1];
$password = $argv[2];
$snapchat = new Snapchat($username, $password);
$stories = $snapchat->getFriendStories();

$stories_log = @json_decode(@file_get_contents("/app/stories.json"), true);
$stories_log = is_array($stories_log) ? $stories_log : [];
echo "Stories log has ".count($stories_log)." elements in it already\n";

$date = date('Ymd');
$directory = "/app/{$date}";
is_dir($directory) || mkdir($directory); # ensure we have a place to put downloads
foreach ($stories as $story) {
  if (!isset($stories_log[$story->id])) {
    $file = ($story->media_type == 0) ? "{$directory}/{$story->id}.jpg" : "{$directory}/{$story->id}.mov";
    $data = $snapchat->getStory($story->media_id, $story->media_key, $story->media_iv);
    file_put_contents($file, $data);
    $snapchat->markStoryViewed($story->id);
    $stories_log[$story->id] = ['file' => $file, 'date' => time()];
    echo "downloaded {$story->id}\n";
  }
  $stories_log[$story->id]['raw'] = $story;
}

// remove old stories from log
foreach ($stories_log as $id => $data) {
  if ($data['date'] <= time() - TWO_DAYS) {
    unset($stories_log[$id]);
  }
}

echo "Stories log now has ".count($stories_log)." elements in it\n\n";

// update the log file
file_put_contents("/app/stories.json", json_encode($stories_log));
