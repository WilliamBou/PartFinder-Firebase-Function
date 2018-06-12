<?php
// src/Service/FirebaseService.php
namespace App\Service;

use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Firestore\CollectionReference;

class FirebaseService
{
    const BrandsCollection= 'Brands';
    const ModelsCollection = 'Models';
    const MotorizationsCollection= 'Motorizations';
    
    /* 
     * @var FirestoreClient $datastore
     */
    protected $ds;
    
    /**
     *
     * @var string $externalProvider 
     */
    protected $externalProvider;
    
    /**
     *
     * @var CollectionReference $ModelsCollectionReference
     */
    protected $ModelsCollectionReference;
    
    /**
     *
     * @var CollectionReference $MotorizationCollectionReference
     */
    protected $MotorizationsCollectionReference;
    
    /**
     *
     * @var CollectionReference $BrandsCollectionReference
     */
    protected $BrandsCollectionReference;
    
    /*
     * https://grpc.io/docs/quickstart/php.html
     * https://firebase.google.com/docs/firestore/quickstart?authuser=3
     */  
    public function __construct()
    {
        // Create the Cloud Firestore client
        $this->ds = new FirestoreClient();
        $this->ModelsCollectionReference = $this->ds->collection(self::ModelsCollection);
        $this->MotorizationsCollectionReference = $this->ds->collection(self::ModelsCollection);
        $this->BrandsCollectionReference = $this->ds->collection(self::BrandsCollection);
    } 
    
    public function setExternalProvider($externalProvider)
    {
        $this->externalProvider = $externalProvider;
    }
    
    protected function findElement($externalId, CollectionReference $collection)
    {
        $query = $collection->where('externalId', '=', $externalId);
        $documents = $query->documents();
        
        foreach ($documents as $document) {
            if ($document->exists()) {
                $element = $document->data();
                $element["id"] = $document->id();
                
                return $element;
            }
        }
        return $element;
    }
     
    protected function createElement(
            $input, 
            CollectionReference $collection, 
            $parentElement,
            $prefix = "") : Array
    {
        $element = $this->findElement($input['Value'], $collection);
                
        if (is_null($element)) {
            $element = [
                'children' => [],
                'externalId' => $input['Value'],
                'externalProvider' => $this->externalProvider
            ];
        }
        $element[trim($prefix . $input['Text'])];
        
        return $element;
    }
    
    public function findModel($externalId)
    {
        return $this->findElement($externalId, $this->ModelsCollectionReference);
    }
    
    public function createModel($input, $brand) : Array
    {
        return $this->createElement($input, $this->ModelsCollectionReference, $brand);
    }
    
    public function findMotorization($externalId)
    {
        return $this->findElement($externalId, $this->MotorizationsCollectionReference);
    }
    
    public function createMotorization($input, $model, $prefixe) : Array 
    { 
        return $this->createElement(
                $input,
                $this->MotorizationsCollectionReference, 
                $model, 
                $prefixe
        );
    }   

    protected function saveElement($element, CollectionReference $collection)
    {
        $id = null;
        
        if (isset($element['id'])) {
            $id = $element['id'];
            unset($element['id']);
            $collection->document($id)->set($element);
        } else {
            $new = $collection->add($element);
            $id = $new->id();
        }
        $element['id'] = $id;
        
        return $element;
    }
    
    public function saveModel($element)
    {
        return $this->saveElement($element, $this->ModelsCollectionReference);
    } 
            
    public function saveMotorization($element)
    {
        return $this->saveElement($element, $this->MotorizationsCollectionReference);
    }
    
    public function saveBrand($element)
    {
        return $this->saveElement($element, $this->BrandsCollectionReference);
    }
}