<?php

namespace cebe\yii2openapi\interfaces;

use cebe\yii2openapi\lib\items\FractalAction;

interface JsonApiActionGenerator
{
    /**
     * @return array|FractalAction[]
     */
    public function generate(): array;
}
