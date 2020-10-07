<?php
namespace app\transformers;

use League\Fractal\TransformerAbstract;
use app\models\Pet;

class PetTransformer extends TransformerAbstract
{
     protected $availableIncludes = [];
     protected $defaultIncludes = [];

     public function transform(Pet $model)
     {
          return $model->getAttributes();
     }



}
