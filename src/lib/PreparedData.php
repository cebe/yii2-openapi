<?php

namespace cebe\yii2openapi\lib;

use yii\base\BaseObject;

class PreparedData extends BaseObject
{
    /**
     * @var array|\cebe\yii2openapi\lib\items\DbModel[]
     */
    public $models;

    /**
     * @var array|\cebe\yii2openapi\lib\items\RestAction[]|\cebe\yii2openapi\lib\items\FractalAction[]
     */
    public $actions;
}
