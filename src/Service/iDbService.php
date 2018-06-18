<?php
namespace App\Service;

/*
 * Interface Database service
 * 
 */
interface iDbService
{
    public function findBrand($externalId);
    public function findModel($externalId);
    public function createModel($input, $brand);
    public function findMotorization($externalId);
    public function createMotorization($input, $model, $prefixe);
    public function saveModel($element);
    public function saveMotorization($element);
    public function saveBrand($element);

    public function getExternalId($element) : string;

    public function addModelsToBrand(&$brand, $models);
    public function addMotorizationsToModel(&$model, $motorizations);
}