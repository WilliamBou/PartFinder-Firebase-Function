<?php
// src/Service/CrawlerService.php

namespace App\Service;

use App\Service\Crawler\iCrawler;
use App\Service\iDbService;
use Symfony\Component\Console\Output\OutputInterface;

class CrawlerService
{
    /*
     * @var OutputInterface $output
     */
    protected $output;
    
    /**
     * @var iDbService $dbAdapterService
     */
    protected $dbService;

    /**
     * @var iCrawler $crawler
     */
    protected $crawler;
    
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function setDbService(iDbService $dbService)
    {
        $this->dbService = $dbService;
    }

    public function setCrawler(iCrawler $crawler)
    {
        $this->crawler = $crawler;
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
     * Crawl with information
     *
     */
    public function crawl($brand)
    {   
        set_time_limit(3600);

        foreach ($this->crawler->getModels($brand) as $model) {
            $this->writeOutput('Model : ' . $model['id']);

            foreach ($this->crawler->getMotorizations($model) as $motorization) {
                $this->writeOutput('Motorization : ' . $motorization['id']);
            }
        }

        $this->writeOutput('End crawling');      
    }
}