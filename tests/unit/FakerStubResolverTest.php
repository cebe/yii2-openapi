<?php

namespace tests\unit;

use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;
use cebe\yii2openapi\lib\FakerStubResolver;
use cebe\yii2openapi\lib\items\Attribute;
use cebe\yii2openapi\lib\openapi\PropertyReader;
use cebe\yii2openapi\lib\openapi\SchemaReader;
use tests\TestCase;
use Yii;
use yii\db\Schema as YiiDbSchema;

class FakerStubResolverTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     **/
    public function testResolve(Attribute $column, PropertyReader $property, $expected)
    {
        $resolver = Yii::createObject(['class' => FakerStubResolver::class], [$column, $property]);
        self::assertEquals($expected, $resolver->resolve());
    }

    public function dataProvider()
    {
        $schemaFile = Yii::getAlias("@specs/blog.yaml");
        $openApi = Reader::readFromYamlFile($schemaFile, OpenApi::class, false);
        $openApiSchema = $openApi->components->schemas['Fakerable'];
        $schema = new SchemaReader($openApiSchema);
        return [
            [
                (new Attribute('id'))->setPhpType('int')->setDbType(YiiDbSchema::TYPE_BIGPK),
                $schema->getProperty('id'),
                '$uniqueFaker->numberBetween(0, 1000000)',
            ],
            [
                (new Attribute('someint'))->setPhpType('int')->setDbType(YiiDbSchema::TYPE_BIGPK),
                $schema->getProperty('id'),
                '$faker->numberBetween(0, 1000000)',
            ],
            [
                (new Attribute('active'))->setPhpType('bool')->setDbType(YiiDbSchema::TYPE_BOOLEAN),
                $schema->getProperty('active'),
                '$faker->boolean',
            ],
            [
                (new Attribute('floatval'))->setPhpType('float')->setDbType(YiiDbSchema::TYPE_FLOAT),
                $schema->getProperty('floatval'),
                '$faker->randomFloat()',
            ],
            [
                (new Attribute('doubleval'))
                    ->setPhpType($schema->getProperty('doubleval')->guessPhpType())
                    ->setDbType($schema->getProperty('doubleval')->guessDbType()),
                $schema->getProperty('doubleval'),
                '$faker->randomFloat()',
            ],
            [
                (new Attribute('floatval_lim'))
                    ->setPhpType('float')->setDbType(YiiDbSchema::TYPE_FLOAT)
                    ->setLimits(0, 1, null),
                $schema->getProperty('floatval_lim'),
                '$faker->randomFloat(null, 0, 1)',
            ],
            [
                (new Attribute('int_simple'))
                    ->setPhpType('int')->setDbType(YiiDbSchema::TYPE_INTEGER),
                $schema->getProperty('int_simple'),
                '$faker->numberBetween(0, 1000000)',
            ],
            [
                (new Attribute('int_created_at'))
                    ->setPhpType('int')->setDbType(YiiDbSchema::TYPE_INTEGER),
                $schema->getProperty('int_created_at'),
                '$faker->unixTime',
            ],
            [
                (new Attribute('int_min'))
                    ->setPhpType('int')->setDbType(YiiDbSchema::TYPE_INTEGER)
                    ->setLimits(5, null, null),
                $schema->getProperty('int_min'),
                '$faker->numberBetween(5, 1000000)',
            ],
            [
                (new Attribute('int_max'))
                    ->setPhpType('int')->setDbType(YiiDbSchema::TYPE_INTEGER)
                    ->setLimits(null, 5, null),
                $schema->getProperty('int_max'),
                '$faker->numberBetween(0, 5)',
            ],
            [
                (new Attribute('int_minmax'))
                    ->setPhpType('int')->setDbType(YiiDbSchema::TYPE_INTEGER)
                    ->setLimits(5, 25, null),
                $schema->getProperty('int_minmax'),
                '$faker->numberBetween(5, 25)',
            ],
            [
                (new Attribute('uuid'))->setPhpType('string')->setDbType('uuid'),
                $schema->getProperty('uuid'),
                '$faker->uuid',
            ],
            [
                (new Attribute('str_text'))->setPhpType('string')->setDbType(YiiDbSchema::TYPE_TEXT),
                $schema->getProperty('str_text'),
                '$faker->sentence',
            ],
            [
                (new Attribute('str_varchar'))->setPhpType('string')->setDbType(YiiDbSchema::TYPE_STRING),
                $schema->getProperty('str_varchar'),
                '$faker->sentence',
            ],
            [
                (new Attribute('str_varchar'))->setPhpType('string')->setDbType(YiiDbSchema::TYPE_STRING)->setSize(100),
                $schema->getProperty('str_varchar'),
                'substr($faker->text(100), 0, 100)',
            ],
            [
                (new Attribute('str_date'))->setPhpType('string')->setDbType(YiiDbSchema::TYPE_DATE),
                $schema->getProperty('str_date'),
                '$faker->dateTimeThisCentury->format(\'Y-m-d\')',
            ],
            [
                (new Attribute('str_datetime'))->setPhpType('string')->setDbType(YiiDbSchema::TYPE_DATETIME),
                $schema->getProperty('str_datetime'),
                '$faker->dateTimeThisYear(\'now\', \'UTC\')->format(DATE_ATOM)',
            ],
        ];
    }
}
