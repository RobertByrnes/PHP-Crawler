#!/usr/bin/php
<?php

require_once('vendor/autoload.php');

printf("
 _____ _                _       _   _         _   _            _____       _     _            
/  __ \ |              | |     | | | |       | | | |          /  ___|     (_)   | |           
| /  \/ |__   __ _ _ __| | ___ | |_| |_ ___  | |_| |__   ___  \ `--. _ __  _  __| | ___ _ __  
| |   | '_ \ / _` | '__| |/ _ \| __| __/ _ \ | __| '_ \ / _ \  `--. \ '_ \| |/ _` |/ _ \ '__| 
| \__/\ | | | (_| | |  | | (_) | |_| ||  __/ | |_| | | |  __/ /\__/ / |_) | | (_| |  __/ |    
 \____/_| |_|\__,_|_|  |_|\___/ \__|\__\___|  \__|_| |_|\___| \____/| .__/|_|\__,_|\___|_|    
                                                                    | |                       
                                                                    |_|");

printf("
                                            / _ \
                                          \_\(_)/_/
                                           _//o|\_
                                            /  |\n
");


$shortopts  = "";
$shortopts .= "u:";  // Required value
$shortopts .= "n:";  // Required value
$shortopts .= "s::"; // Optional value
$shortopts .= "vh"; // These options do not accept values

$longopts  = array(
    "url:",     // Required value
    "name:",
    "spiders::",    // Optional value
    "version",        // No value
    "help",           // No value
);

$options = getopt($shortopts, $longopts);

if(isset($options['u'])) {
    $url = $options['u'];
}

if(isset($options['n'])) {
    $project_name = $options['n'];
}

(empty($options['s'])) ? $spiders = 4 : $spiders = $options['s'];

if(isset($options['v'])) {
    print("\n[+] Charlotte the spider - web links crawler, written by Robert Byrnes,
    under GPLv3 licence. https://github/RobertByrnes/PHP-Crawler\n");
    die();
}

if(isset($options['h'])) {
    help();
}

(!empty($url) && !empty($project_name)) ? run($url, $project_name, $spiders) : help();


function run($url, $project_name, $spiders) {
    $SAVE = new SaveData($project_name, $url);
    $CRAWLER = new Crawler($url, $project_name, $spiders);
    $CRAWLER->crawl();
    $CRAWLER->spawn();
}

function help() {
    $helpMessage = "\n
    /*** ARGUMENTS ***/\n
    Required arguments:
        -u url (string) e.g. http://website.org or https://...
        -n project name (string) e.g. website - this is used to create the dir to save the results to
            following crawling.
    Optional arguments: 
        -s number of spiders (integer)[parallel processes] used in crawling. Default is 4.
        -v returns the version.
        -h prints this help message.";
    print("\n".$helpMessage."\n");
    die();
}