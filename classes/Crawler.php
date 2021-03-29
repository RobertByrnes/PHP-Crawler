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

    public function __construct($url, $project_name, $save)
    {
        $this->TARGET_URL = $url;
        $this->PROJECT_NAME = $project_name;
        $this->queue_path = "results/".$this->PROJECT_NAME."/queued.txt";
        $this->QUEUE = new Queue;
        $this->SAVE = $save;
        $this->SPIDER = new Spider($this->TARGET_URL, $this->PROJECT_NAME, $this->SAVE, $this->QUEUE);
    }

    /**
     * Utilises the Amp\Parallel library to created workers.
     * Workers take a link from the queue and crawl the url for more.
     * This function is recursive.
     *
     * @param int $spawned_spiders
     * @return void
     */
    public function spawn($spawned_spiders) : void
    {
        $results = [];
        $tasks = [];
        for ($i=1; $i<=$spawned_spiders; $i++) {
            $spider_name = "Spider ".$i;
            $link = $this->QUEUE->pop();
            $tasks[] = new CallableTask($this->SPIDER->search($spider_name, $link),[]);
        }
        Loop::run(function() use (&$results, $tasks) {
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
        $this->spawn($spawned_spiders);
    }

    /**
     * Reads queue.txt using SaveData::class adding links to the queue
     * pushing the links to the Queue::class. This function is recursive.
     *
     * @return void
     */
    public function add_job() : void
    {
        $links = $this->SAVE->file_to_array($this->queue_path);
        foreach ($links as $link) {
            $this->QUEUE->push($link);
        }
        $this->add_job();
    }
}