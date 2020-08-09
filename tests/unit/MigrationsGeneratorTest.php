<?php

namespace tests\unit;

use cebe\yii2openapi\lib\items\Attribute;
use cebe\yii2openapi\lib\items\DbModel;
use cebe\yii2openapi\lib\items\MigrationModel;
use cebe\yii2openapi\lib\MigrationRecordBuilder;
use cebe\yii2openapi\lib\MigrationsGenerator;
use tests\TestCase;
use yii\db\Schema;
use yii\db\TableSchema;
use yii\helpers\VarDumper;
use function count;

class MigrationsGeneratorTest extends TestCase
{

    /**
     * @dataProvider simpleDbModelsProvider
     * @param array|DbModel[]        $dbModels
     * @param array|MigrationModel[] $expected
     * @throws \Exception
     */
    public function testGenerateSimple(array $dbModels, array $expected):void
    {
        $this->prepareTempDir();
        $this->mockApplication($this->mockDbSchemaAsEmpty());
        $generator = new MigrationsGenerator();
        $models = $generator->generate($dbModels);
        $model = \array_values($models)[0];
        self::assertInstanceOf(MigrationModel::class, $model);
        self::assertEquals($expected[0]->fileName, $model->fileName);
        self::assertEquals($expected[0]->dependencies, $model->dependencies);
        self::assertCount(count($expected[0]->upCodes), $model->upCodes);
        self::assertCount(count($expected[0]->downCodes), $model->downCodes);
        self::assertEquals(trim($expected[0]->getUpCodeString()), trim($model->getUpCodeString()));
        self::assertEquals(trim($expected[0]->getDownCodeString()), trim($model->getDownCodeString()));
    }

    public function tableSchemaStub(string $tableName):?TableSchema
    {
        $stub = [];
        return $stub[$tableName] ?? null;
    }

    public function simpleDbModelsProvider():array
    {
        $dbModel = new DbModel([
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
            ],
        ]);
        $codes = str_replace(PHP_EOL,
            PHP_EOL . MigrationRecordBuilder::INDENT,
            VarDumper::export([
                'id' => '$this->primaryKey()',
                'title' => '$this->string(60)->notNull()->unique()',
                'article' => '$this->text()->null()->defaultValue("")',
            ]));
        $expect = new MigrationModel($dbModel, true, [
            'dependencies' => [],
            'upCodes' => [
                "\$this->createTable('{{%dummy}}', $codes);",
            ],
            'downCodes' => [
                "\$this->dropTable('{{%dummy}}');",
            ],
        ]);
        return [
            [
                [$dbModel],
                [$expect],
            ],
        ];
    }
}
