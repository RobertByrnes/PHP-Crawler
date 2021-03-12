<?php

ini_set("memory_limit", "-1");
/**
 * Class Queue. A first-in, first-out data structure. 
 */
class Queue
{
    /**
     * An array to hold the queue.
     * @var array
     */
    public array $queue = [];

    /**
     * A counter to track the number of tasks taken out of the queue, will block until tasks completed.
     * @var integer
     */
    protected $unfinished_tasks = 0;
 
    public function __construct()
    {

    }

    /**
     * Add an item to the end of the queue.
     * 
     * @param mixed $item The item.
     */
    public function push($item)
    {
        $this->queue[] = $item;
    }

    /**
     * Take an item off the head of the queue.
     * 
     * @return mixed The item. 
     */
    public function shift()
    {
        return array_shift($this->queue);
    }

    /**
     * Take an item off the end of the queue.
     * 
     * @return mixed The item. 
     */
    public function pop()
    {
        try {
            if($this->unfinished_tasks == 0) {
                if (!$this->open_job()) {
                    throw new Exception(" the queue is empty."); 
                }
                return array_pop($this->queue);  
            }
        }
        catch (Exception $e) {
            print("[-] ".$e."\n");
        }         
    }

    /**
     * Increment count of tasks taken out of the queue.
     *
     * @return void
     */
    public function open_job()
    {
        if ($this->getCount() > 0) {
            $this->unfinished_tasks++;
        }
        return True;
        
    }

    /**
     * Decrement count of unfinished tasks.
     *
     * @return void
     */
    public function task_done() : void
    {
        if ($this->unfinished_tasks > 0) {
            $this->unfinished_tasks--;
        }
    }

    /**
     * Get the total number of items in the queue
     * 
     * @return int The number of items
     */
    public function getCount() : int
    {
        return count($this->queue);
    }
}