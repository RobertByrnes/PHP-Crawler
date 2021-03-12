<?php

require_once('vendor/autoload.php');

$url = "http://www.llexmoto.co.uk";
$project_name = "cmpo";
$SAVE = new SaveData($project_name, $url);
$CRAWLER = new Crawler($url, $project_name);

$CRAWLER->crawl();
sleep(5);
$CRAWLER->accept_job();