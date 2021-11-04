<?php

namespace cebe\yii2openapi\interfaces;

use cebe\yii2openapi\lib\items\RestAction;

interface RestActionGenerator
{
    /**
     * @return array|RestAction[]
     */
    public function generate(): array;
}
