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

    public function __construct(string $url, string $name, SaveData $Save)
    {
        $this->PROJECT_NAME = $name;
        $this->TARGET_URL = $url;
        $this->DOMAIN_NAME = $this->getDomain($url);
        $this->Save = $Save;
        $this->queue_path = "results/".$this->PROJECT_NAME."/queued.txt";
        $this->crawled_path = "results/".$this->PROJECT_NAME."/crawled.txt";
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
            if (!$this->sort_to_queue($spider_name, $this->extract_links($url))){
                if (in_array($url, $this->queue)) {
                    unset($this->queue[$url]); // may be wrong index
                }
                $this->update();
            }
            throw new Exception("extracting links from ".$url." failed.");
        }
        catch (Exception $e) {
            printf("[-] ".$e."\n");
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

    private function extract_links($target_url)
    {
        $dom = new DomDocument();
        try {
            if ($response = $dom->loadHTML($target_url)) {
                $links = $dom->getElementsByTagName('a');
                print_r($links);die();
// Yeah, DOMXpath::query returns are always a DOMNodeList, which is a bit of an odd object to deal with. You basically have to iterate over it, or just use item() to get a single item:

// // There's actually something in the list
// if($result->length > 0) {
//   $node = $result->item(0);
//   echo "{$node->nodeName} - {$node->nodeValue}";
// } 
// else {
//   // empty result set
// }
// Or you can loop through the values:

// foreach($result as $node) {
//   echo "{$node->nodeName} - {$node->nodeValue}";
//   // or you can just print out the the XML:
//   // $dom->saveXML($node);
// }
                $urls = [];
                foreach($links as $link) {
                    $url = $link->getAttribute('href');
                    $parsed_url = parse_url($url);
                    if(isset($parsed_url['host']) && $parsed_url['host'] === $this->DOMAIN_NAME) {
                        $urls[] = $url;
                    }
                }
                return $urls;
            }
            throw new Exception("no response from ".$url);
        }
        catch (Exception $e) {
            printf("[-] ".$e."\n");
        }
    }

    private function sort_to_queue($spider_name, $links)
    {
        foreach ($links as $link => $value) {
            if ((in_array($link, $this->crawled)) || (in_array($link, $this->queue))) {
                continue;
            }
            if($this->DOMAIN_NAME != $this->getDomain($link)){
                continue;
            }
            $this->queue[] = $link;
            $this->crawled[] = $link;
            printf("[+] now crawling >> ".$link." with ".$spider_name."\n");
            printf("[+] Queued ".count($this->queue)." >> Crawled ".count($this->crawled)."\n");
        }

    }

    public function update()
    {
        try {
            if (($this->Save->array_to_file($this->queue, $this->queue_path)) &&
            ($this->Save->array_to_file($this->crawled, $this->crawled_path))) {
                return True;
            }
            throw new Exception("failure updating files.");
        }
        catch (Exception $e) {
            printf("[-]: ".$e."\n");
        }
    }
}