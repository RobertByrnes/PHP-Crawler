<?php

require_once('vendor/autoload.php');

$url = "https://www.skatewarehouse.co.uk";
$project_name = "Skatewarehouse";
$SAVE = new SaveData($project_name, $url);
$CRAWLER = new Crawler($url, $project_name);

$CRAWLER->crawl();

$CRAWLER->accept_job();