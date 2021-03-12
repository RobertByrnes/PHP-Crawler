<?php

require_once('vendor/autoload.php');

$url = "http://www.website.org";
$project_name = "WebsiteDotOrg";
$SAVE = new SaveData($project_name, $url);
$CRAWLER = new Crawler($url, $project_name);

$CRAWLER->crawl();

$CRAWLER->accept_job();