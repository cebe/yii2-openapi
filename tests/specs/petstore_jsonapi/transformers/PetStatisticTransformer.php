<?php
namespace app\transformers;

use League\Fractal\TransformerAbstract;
use app\models\PetStatistic;

class PetStatisticTransformer extends TransformerAbstract
{
    protected array $availableIncludes = ['parentPet', 'favoritePets', 'topDoctors'];
    protected array $defaultIncludes = [];

    public function transform(PetStatistic $model)
    {
        return $model->getAttributes();
    }

    public function includeParentPet(PetStatistic $model)
    {
        $relation = $model->parentPet;
        if ($relation === null) {
            return $this->null();
        }
        $transformer = new PetTransformer();
        return $this->item($relation, $transformer, 'pets');
    }

    public function includeFavoritePets(PetStatistic $model)
    {
        $relation = $model->favoritePets;
        $transformer = new PetTransformer();
        return $this->collection($relation, $transformer, 'pets');
    }

    public function includeTopDoctors(PetStatistic $model)
    {
        $relation = $model->topDoctors;
        $transformer = new DoctorTransformer();
        return $this->collection($relation, $transformer, 'doctors');
    }
}
