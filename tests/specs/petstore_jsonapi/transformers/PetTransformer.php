<?php
namespace app\transformers;

use League\Fractal\TransformerAbstract;
use app\models\Pet;

class PetTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['duplicates'];
    protected $defaultIncludes = [];

    public function transform(Pet $model)
    {
        return $model->getAttributes();
    }

    public function includeDuplicates(Pet $model)
    {
        $relation = $model->duplicates;
        $transformer = new static();
        return $this->collection($relation, $transformer, 'pets');
    }
}
