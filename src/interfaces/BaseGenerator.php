<?php

namespace cebe\yii2openapi\interfaces;

use cebe\yii2openapi\generator\GeneratorResult;

interface BaseGenerator
{
    public function generate(): GeneratorResult;
}
