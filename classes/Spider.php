<?php

/**
 * Class Spider. Extracts links from given url. Updates queues and files.
 */
class Spider
{
    /**
     * Name of the project, also name of the directory created in root/results/
     * @var string
     */
    protected string $PROJECT_NAME = "";

    /**
     * Homepage of the domain being crawled.
     * @var string
     */
    protected string $TARGET_URL = "";

    /**
     * Domain name, derived from TARGET_URL, used in checking links remain within the domain.
     */
    protected string $DOMAIN_NAME = "";

    /**
     * Filepath to queue.txt.
     * @var string
     */
    private string $queue_path = "";

    /**
     * Filepath to crawled.txt.
     */
    private string $crawled_path = "";

    /**
     * Object of the SaveData::class.
     */
    private SaveData $Save;

    /**
     * Temporary queued task queue which interacts with the queue held in Queue::class.
     */
    private array $queue = [];

    /**
     * Temporary crawled link queue which is continually written to crawled.txt.
     */
    private array $crawled = [];

    /**
     * Object of the Queue::class.
     */
    private Queue $QUEUE;

    /**
     * A portion of the domain name saved to a string, used to check links remain within the domain.
     */
    private string $preg_string;

    /**
     * Constructor for the Spider::class.
     *
     * @param string $url
     * @param string $name
     * @param SaveData $Save
     * @param Queue $queue
     */
    public function __construct(string $url, string $name, SaveData $Save, Queue $queue)
    {
        $this->Save = $Save;
        $this->QUEUE = $queue;
        $this->PROJECT_NAME = $name;
        $this->queue_path = "results/".$this->PROJECT_NAME."/queued.txt";
        $this->crawled_path = "results/".$this->PROJECT_NAME."/crawled.txt";
        $this->TARGET_URL = $url;
        $this->DOMAIN_NAME = $this->getDomain($url);
        $this->preg_string = $this->preg_string(); 
        $this->setup();
        $this->search($spider_name="Charlotte the Spider", $this->TARGET_URL);
    }

    /**
     * Prints to user the domain name derived from the url. Creates dir and files.
     * Populates both queue and crawled arrays from files.
     *
     * @return void
     */
    private function setup() : void
    { 
        printf("\n[+] Using domain name >> ".$this->DOMAIN_NAME."\n");
        sleep(1);
        $this->Save->create_dir($this->PROJECT_NAME);
        $this->Save->create_files($this->PROJECT_NAME, $this->TARGET_URL);
        $this->queue = $this->Save->file_to_array($this->queue_path);
        $this->crawled = $this->Save->file_to_array($this->crawled_path);
    }

    /**
     * Takes a url as an argument and calls extract_links(), passing the result to
     * sort_to_queue(). Prints updated queue counts to the user and updates the files
     * once the crawling round is complete.
     *
     * @param string $spider_name
     * @param string $url
     * @return void
     */
    public function search($spider_name, $url) : void
    {
        try {
            if ($this->sort_to_queue($spider_name, $this->extract_links($url))){
                print("[+] now crawling >> ".$url." with ".$spider_name."\n");
                if ((array_search($url, $this->queue))||(in_array($url, $this->crawled))) {
                    $index = array_search($url, $this->queue);
                    unset($this->queue[$index]);
                }
                if (!in_array($url, $this->crawled)) {
                    $this->crawled[] = $url;
                }
                printf("[+] Queued ".count($this->queue)." >> Crawled ".count($this->crawled));
                $memory = (memory_get_usage()/1000000);
                printf(" >> Memory Usage ".$memory."\n");
                $this->update();
            }
        }
        catch (Exception $e) {
            printf("[-] extracting links from ".$url." failed.\n");
        }
    }

    /**
     * Extracts the domain name form the given url.
     *
     * @param string $url
     * @return void
     */
    private function getDomain($url)
    {
        $pieces = parse_url($url);
        $domain = isset($pieces['host']) ? $pieces['host'] : '';
        if(preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)){
            return $regs['domain'];
        }
        return False;
    }

    /**
     * Uses the DomDocument class to parses links extracted from a url into an array.
     *
     * @param string $target_url
     * @return array
     */
    private function extract_links($target_url) : array
    {
        $dom = new DomDocument();
        $internalErrors = libxml_use_internal_errors(true);
        $links = [];
        try {
            if ($dom->loadHTMLFile($target_url)) {
                libxml_use_internal_errors($internalErrors);
                foreach ($dom->getElementsByTagName('a') as $link) {
                    $links[] = $link->getAttribute('href');
                }
                return $links;
            }
            libxml_use_internal_errors($internalErrors);
            throw new Exception("no response from ".$url);
        }
        catch (Exception $e) {
            printf("[-] ".$e."\n");
            return array();
        }
    }

    /**
     * Cleans link to ensure all are from within the domain. Ensures unique links
     * are pushed to the queue in Queue::class. 
     *
     * @param string $spider_name
     * @param array $links
     * @return void
     */
    private function sort_to_queue($spider_name, $links)
    {
        foreach ($links as $link => $value) {
            if ((in_array($value, $this->crawled)) || (in_array($value, $this->queue))) {
                unset($links[$link]);
            }
            if(isset($value)) {
                if($this->DOMAIN_NAME != $this->getDomain($value)){
                    unset($links[$link]);
                }
            }
            if(isset($value)) {
                if(!preg_match("/http/", $value)) {
                    $value = $this->TARGET_URL."/".$value; 
                }
            }
            if(isset($value)) {
                if(!preg_match("/.$this->preg_string./", $value)) {
                    unset($links[$link]);
                }
            }
            if((!empty($links[$link]))) {
                $this->queue[] = $links[$link];
                $this->queue = array_filter($this->queue);
            }
        }
        $this->queue = array_unique($this->queue);
        foreach($this->queue as $link) {
            $this->QUEUE->push($link);
        }
        $this->check_queue();
        return True;
    }

    /**
     * Explodes the url given in main.php to create a test string
     * e.g. website from http://www.website.org.
     *
     * @return string
     */
    public function preg_string() : string
    {
        $preg_string = explode(".", $this->TARGET_URL);
        $preg_string = $preg_string[1];
        return $preg_string;
    }

    /**
     * Utilises SaveData::class to write files queue.txt and crawled.txt with updated links.
     *
     * @return void
     */
    public function update()
    {
        try {
            if ($this->Save->array_to_file(array_unique($this->queue), $this->queue_path)) {
                if ($this->Save->array_to_file(array_unique($this->crawled), $this->crawled_path)) {
                    return True;
                }
            }
            throw new Exception("failure updating files.");
        }
        catch (Exception $e) {
            printf("[-]: ".$e."\n");
        }
    }

    /**
     * Checks the count of the Spider::class property $queue.
     *
     * @return void
     */
    public function check_queue()
    {
        $count = count($this->queue);
        if($count == 0) {
            die();
        }
    }
}