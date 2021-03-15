# PHP-Crawler
Implementation of Queue - Producer - Consumer Web Crawler in PHP. Uses multiple processes or native threads via the amphp/parallel dependency to crawl a domain for respondant links.

                                                        / _ \
                                                      \_\(_)/_/
                                                       _//o|\_
                                                        /  |
@author: Robert Byrnes
@email: robert@globalrbdev.uk

# Install
Install using 'composer require robertbyrnes/phpcrawler':
Once installed 'cd' into vendor/robertbyrnes/phpcrawler to find main.php this is the file to run the program.  If you run into any trouble with 'class not found' errors be sure to run composer update and composer dump-autoload commands.  If run with 'php main.php' from a terminal/command prompt the help menu will show detailing the arguments required to begin a crawl. 

    /*** ARGUMENTS ***/
    Required arguments:
        -u url (string) e.g. http://website.org or https://...
        -n project name (string) e.g. website - this is used to create the dir to save the results to
            following crawling.
    Optional arguments: 
        -s number of spiders (integer)[parallel processes] used in crawling. Default is 4.
        -v returns the version.
        -h prints this help message.

# Dependencies
This program requires ^PHP7 to run as well as the amphp/parallel library for the multiple processes. Amphp/parallel should auto install with composer.

# Classes
# Crawler::class
Manages queueing of tasks and passes work between Queue::class and Spider::class
utilising producer/consumer model with queue.

- Crawler::class functions:

- spawn() recursive function implementing the functionality of the parallel library to create           processes which call Spider::search() to the do the crawling.  This recursive loop will run until the Queue::class->queue is empty.  When this happens the program will exit.
- crawl() works with add_job() calling each other until the program exits.
- crawl() checks links remain within the domain.
- add_job() pushes each newly found link to the queue in Queue::class.

# Queue::class
A first-in, first-out data structure.

- Queue::class functions:

- push() pushes an item to the end of the queue.
- shift() take an item off the head of the queue.
- pop() take an item off the end of the queue.
- open_job() increment the count of unfinished tasks.
- task_done() decrement the count of unfinished tasks.
- getCount() gets the total number of items in the queue.

# SaveData::class
Handles all file tasks.

- SaveData::class functions:

- create_dir() creates the project dir within results in the root.
- create_files() creates queue.txt and crawled.txt in the project dir.
- file_to_array() opens either queue.txt or crawled.txt and parses stream to array.
- array_to_file() takes queue array or crawled array and writes to queue.txt or crawled.txt.
- write_file() uses fwrite to open new files, or open - empty - then rewrite.
- append_to_file() appends lines to either queue.txt or crawled.txt.
- delete_file_contents() empties files - unused in PHP-Crawler.

# Spider::class
Extracts links from given url. Updates queues and files.

- Spider::class functions:

- setup() prints to user the domain name derived from the url. Creates dir and files.
Populates both queue and crawled arrays from files.
- search() takes a url as an argument and calls extract_links(), passing the result to
sort_to_queue(). Prints updated queue counts to the user and updates the files
once the crawling round is complete.
- getDomain() extracts the domain name form the given url.
- extract_links() uses the built in php DomDocument::class to parses links extracted from a url into an array.
- sort_to_queue() cleans link to ensure all are from within the domain. Ensures unique links
are pushed to the queue in Queue::class.
- update() utilises SaveData::class to write files queue.txt and crawled.txt with updated links.
- check_queue() checks the count of the Spider::class property $queue. Exits the program once the queue is empty.






