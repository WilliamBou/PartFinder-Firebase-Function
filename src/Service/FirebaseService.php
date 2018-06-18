<?php
// src/Service/FirebaseService.php
namespace App\Service;

use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Firestore\CollectionReference;
use Symfony\Component\Config\Definition\Exception\Exception;

class FirebaseService implements iDbService
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
        $this->MotorizationsCollectionReference = $this->ds->collection(self::MotorizationsCollection);
        $this->BrandsCollectionReference = $this->ds->collection(self::BrandsCollection);
    } 
    
    public function setExternalProvider($externalProvider)
    {
        $this->externalProvider = $externalProvider;
    }

    /*Find and create*/

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
        return null;
    }
     
    protected function createElement(
            $input, 
            CollectionReference $collection, 
            $parentElement = null,
            CollectionReference $parentElementCollection = null,
            $prefix = "")
    {
        if (!is_null($parentElement) && !isset($parentElement['id'])) {
            throw new Exception('No "id" for parent element');
        } elseif (!is_null($parentElement) && is_null($parentElementCollection)) {
            throw new Exception('No collection name defined for parent element');
        }

        $element = $this->findElement($input['Value'], $collection);

        if (is_null($element)) {
            $element = [
                'children' => [],
                'externalId' => $input['Value']
            ];
        }

        $element['externalProvider'] = $this->externalProvider;
        $element['label'] = trim($prefix . $input['Text']);
        $element['parent'] = $parentElementCollection->document($parentElement['id']);

        return $element;
    }

    public function findBrand($externalId)
    {
        return $this->findElement($externalId, $this->BrandsCollectionReference);
    }
    
    public function findModel($externalId)
    {
        return $this->findElement($externalId, $this->ModelsCollectionReference);
    }
    
    public function createModel($input, $brand)
    {
        return $this->createElement($input, $this->ModelsCollectionReference, $brand, $this->BrandsCollectionReference);
    }
    
    public function findMotorization($externalId)
    {
        return $this->findElement($externalId, $this->MotorizationsCollectionReference);
    }
    
    public function createMotorization($input, $model, $prefixe)
    { 
        return $this->createElement(
            $input,
            $this->MotorizationsCollectionReference,
            $model,
            $this->ModelsCollectionReference,
            $prefixe
        );
    }   

    /*Save*/

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

    public function getExternalId($element) : string
    {
        return $element['externalId'];
    }


    /** add children */

    public function addChildrenElements(
        &$elements,
        array $childrenElements,
        CollectionReference $childrenElementsCollection)
    {
        $children = [];
        foreach ($childrenElements as $childElement){
            if (!isset($childElement['id'])) {
                throw new Exception('No "id" for child element');
            }

            $children[] = $childrenElementsCollection->document($childElement['id']);
        }
        $elements["children"] = $children;
    }

    public function addModelsToBrand(&$brand, $models)
    {
        $this->addChildrenElements($brand, $models, $this->ModelsCollectionReference);
    }

    public function addMotorizationsToModel(&$model, $motorizations)
    {
        $this->addChildrenElements($model, $motorizations, $this->MotorizationsCollectionReference);
    }
}