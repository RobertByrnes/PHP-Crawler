<?php

declare(strict_types=1);
class SaveData
{
    /**
     * file path to queue.txt
     * @var string 
     */
    public string $queue_path;

    /**
     * project name used as directory name in results dir
     * @var string 
     */
    public string $crawled_path;

    /**
     * class SaveData constructor
     * @param string $project_name 
     */
    public function __construct($project_name)
    {
        $this->crawled_path = "results/".$project_name."/crawled.txt";
        $this->queue_path = "results/".$project_name."/queued.txt";
    }

    /**
     * creates the project dir within results in the root
     * @param string $directory
     * @return void
     */
    public function create_dir($directory) : void
    {
        try {
            if (!is_dir($directory)) {
                printf("[+] Creating directory ".$directory." >>\n");
                mkdir("results/".$directory);  
            }
            throw new Exception("directory found.");
        }
        catch (Exception $e) {
            printf("[+]".$e."\n");
        }
    }

    /**
     * creates queue.txt and crawled.txt in the project dir
     * @return void
     */
    public function create_files() : void
    {
        try {
            if (!file_exists($this->crawled_path)) {
                $this->write_file($this->crawled_path, "");
            }
            if (!file_exists($this->queue_path)) {
                $this->write_file($this->queue_path, "");
            }
            throw new Exception("file already created.");
        }
        catch (Exception $e) {
            printf("[+] ".$e."\n");
        }
    }

    /**
     * opens either queue.txt or crawled.txt and parses stream to array
     * @param string $file_name
     * @return array
     */
    public function file_to_array(string $file_name) : array
    {
        $results = [];
        $file = fopen($file_name, "r");
        $lines = [];
        while (True) {
            $line = fgets($file);
            if (!$line)
            {
              break;
            }
            $lines[] = $line;
        }
        foreach ($lines as $line) {
            preg_replace("\n", "", $line);
            $result[] = $line;
        }
        fclose($file);
        return $results;
    }

    /**
     * takes queue array or crawled array and writes to queue.txt or crawled.txt
     * @param array $links
     * @param string $file_name
     * @return void
     */
    public function array_to_file(array $links, string $file_name) : void
    {
        $emptied_file = fopen($file_name, "w");
        asort($links);
        foreach ($links as $link) {
            fwrite($emptied_file, $link."\n");
        }
        fclose($emptied_file);
    }

    /**
     * uses fwrite to open new files, or open - empty - then rewrite
     * @param string $path
     * @param string $data
     * @return void
     */
    public function write_file(string $path, string $data) : void
    {
        $file = fopen($path, "w");
        fwrite($file, $data."\n");
        fclose($file);
    }

    /**
     * appends lines to either queue.txt or crawled.txt
     * @param string $path
     * @param string $data
     * @return void
     */
    public function append_to_file(string $path, string $data) : void
    {
        $file = fopen($path, "a");
        fwrite($file, $data."\n");
        fclose($file);
    }

    /**
     * empties files - unused in PHP-Crawler
     * @param string $path
     * @return void
     */
    public function delete_file_contents(string $path) : void
    {
        $file = fopen($path, "w");
        fclose($file);
    }
}