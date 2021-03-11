<?php

class SaveData
{
    public string $queue_path;
    public string $crawled_path;

    public function __construct($project_name)
    {
        $this->crawled_path = "results/".$project_name."/crawled.txt";
        $this->queue_path = "results/".$project_name."/queued.txt";
    }

    public function create_dir($directory)
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

    public function create_files()
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

    public function file_to_array(string $file_name)
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
        return $results;
    }

    public function array_to_file(array $links, $file_name)
    {
        $emptied_file = fopen(file_name, "w");
        asort($links);
        foreach ($links as $link) {
            fwrite($emptied_file, $link."\n");
        }
    }

    public function write_file($path, $data)
    {
        $file = fopen($path, "w");
        fwrite($file, $data."\n");
    }

    public function append_to_file($path, $data)
    {
        $file = fopen($path, "a");
        fwrite($file, $data."\n");
    }

    public function delete_file_contents($path)
    {
        $file = fopen($path, "w");
        fclose($file);
    }
}