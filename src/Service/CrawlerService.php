<?php
// src/Service/CrawlerService.php

namespace App\Service;

use Symfony\Component\Console\Output\OutputInterface;
use App\Service\FirebaseService;

class CrawlerService
{
    /*
     * @var OutputInterface $output
     */
    protected $output;
    
    /**
     * @var iDbAdapterService $dbAdapterService 
     */
    protected $dbAdapterService;
    
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function writeOutput($text, $ln = true)
    {
        if (!is_null($this->output)) {
            if ($ln) {
                $this->output->writeln($text);
            }
            else {
                $this->output->write($text);
            }
        }
    }
    
    /**
     * Constructor 
     * 
     * @param FirebaseService $dbAdapterService
     */
    public function __construct(FirebaseService $dbAdapterService)
    {
        $this->dbAdapterService = $dbAdapterService;
    }
    
    /**
     * Crawl with Tag information
     * 
     * @throws NoTagBrandCrawlerException
     */
    public function crawl()
    {   
        set_time_limit(3600);
        $this->writeOutput('End crawling');      
    }
}