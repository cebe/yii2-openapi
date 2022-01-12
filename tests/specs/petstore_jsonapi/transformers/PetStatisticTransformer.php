<?php
namespace app\transformers;

use League\Fractal\TransformerAbstract;
use app\models\PetStatistic;

class PetStatisticTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['parentPet', 'favoritePets'];
    protected $defaultIncludes = [];

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
}