<?php

namespace Models;

use Doctrine\ODM\MongoDB\DocumentManager;

abstract class BaseModel
{
    /**
     * Populate model from array
     *
     * @param DocumentManager $docManager
     * @param array $data
     * @return $this
     */
    public static function populate(DocumentManager $docManager, array $data)
    {
        $model = new static();
        $hf = $docManager->getHydratorFactory();
        $hf->hydrate($model, $data);
        return $model;
    }
}