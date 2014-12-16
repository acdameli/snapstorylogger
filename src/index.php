<?php

require_once("vendor/autoload.php");

$app = new \Cilex\Application('SnapchatStoryLogger');
$app->command(new \acdameli\Snapchat\Commands\StoryLoggerCommand());
$app->run();
