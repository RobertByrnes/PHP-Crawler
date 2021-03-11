<?php

require_once('vendor/autoload.php');

$url = "www.website.org";
$project_name = "WebsiteDotOrg";
$SAVE = new SaveData($project_name);
$CRAWLER = new Crawler($url, $project_name);

$CRAWLER->accept_job();

$CRAWLER->crawl();