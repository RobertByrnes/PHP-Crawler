<?php

/**
 * Queue
 * 
 * A first-in, first-out data structure. 
 */
class Queue
{
    public array $queue = [];
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
        // if ($this->getCount() == static::MAX_ITEMS) {
        //     throw new QueueException ("Queue is full.");
        // }
        $this->queue[] = $item;
    }

    /**
     * Take an item off the head of the queue.
     * 
     * @return ,ixed The item. 
     */
    public function shift()
    {
        return array_shift($this->queue);
    }

    /**
     * Take an item off the end of the queue.
     * 
     * @return ,ixed The item. 
     */
    public function pop()
    {
        // try {
        //     if($this->unfinished_tasks == 0) {
        //         if (!$this->open_job()) {
        //             throw new Exception(" the queue is empty."); 
        //         }
                return array_pop($this->queue);  
            // }
        // }
        // catch (Exception $e) {
        //     print("[-] ".$e."\n");
        // }         
    }

    public function open_job(Type $var = null)
    {
        if ($this->getCount() > 0) {
            $this->unfinished_tasks++;
        }
        return True;
        
    }

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