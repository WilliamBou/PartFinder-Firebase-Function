<?php
namespace App\Service\Crawler;

/*
 * Interface Crawler
 * 
 */
interface iCrawler
{   
    public function getModels($brand) : array ;
    public function getMotorizations($model) : array;
}