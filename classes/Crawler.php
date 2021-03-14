<?php

use Amp\Loop;
use Amp\Parallel\Worker\CallableTask;
use Amp\Parallel\Worker\DefaultPool;
/**
 * Class Crawler - manages queueing of tasks and passes work between Queue::class and Spider::class
 * utilising producer/consumer model with queue.
 */
class Crawler {
    /**
     * Homepage of the domain being crawled.
     * @var string
     */
    private string $TARGET_URL = "";

    /**
     * Name of the project, also name of the directory created in root/results/.
     * @var string
     */
    private string $PROJECT_NAME = "";
    
    /**
     * Filepath to queue.txt.
     * @var string
     */
    private string $queue_path = "";

    /**
     * Object of the Spider::class.
     */
    private Spider $SPIDER;

    /**
     * Object of the Queue::class.
     */
    private Queue $QUEUE;

    /**
     * Object of the SaveData::class.
     */
    private SaveData $SAVE;

    /**
     * Number of workers called in (spiders) to crawl for links.
     */
    private int $spawned_spiders;

    public function __construct($url, $project_name, $spawned_spiders)
    {
        $this->TARGET_URL = $url;
        $this->PROJECT_NAME = $project_name;
        $this->queue_path = "results/".$this->PROJECT_NAME."/queued.txt";
        $this->QUEUE = new Queue;
        $this->SAVE = new SaveData($this->PROJECT_NAME, $this->TARGET_URL);
        $this->SPIDER = new Spider($this->TARGET_URL, $this->PROJECT_NAME, $this->SAVE, $this->QUEUE);
        $this->spawned_spiders = $spawned_spiders;
    }

    public function spawn()
    {
        $results= [];
        $tasks = [];
        for ($i=1; $i<=$this->spawned_spiders; $i++) {
            $spider_name = "Spider ".$i;
            $link = $this->QUEUE->pop();
            $tasks [] = new CallableTask($this->SPIDER->search($spider_name,$link),[]);
        }
        Loop::run(function () use (&$results, $tasks) {
            $spiders = new DefaultPool;        
            $coroutines = [];
            foreach ($tasks as $index => $task) {
                $coroutines[] = Amp\call(function () use ($spiders, $index, $task) {
                    $result = yield $spiders->enqueue($task);
                    $this->QUEUE->task_done();
                    return $result;
                });
            }   
            $results = yield Amp\Promise\all($coroutines);
            return yield $spiders->shutdown();
        });
        $this->spawn();
    }

    /**
     * Checks on number of task in the queue and calls add_job()
     *
     * @return void
     */
    public function crawl() : void
    {
        $queued_links = $this->SAVE->file_to_array($this->queue_path);
        foreach ($queued_links as $link => $value) {
            if (count($queued_links > 0)) {
                if (($this->SPIDER->getDomiain($this->TARGET_URL)) == ($this->SPIDER->getDomain($value))) {
                    print("[+] ".count($queued_links)." queued links awaiting spiders >>");
                    $this->add_job();
                }
            }
        }
    }

    /**
     * Reads queue.txt using SaveData::class adding links to the queue
     * pushing the links to the Queue::class. Finally, calls crawl()
     *
     * @return void
     */
    public function add_job() : void
    {
        $links = $this->SAVE->file_to_array($this->queue_path);
        foreach ($links as $link) {
            $this->QUEUE->push($link);

        }
        $this->QUEUE->join();
        $this->crawl();
    }
}