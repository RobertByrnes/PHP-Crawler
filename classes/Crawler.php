<?php

class Crawler {
    private string $TARGET_URL = "";
    private string $PROJECT_NAME = ""; 
    private string $queue_path = "";
    private Spider $SPIDER;
    private Queue $QUEUE;
    private SaveData $SAVE;

    public function __construct($url, $project_name)
    {
        $this->TARGET_URL = $url;
        $this->PROJECT_NAME = $project_name;
        $this->queue_path = "results/".$this->PROJECT_NAME."/queued_links.txt";
        $this->QUEUE = new Queue;
        $this->SAVE = new SaveData($this->PROJECT_NAME);
        $this->SPIDER = new Spider($this->TARGET_URL, $this->PROJECT_NAME, $this->SAVE);
    }

    // public function spawn()
    // {
    //     print("[+] Creating workforce of " + str(self.WORKERS) + " more spiders >>")
    //     for _ in range(self.WORKERS):
    //         thread = threading.Thread(target=self.accept_job)
    //         thread.daemon = True
    //         thread.start()
    // }
    
    public function accept_job()
    {
        While (TRUE) {
            $link = $this->QUEUE->pop();
            $this->SPIDER->search($spider_name = "Charlotte", $link);
            $this->QUEUE->task_done();
        }
    }

    public function crawl()
    {
        $queued_links = $SAVE->file_to_array($this->queue_path);
        foreach ($queued_links as $link => $value) {
            if (count($queued_links > 0)) {
                if (($this->SPIDER->getDomiain($this->TARGET_URL)) === ($this->SPIDER->getDomain($link))) {
                    print("[+] ".count($queued_links)." queued links awaiting spiders >>");
                    $this->add_job();
                }
            }
        }
    }

    public function add_job()
    {
        $links = $this->Save->file_to_array($this->queue_path);
        foreach ($links as $link) {
            $this->QUEUE->push($link);

        }
        $this->QUEUE->join();
        $this->crawl();
    }
}