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

$shortopts = "u:n:s::vh";

$longopts  = array(
    "url:",
    "name:",
    "spiders::",
    "version",
    "help"
);

switch ($options = getopt($shortopts, $longopts)) {
    case isset($options['u']):     $url = $options['u'];
    case isset($options['n']):     $project_name = $options['n']; break;
    case isset($options['v']):     printVersion(); break;
    case isset($options['h']):     help(); break;
}

empty($options['s']) ? $spiders = 2 : $spiders = $options['s'];

(!empty($url) && !empty($project_name)) ? run($url, $project_name, $spiders) : help();


function run($url, $project_name, $spiders)
{
    $SAVE = new SaveData($project_name, $url);
    $CRAWLER = new Crawler($url, $project_name, $SAVE);
    $CRAWLER->spawn($spiders);
    $CRAWLER->add_job();
}

function help()
{
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

function printVersion()
{
    print("\n[+] Charlotte the spider - web links crawler, written by Robert Byrnes,
    under GPLv3 licence. https://github/RobertByrnes/PHP-Crawler\n");
    die();
}