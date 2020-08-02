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
                                             ->setRequired(true)->setPhpType('int')->setDbType('integer')
              ],
          ]);
          $expected = [
              new ValidationRule(['title', 'article'], 'trim'),
              new ValidationRule(['title', 'category_id'], 'required'),
              new ValidationRule(['category_id'], 'integer'),
              new ValidationRule(['category_id'], 'exist', ['targetRelation'=>'Category']),
              new ValidationRule(['title', 'article'], 'string'),
              new ValidationRule(['active'], 'boolean'),
          ];

          $rules = (new ValidationRulesBuilder($model))->build();
          $this->assertEquals($expected, $rules);
     }


}