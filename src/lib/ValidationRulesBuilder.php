<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib;

use cebe\yii2openapi\lib\items\DbModel;
use cebe\yii2openapi\lib\items\ValidationRule;

class ValidationRulesBuilder
{
    /**
     * @var \cebe\yii2openapi\lib\items\DbModel
     */
    private $model;

    /**
     * @var array|ValidationRule[]
    **/
    private $rules = [];

    private $typeScope = [
        'safe' => [], 'required' => [], 'int' => [], 'bool' => [], 'float' => [], 'string' => [], 'ref' => []
    ];

    public function __construct(DbModel $model)
    {
        $this->model = $model;
    }

    /**
     * @return array|\cebe\yii2openapi\lib\items\ValidationRule[]
     */
    public function build(): array
    {
        $this->prepareTypeScope();
        $this->rulesByType();
        return $this->rules;
    }

    private function prepareTypeScope():void
    {
        foreach ($this->model->attributes as $attribute) {
            if ($attribute->isReadOnly()) {
                continue;
            }
            if ($attribute->isRequired()) {
                $this->typeScope['required'][$attribute->columnName] = $attribute->columnName;
            }

            if ($attribute->isReference()) {
                if (in_array($attribute->phpType, ['int', 'string'])) {
                    $this->typeScope[$attribute->phpType][$attribute->columnName] = $attribute->columnName;
                }
                $this->typeScope['ref'][] = ['attr' => $attribute->columnName, 'rel' => $attribute->camelName()];
                continue;
            }

            if (in_array($attribute->phpType, ['int', 'string', 'bool', 'float'])) {
                $this->typeScope[$attribute->phpType][$attribute->columnName] = $attribute->columnName;
                continue;
            }

            if ($attribute->phpType === 'double') {
                $this->typeScope['float'][$attribute->columnName] = $attribute->columnName;
                continue;
            }

            $this->typeScope['safe'][$attribute->columnName] = $attribute->columnName;
        }
    }

    private function rulesByType():void
    {
        if (!empty($this->typeScope['string'])) {
            $this->rules[] = new ValidationRule($this->typeScope['string'], 'trim');
        }
        if (!empty($this->typeScope['required'])) {
            $this->rules[] = new ValidationRule($this->typeScope['required'], 'required');
        }

        if (!empty($this->typeScope['int'])) {
            $this->rules[] = new ValidationRule($this->typeScope['int'], 'integer');
        }

        foreach ($this->typeScope['ref'] as $relation) {
            $this->rules[] = new ValidationRule([$relation['attr']], 'exist', ['targetRelation'=>$relation['rel']]);
        }

        if (!empty($this->typeScope['string'])) {
            $this->rules[] = new ValidationRule($this->typeScope['string'], 'string');
        }

        if (!empty($this->typeScope['float'])) {
            $this->rules[] = new ValidationRule($this->typeScope['float'], 'double');
        }
        if (!empty($this->typeScope['bool'])) {
            $this->rules[] = new ValidationRule($this->typeScope['bool'], 'boolean');
        }
        if (!empty($this->typeScope['safe'])) {
            $this->rules[] = new ValidationRule($this->typeScope['safe'], 'safe');
        }
    }
}
