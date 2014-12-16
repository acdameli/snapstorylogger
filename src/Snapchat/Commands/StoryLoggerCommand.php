<?php

namespace acdameli\Snapchat\Commands;

use \Symfony\Component\Console\Command\Command as BaseCommand;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

use \Snapchat;

define('TWO_DAYS', 172800); //60*60*24*2 = 172800

class StoryLoggerCommand extends BaseCommand {
    public function configure() {
        $this
            ->setName('stories:log')
            ->setDescription('Pull friend stories and store them to the disk')
            ->addArgument('username', InputArgument::REQUIRED, 'The username to connect with.')
            ->addArgument('password', InputArgument::REQUIRED, 'The password for the username.')
            ->addArgument('json_log_path', InputArgument::REQUIRED, 'The location of the log history file used to prevent downloading images which have already been pulled.')
            ->addArgument('download_path', InputArgument::REQUIRED, 'The location to store the story images.');
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $username  = $input->getArgument('username');
        $password  = $input->getArgument('password');
        $json_path = $input->getArgument('json_log_path');
        $path      = $input->getArgument('download_path');

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        $story_log = @json_decode(@file_get_contents($json_path), true);
        $story_log = is_array($story_log) ? $story_log : [];

        $this->output($output, "Starting execution for:", OutputInterface::VERBOSITY_NORMAL);
        $this->output($output, " username: {$username}", OutputInterface::VERBOSITY_NORMAL);
        $this->output($output, " log_path: {$json_path}", OutputInterface::VERBOSITY_NORMAL);
        $this->output($output, " download_path: {$path}", OutputInterface::VERBOSITY_NORMAL);

        $story_start_count = count($story_log);
        $this->output($output, " story_log started with {$story_start_count} stories", OutputInterface::VERBOSITY_VERBOSE);

        $snapchat  = new Snapchat($username, $password);
        $story_log = $this->getStories($story_log, $snapchat, $path);

        $story_new_count = count($story_log) - $story_start_count;
        $this->output($output, " downloaded {$story_new_count} stories", OutputInterface::VERBOSITY_VERBOSE);

        $story_log = array_filter($story_log, function($story_data) {
            return $story_data['date'] > (time() - TWO_DAYS);
        });
        $this->persistLog($story_log, $json_path);

        $story_cleaned_count = count($story_log);
        $this->output($output, " story_log ended with {$story_cleaned_count} stories", OutputInterface::VERBOSITY_VERBOSE);

        $this->output($output, "Execution Complete", OutputInterface::VERBOSITY_NORMAL);
    }

    protected function output(OutputInterface $output, $message, $verbosity_minimum) {
        if ($output->getVerbosity() >= $verbosity_minimum) {
            $output->writeln($message);
        }
    }

    protected function getStories(array $story_log, Snapchat $snapchat, $path) {
        $stories = $snapchat->getFriendStories();
        foreach ($stories as $story) {
            if (!isset($story_log[$story->id])) {
                $file = ($story->media_type = 0) ? "{$path}/{$story->id}.jpg" : "{$path}/{$story->id}.mov";
                $data = $snapchat->getStory($story->media_id, $story->media_key, $story->media_iv);
                file_put_contents($file, $data);
                $snapchat->markStoryViewed($story->id);
                $story_log[$story->id] = ['file' => $file, 'date' => time(), 'raw' => $story];
            }
        }

        return $story_log;
    }

    protected function persistLog($log, $path) {
        file_put_contents($path, json_encode($log));
    }
}
