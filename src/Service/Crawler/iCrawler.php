<?php
namespace App\Service\Crawler;

/*
 * Interface Crawler
 * 
 */
interface iCrawler
{   
    public function getModels($brand) : Array;
    public function getMotorizations($model) : Array;
}