<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib;

use cebe\yii2openapi\lib\items\Attribute;
use cebe\yii2openapi\lib\items\DbModel;
use cebe\yii2openapi\lib\items\ValidationRule;
use function in_array;
use function preg_match;
use function strtolower;

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
        'required' => [],
        'ref' => [],
        'trim' => [],
        'safe' => [],
    ];

    public function __construct(DbModel $model)
    {
        $this->model = $model;
    }

    /**
     * @return array|\cebe\yii2openapi\lib\items\ValidationRule[]
     */
    public function build():array
    {
        $this->prepareTypeScope();

        if (!empty($this->typeScope['trim'])) {
            $this->rules[] = new ValidationRule($this->typeScope['trim'], 'trim');
        }

        if (!empty($this->typeScope['required'])) {
            $this->rules[] = new ValidationRule($this->typeScope['required'], 'required');
        }
        if (!empty($this->typeScope['ref'])) {
            $this->addExistRules($this->typeScope['ref']);
        }
        foreach ($this->model->attributes as $attribute) {
            $this->resolveAttributeRules($attribute);
        }
        if (!empty($this->typeScope['safe'])) {
            $this->rules[] = new ValidationRule($this->typeScope['safe'], 'safe');
        }
        return $this->rules;
    }

    private function resolveAttributeRules(Attribute $attribute):void
    {
        if ($attribute->isReadOnly()) {
            return;
        }
        if ($attribute->isUnique()) {
            $this->rules[] = new ValidationRule([$attribute->columnName], 'unique');
        }
        if ($attribute->phpType === 'bool') {
            $this->rules[] = new ValidationRule([$attribute->columnName], 'boolean');
            return;
        }

        if (in_array($attribute->dbType, ['date', 'time', 'datetime'], true)) {
            $this->rules[] = new ValidationRule([$attribute->columnName], $attribute->dbType, []);
            return;
        }
        if (in_array($attribute->phpType, ['int', 'double', 'float']) && !$attribute->isReference()) {
            $this->addNumericRule($attribute);
            return;
        }
        if ($attribute->phpType === 'string' && !$attribute->isReference()) {
            $this->addStringRule($attribute);
        }
        if (!empty($attribute->enumValues)) {
            $this->rules[] = new ValidationRule([$attribute->columnName], 'in', ['range' => $attribute->enumValues]);
            return;
        }
        $this->addRulesByAttributeName($attribute);
    }

    private function addRulesByAttributeName(Attribute $attribute):void
    {
        //@TODO: probably also patterns for file, image
        $patterns = [
            '~e?mail~i' => 'email',
            '~(url|site|website|href|link)~i' => 'url',
            '~(ip|ipaddr)~i' => 'ip',
        ];
        foreach ($patterns as $pattern => $validator) {
            if (preg_match($pattern, strtolower($attribute->columnName))) {
                $this->rules[] = new ValidationRule([$attribute->columnName], $validator);
                return;
            }
        }
    }

    /**
     * @param array|Attribute[] $relations
     */
    private function addExistRules(array $relations):void
    {
        foreach ($relations as $attribute) {
            if ($attribute->phpType === 'int') {
                $this->addNumericRule($attribute);
            } elseif ($attribute->phpType === 'string') {
                $this->addStringRule($attribute);
            }
            $this->rules[] = new ValidationRule(
                [$attribute->columnName],
                'exist',
                ['targetRelation' => $attribute->camelName()]
            );
        }
    }

    private function addStringRule(Attribute $attribute):void
    {
        $params = [];
        if ($attribute->maxLength === $attribute->minLength && $attribute->minLength !== null) {
            $params['length'] = $attribute->minLength;
        } else {
            if ($attribute->minLength !== null) {
                $params['min'] = $attribute->minLength;
            }
            if ($attribute->maxLength !== null) {
                $params['max'] = $attribute->maxLength;
            }
        }
        $this->rules[] = new ValidationRule([$attribute->columnName], 'string', $params);
    }

    private function addNumericRule(Attribute $attribute):void
    {
        $params = [];
        if ($attribute->limits['min'] !== null) {
            $params['min'] = $attribute->limits['min'];
        }
        if ($attribute->limits['max'] !== null) {
            $params['max'] = $attribute->limits['max'];
        }
        $validator = $attribute->phpType === 'int' ? 'integer' : 'double';
        $this->rules[] = new ValidationRule([$attribute->columnName], $validator, $params);
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

            if ($attribute->phpType === 'string') {
                $this->typeScope['trim'][$attribute->columnName] = $attribute->columnName;
            }

            if ($attribute->isReference()) {
                $this->typeScope['ref'][] = $attribute;
                continue;
            }

            if (in_array($attribute->phpType, ['int', 'string', 'bool', 'float', 'double'])) {
                continue;
            }

            $this->typeScope['safe'][$attribute->columnName] = $attribute->columnName;
        }
    }
}
