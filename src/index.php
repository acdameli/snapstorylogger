<?php

require_once("vendor/autoload.php");

$app = new \Cilex\Application('SnapchatStoryLogger');

$app->register(new \Cilex\Provider\ConfigServiceProvider(), array('config.path' => ''));

$app->command(new \acdameli\Snapchat\Commands\StoryLoggerCommand());
$app->run();
