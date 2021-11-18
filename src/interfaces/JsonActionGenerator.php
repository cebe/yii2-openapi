<?php

namespace cebe\yii2openapi\interfaces;

use cebe\yii2openapi\lib\items\FractalAction;

interface JsonActionGenerator
{
    /**
     * @return array|FractalAction[]
     */
    public function generate(): array;
}
