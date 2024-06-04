<?php

namespace tests\unit;

use cebe\yii2openapi\lib\items\Attribute;
use cebe\yii2openapi\lib\items\DbIndex;
use cebe\yii2openapi\lib\items\DbModel;
use cebe\yii2openapi\lib\items\ValidationRule;
use cebe\yii2openapi\lib\ValidationRulesBuilder;
use tests\TestCase;
use yii\db\Schema;

class ValidatorRulesBuilderTest extends TestCase
{
    public function testBuild()
    {
        $model = new DbModel([
            'name' => 'dummy',
            'tableName' => 'dummy',
            'attributes' => [
                (new Attribute('id'))->setPhpType('int')->setDbType(Schema::TYPE_PK)
                                     ->setRequired(true)->setReadOnly(true),
                (new Attribute('title'))->setPhpType('string')
                                        ->setDbType('string')
                                        ->setSize(60)
                                        ->setRequired(true),
                (new Attribute('article'))->setPhpType('string')->setDbType('text')->setDefault(''),
                (new Attribute('active'))->setPhpType('bool')->setDbType('boolean'),
                (new Attribute('category'))->asReference('Category')
                                           ->setRequired(true)->setPhpType('int')->setDbType('integer'),
                (new Attribute('state'))->setPhpType('string')->setDbType('string')->setEnumValues(['active', 'draft']),
                (new Attribute('created_at'))->setPhpType('string')->setDbType('datetime'),
                (new Attribute('contact_email'))->setPhpType('string')->setDbType('string'),
                (new Attribute('required_with_def'))->setPhpType('string')
                                                    ->setDbType('string')->setRequired()->setDefault('xxx'),
            ],
            'indexes' => [
                'dummy_title_active_key' => DbIndex::make('dummy', ['title', 'active'], null, true),
            ],
        ]);
        $expected = [
            'trim' => new ValidationRule([
                'title',
                'article',
                'created_at',
                'contact_email',
                'required_with_def',
            ], 'trim'),
            'required' => new ValidationRule(['title', 'category_id', 'required_with_def'], 'required'),
            'category_id_integer' => new ValidationRule(['category_id'], 'integer'),
            'category_id_exist' => new ValidationRule(['category_id'], 'exist', ['targetRelation' => 'Category']),
            'title_active_unique' => new ValidationRule(['title', 'active'], 'unique', [
                'targetAttribute' =>
                    ['title', 'active'],
            ]),
            'title_string' => new ValidationRule(['title'], 'string', ['max' => 60]),
            'article_string' => new ValidationRule(['article'], 'string'),
            'article_default' => new ValidationRule(['article'], 'default', ['value' => '']),
            'active_boolean' => new ValidationRule(['active'], 'boolean'),
            'state_string' => new ValidationRule(['state'], 'string'),
            'state_in' => new ValidationRule(['state'], 'in', ['range' => ['active', 'draft']]),
            'created_at_datetime' => new ValidationRule(['created_at'], 'datetime', ['format' => 'php:Y-m-d H:i:s']),
            'contact_email_string' => new ValidationRule(['contact_email'], 'string'),
            'contact_email_email' => new ValidationRule(['contact_email'], 'email'),
            'required_with_def_string' => new ValidationRule(['required_with_def'], 'string'),
            'required_with_def_default' => new ValidationRule(['required_with_def'], 'default', ['value' => 'xxx']),
        ];

        $rules = (new ValidationRulesBuilder($model))->build();
        $this->assertEquals($expected, $rules);
    }

}
