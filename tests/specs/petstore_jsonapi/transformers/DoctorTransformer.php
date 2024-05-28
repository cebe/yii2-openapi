<?php
namespace app\transformers;

use League\Fractal\TransformerAbstract;
use app\models\Doctor;

class DoctorTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [];
    protected array $defaultIncludes = [];

    public function transform(Doctor $model)
    {
        return $model->getAttributes();
    }
}
