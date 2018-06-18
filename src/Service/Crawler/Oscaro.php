<?php
namespace App\Service\Crawler;

use App\Service\iDbService;

/**
 * Crawler for Oscaro
 * 
 */
class Oscaro implements iCrawler
{

    /**
     * @var iDbService $dbService
     */
    protected $dbService;
    /*
     * Construct
     */
    public function __construct(iDbService $dbService)
    {
        $this->dbService = $dbService;
    }
   
    public function getModels($brand) : array
    {
        $models = [];
        
        $url = "https://www.oscaro.com/Catalog/SearchEngine/GetModels";    
        $curl = curl_init();

        $postfields = [
            'idOscManufacturer' => $this->dbService->getExternalId($brand)
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
                    $model = $this->dbService->createModel($element, $brand);
                    $models[] = $this->dbService->saveModel($model);
                }  
            }
        }

        $this->dbService->addModelsToBrand($brand, $models);
        $this->dbService->saveBrand($brand);

        return $models;
    }
    
    public function getMotorizations($model) : array
    {
        $motorizations = [];
        
        $url = "https://www.oscaro.com/Catalog/SearchEngine/GetTypes";    
        $curl = curl_init();

        $postfields = [
            'idOscModel' => $this->dbService->getExternalId($model)
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
                    $motorization = $this->dbService->createMotorization($element, $model, $prefixe);
                    $motorizations[] = $this->dbService->saveMotorization($motorization);
                }   
            }
        }

        $this->dbService->addMotorizationsToModel($model, $motorizations);
        $this->dbService->saveModel($model);

        return $motorizations;
    }
}


