<?php
namespace App\Service\Crawler;

/**
 * Crawler for Oscaro
 * 
 */
class Oscaro implements iCrawler
{
    /*
     * Construct
     */
    public function __construct() 
    {
        $this->externalId = External::OSCARO;
    }
   
    public function getModels($brand) : Array
    {
        $models = [];
        
        $url = "https://www.oscaro.com/Catalog/SearchEngine/GetModels";    
        $curl = curl_init();

        $postfields = [
            'idOscManufacturer' => $brand->getExternalMapping()
        ];
        
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_COOKIESESSION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postfields);

        $return = curl_exec($curl);
        curl_close($curl);
        
        foreach (json_decode($return, true) as $groupModel) {
            foreach($groupModel['Items'] as $element) {
                if (!$element["Disabled"]) {
                    /* @var Tag $model */
                    $model = $this->createModel($element, $brand);
                    $models->add($model);
                    $this->entityManager->persist($model);
                }  
            } 
            $this->entityManager->flush();
        }
        $this->entityManager->persist($brand);
        $this->entityManager->flush();
        
        return $models;
    }
    
    public function getMotorizations($model) : Array
    {
        $motorizations = [];
        
        $url = "https://www.oscaro.com/Catalog/SearchEngine/GetTypes";    
        $curl = curl_init();

        $postfields = [
            'idOscModel' => $model->getExternalMapping()
        ];
        
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_COOKIESESSION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postfields);

        $return = curl_exec($curl);
        curl_close($curl);
        
        $json = json_decode($return, true);
        
        foreach ($json['datas'] as $groupMotorization) {
            $prefixe = strtok($groupMotorization['Name'], " ") . " ";
            
            foreach($groupMotorization['Items'] as $element) {
                if (!$element["Disabled"]) {
                    /* @var Tag $motorization */
                    $motorization = $this->createMotorization($element, $model, $prefixe);
                    $motorizations->add($motorization);
                    $this->entityManager->persist($motorization);
                }   
            }
            $this->entityManager->flush();
        }
        
        return $motorizations;
    }
}


