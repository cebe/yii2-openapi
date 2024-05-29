<?php
namespace app\transformers\base;

use League\Fractal\TransformerAbstract;
use app\models\User;

class UserTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [];
    protected array $defaultIncludes = [];

    public function transform(User $model)
    {
        return $model->getAttributes();
    }
}
