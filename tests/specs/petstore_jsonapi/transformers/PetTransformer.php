<?php
namespace app\transformers;

use League\Fractal\TransformerAbstract;
use app\models\Pet;

class PetTransformer extends TransformerAbstract
{
    protected array $availableIncludes = ['duplicates', 'doctor'];
    protected array $defaultIncludes = [];

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

    public function includeDoctor(Pet $model)
    {
        $relation = $model->doctor;
        if ($relation === null) {
            return $this->null();
        }
        $transformer = new DoctorTransformer();
        return $this->item($relation, $transformer, 'doctors');
    }
}
