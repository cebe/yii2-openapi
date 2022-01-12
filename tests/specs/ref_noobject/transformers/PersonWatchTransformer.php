<?php
namespace app\transformers;

use League\Fractal\TransformerAbstract;
use app\models\PersonWatch;

class PersonWatchTransformer extends TransformerAbstract
{
    protected $availableIncludes = [];
    protected $defaultIncludes = [];

    public function transform(PersonWatch $model)
    {
        return $model->getAttributes();
    }
}
