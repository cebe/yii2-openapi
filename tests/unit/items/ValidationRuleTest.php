<?php

namespace tests\unit\items;

use cebe\yii2openapi\lib\items\ValidationRule;
use tests\TestCase;
use function implode;
use function strtolower;

class ValidationRuleTest extends TestCase
{

    /**
     * @dataProvider dataProvider
     * @param \cebe\yii2openapi\lib\items\ValidationRule $rule
     * @param string                                     $expected
     */
    public function testToString(ValidationRule $rule, string $expected):void
    {
        $this->assertEquals($expected, (string)$rule);
    }

    public function testRulesToString()
    {
        $rules = [
            new ValidationRule(['foo'], 'required'),
            new ValidationRule(['foo', 'bar'], 'trim'),
        ];
        $result = implode(",\n", $rules);
        $this->assertEquals($result, "[['foo'], 'required'],\n[['foo', 'bar'], 'trim']");
    }

    public function dataProvider():array
    {
        return [
            [
                new ValidationRule(['foo'], 'required'),
                "[['foo'], 'required']",
            ],
            [
                new ValidationRule(['foo', 'bar'], 'trim'),
                "[['foo', 'bar'], 'trim']",
            ],
            [
                new ValidationRule(['foo'], 'string', ['min' => 1, 'max' => 500]),
                "[['foo'], 'string', 'min' => 1, 'max' => 500]",
            ],
            [
                new ValidationRule(['foo'], 'default', ['value' => null]),
                "[['foo'], 'default', 'value' => null]",
            ],
            [
                new ValidationRule(['foo'], 'filter', ['filter' => 'intval', 'skipOnEmpty' => true]),
                "[['foo'], 'filter', 'filter' => 'intval', 'skipOnEmpty' => true]",
            ],
            [
                new ValidationRule(['foo'], 'in', ['range' => ['one', 'two', 'three']]),
                "[['foo'], 'in', 'range' => ['one', 'two', 'three']]",
            ],
            [
                new ValidationRule(['foo'], 'exist', ['targetAttribute' => ['a2', 'a1' => 'a3']]),
                "[['foo'], 'exist', 'targetAttribute' => ['a2', 'a1' => 'a3']]",
            ],
            [
                new ValidationRule(['foo'], 'filter', [
                    'filter' => function($v) {
                        return strtolower($v);
                    },
                ]),
                "[['foo'], 'filter', 'filter' => '']",
            ],
        ];
    }
}
