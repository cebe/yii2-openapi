<?php

namespace tests\unit;

use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Schema;
use cebe\yii2openapi\lib\FakerStubResolver;
use cebe\yii2openapi\lib\SchemaTypeResolver;
use cebe\yii2openapi\lib\items\Attribute;
use tests\TestCase;
use Yii;
use yii\db\Schema as YiiDbSchema;
use const DATE_ATOM;

class FakerStubResolverTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     **/
    public function testResolve(Attribute $column, Schema $property, $expected)
    {
        $resolver = Yii::createObject(['class' => FakerStubResolver::class], [$column, $property]);
        self::assertEquals($expected, $resolver->resolve());
    }

    public function dataProvider()
    {
        $schemaFile = Yii::getAlias("@specs/blog.yaml");
        $openApi = Reader::readFromYamlFile($schemaFile, OpenApi::class, false);
        $schema = $openApi->components->schemas['Fakerable'];
        return [
            [
                (new Attribute('id'))->setPhpType('int')->setDbType(YiiDbSchema::TYPE_BIGPK),
                $schema->properties['id'],
                '$uniqueFaker->numberBetween(0, 1000000)',
            ],
            [
                (new Attribute('someint'))->setPhpType('int')->setDbType(YiiDbSchema::TYPE_BIGPK),
                $schema->properties['id'],
                '$faker->numberBetween(0, 1000000)',
            ],
            [
                (new Attribute('active'))->setPhpType('bool')->setDbType(YiiDbSchema::TYPE_BOOLEAN),
                $schema->properties['active'],
                '$faker->boolean',
            ],
            [
                (new Attribute('floatval'))->setPhpType('float')->setDbType(YiiDbSchema::TYPE_FLOAT),
                $schema->properties['floatval'],
                '$faker->randomFloat()',
            ],
            [
                (new Attribute('doubleval'))
                    ->setPhpType(SchemaTypeResolver::schemaToPhpType($schema->properties['doubleval']))
                    ->setDbType(SchemaTypeResolver::schemaToDbType($schema->properties['doubleval'])),
                $schema->properties['doubleval'],
                '$faker->randomFloat()',
            ],
            [
                (new Attribute('floatval_lim'))
                    ->setPhpType('float')->setDbType(YiiDbSchema::TYPE_FLOAT)
                                         ->setLimits(0, 1, null),
                $schema->properties['floatval_lim'],
                '$faker->randomFloat(null, 0, 1)',
            ],
            [
                (new Attribute('int_simple'))
                    ->setPhpType('int')->setDbType(YiiDbSchema::TYPE_INTEGER),
                $schema->properties['int_simple'],
                '$faker->numberBetween(0, 1000000)',
            ],
            [
                (new Attribute('int_created_at'))
                    ->setPhpType('int')->setDbType(YiiDbSchema::TYPE_INTEGER),
                $schema->properties['int_created_at'],
                '$faker->unixTime',
            ],
            [
                (new Attribute('int_min'))
                    ->setPhpType('int')->setDbType(YiiDbSchema::TYPE_INTEGER)
                    ->setLimits(5, null, null),
                $schema->properties['int_min'],
                '$faker->numberBetween(5, 1000000)',
            ],
            [
                (new Attribute('int_max'))
                    ->setPhpType('int')->setDbType(YiiDbSchema::TYPE_INTEGER)
                    ->setLimits(null, 5, null),
                $schema->properties['int_max'],
                '$faker->numberBetween(0, 5)',
            ],
            [
                (new Attribute('int_minmax'))
                    ->setPhpType('int')->setDbType(YiiDbSchema::TYPE_INTEGER)
                    ->setLimits(5, 25, null),
                $schema->properties['int_minmax'],
                '$faker->numberBetween(5, 25)',
            ],
            [
                (new Attribute('uuid'))->setPhpType('string')->setDbType('uuid'),
                $schema->properties['uuid'],
                '$faker->uuid',
            ],
            [
                (new Attribute('str_text'))->setPhpType('string')->setDbType(YiiDbSchema::TYPE_TEXT),
                $schema->properties['str_text'],
                '$faker->sentence',
            ],
            [
                (new Attribute('str_varchar'))->setPhpType('string')->setDbType(YiiDbSchema::TYPE_STRING),
                $schema->properties['str_varchar'],
                '$faker->sentence',
            ],
            [
                (new Attribute('str_varchar'))->setPhpType('string')->setDbType(YiiDbSchema::TYPE_STRING)->setSize(100),
                $schema->properties['str_varchar'],
                'substr($faker->text(100), 0, 100)',
            ],
            [
                (new Attribute('str_date'))->setPhpType('string')->setDbType(YiiDbSchema::TYPE_DATE),
                $schema->properties['str_date'],
                '$faker->dateTimeThisCentury->format(\'Y-m-d\')',
            ],
            [
                (new Attribute('str_datetime'))->setPhpType('string')->setDbType(YiiDbSchema::TYPE_DATETIME),
                $schema->properties['str_datetime'],
                '$faker->dateTimeThisYear(\'now\', \'UTC\')->format(DATE_ATOM)',
            ],
        ];
    }
}
