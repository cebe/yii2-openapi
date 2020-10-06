<?php
namespace app\transformers;

use League\Fractal\TransformerAbstract;
use app\models\User;

class UserTransformer extends TransformerAbstract
{
     protected $availableIncludes = [];
     protected $defaultIncludes = [];

     public function transform(User $model)
     {
          return $model->getAttributes();
     }



}
