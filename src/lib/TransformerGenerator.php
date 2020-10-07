<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib;

use cebe\yii2openapi\lib\items\DbModel;
use cebe\yii2openapi\lib\items\Transformer;

class TransformerGenerator
{
    /**
     * @var array|\cebe\yii2openapi\lib\items\DbModel[]
     */
    private $models;

    /**
     * @var string
     */
    private $transformerNamespace;

    /**
     * @var string
     */
    private $modelNamespace;

    /**
     * @var array
     */
    private $usedTransformers;

    /**
     * @var bool
     */
    private $singularResourceKeys;

    public function __construct(
        array $models,
        array $usedTransformers,
        string $transformerNamespace,
        string $modelNamespace,
        bool $singularResourceKeys
    ) {
        $this->models = $models;
        $this->transformerNamespace = $transformerNamespace;
        $this->modelNamespace = $modelNamespace;
        $this->usedTransformers = $usedTransformers;
        $this->singularResourceKeys = $singularResourceKeys;
    }

    /**
     * @return array|Transformer[]
     */
    public function generate():array
    {
        $transformers = \array_map(function (DbModel $model) {
            return new Transformer($model, $this->transformerNamespace, $this->modelNamespace, $this->singularResourceKeys);
        }, $this->models);
        return \array_filter($transformers, function (Transformer $transformer) {
            return \in_array($transformer->getFQN(), $this->usedTransformers);
        });
    }
}
