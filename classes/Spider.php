<?php

class Spider
{
    protected string $PROJECT_NAME = "";
    protected string $TARGET_URL = "";
    protected string $DOMAIN_NAME = "";
    private string $queue_path = "";
    private string $crawled_path = "";
    private SaveData $Save;
    private array $queue = [];
    private array $crawled = [];
    private Queue $QUEUE;
    private string $preg_string;

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

    private function setup() : void
    { 
        printf("\n[+] Using domain name >> ".$this->DOMAIN_NAME."\n");
        sleep(1);
        $this->Save->create_dir($this->PROJECT_NAME);
        $this->Save->create_files($this->PROJECT_NAME, $this->TARGET_URL);
        $this->queue = $this->Save->file_to_array($this->queue_path);
        $this->crawled = $this->Save->file_to_array($this->crawled_path);
    }

    public function search($spider_name, $url) : void
    {
        try {
            if ($this->sort_to_queue($spider_name, $this->extract_links($url))){
                if ((array_search($url, $this->queue))||(in_array($url, $this->crawled))) {
                    $index = array_search($url, $this->queue);
                    unset($this->queue[$index]);
                }
                if (!in_array($url, $this->crawled)) {
                    $this->crawled[] = $url;
                }
                printf("[+] now crawling >> ".$url." with ".$spider_name."\n");
                printf("[+] Queued ".count($this->queue)." >> Crawled ".count($this->crawled)."\n");
                $this->update();
            }
        }
        catch (Exception $e) {
            printf("[-] extracting links from ".$url." failed.\n");
        }
    }

    private function getDomain($url)
    {
        $pieces = parse_url($url);
        $domain = isset($pieces['host']) ? $pieces['host'] : '';
        if(preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)){
            return $regs['domain'];
        }
        return FALSE;
    }

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

    public function preg_string() : string
    {
        $preg_string = explode(".", $this->TARGET_URL);
        $preg_string = $preg_string[1];
        return $preg_string;
    }

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

    public function check_queue()
    {
        $count = count($this->queue);
        if($count == 0) {
            die();
        }
    }
}