<?php

namespace tests\unit;

use cebe\yii2openapi\lib\items\Attribute;
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
                                          ->setUnique(true)
                                          ->setSize(60)
                                          ->setRequired(true),
                  (new Attribute('article'))->setPhpType('string')->setDbType('text')->setDefault(''),
                  (new Attribute('active'))->setPhpType('bool')->setDbType('boolean'),
                  (new Attribute('category'))->asReference('Category')
                                             ->setRequired(true)->setPhpType('int')->setDbType('integer'),
                  (new Attribute('state'))->setPhpType('string')->setDbType('string')->setEnumValues(['active', 'draft']),
                  (new Attribute('created_at'))->setPhpType('string')->setDbType('datetime'),
                  (new Attribute('contact_email'))->setPhpType('string')->setDbType('string')
              ],
          ]);
          $expected = [
              new ValidationRule(['title', 'article', 'state', 'created_at', 'contact_email'], 'trim'),
              new ValidationRule(['title', 'category_id'], 'required'),
              new ValidationRule(['category_id'], 'integer'),
              new ValidationRule(['category_id'], 'exist', ['targetRelation'=>'Category']),
              new ValidationRule(['title'], 'unique'),
              new ValidationRule(['title'], 'string', ['max'=>60]),
              new ValidationRule(['article'], 'string'),
              new ValidationRule(['active'], 'boolean'),
              new ValidationRule(['state'], 'string'),
              new ValidationRule(['state'], 'in', ['range'=>['active', 'draft']]),
              new ValidationRule(['created_at'], 'datetime'),
              new ValidationRule(['contact_email'], 'string'),
              new ValidationRule(['contact_email'], 'email'),
          ];

          $rules = (new ValidationRulesBuilder($model))->build();
          $this->assertEquals($expected, $rules);
     }


}