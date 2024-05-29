<?php
namespace app\transformers;

use League\Fractal\TransformerAbstract;
use app\models\PersonWatch;

class PersonWatchTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [];
    protected array $defaultIncludes = [];

    public function transform(PersonWatch $model)
    {
        return $model->getAttributes();
    }
}
