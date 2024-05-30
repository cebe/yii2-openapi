<?php

namespace tests\unit;

use cebe\yii2openapi\lib\Config;
use cebe\yii2openapi\lib\generators\MigrationsGenerator;
use cebe\yii2openapi\lib\items\Attribute;
use cebe\yii2openapi\lib\items\DbIndex;
use cebe\yii2openapi\lib\items\DbModel;
use cebe\yii2openapi\lib\items\MigrationModel;
use cebe\yii2openapi\lib\migrations\MigrationRecordBuilder;
use tests\TestCase;
use tests\DbTestCase;
use Yii;
use yii\db\Schema;
use yii\db\TableSchema;
use yii\helpers\VarDumper;
use function count;
use function preg_replace;

class MigrationsGeneratorTest extends DbTestCase
{

    public function testNoMigrations()
    {
        $this->prepareTempDir();
        // $this->mockApplication($this->mockDbSchemaAsEmpty());
        $this->mockApplication();
        $model = new DbModel(['name' => 'dummy', 'tableName' => 'dummy', 'attributes' => []]);
        $generator = new MigrationsGenerator(new Config(), [$model], Yii::$app->db);
        $migrations = $generator->buildMigrations();
        self::assertEmpty($migrations);
    }

    /**
     * @dataProvider simpleDbModelsProvider
     * @param array|DbModel[]        $dbModels
     * @param array|MigrationModel[] $expected
     * @throws \Exception
     */
    public function testGenerateSimple(array $dbModels, array $expected):void
    {
        $this->prepareTempDir();
        // $this->mockApplication($this->mockDbSchemaAsEmpty());
        // $this->mockRealApplication();
        $this->mockApplication();
        $generator = new MigrationsGenerator(new Config(), $dbModels, Yii::$app->db);
        $models = $generator->buildMigrations();
        $model = \array_values($models)[0];
        self::assertInstanceOf(MigrationModel::class, $model);
        self::assertEquals($expected[0]->fileName, $model->fileName);
        self::assertEquals($expected[0]->dependencies, $model->dependencies);
        self::assertCount(count($expected[0]->upCodes), $model->upCodes);
        self::assertCount(count($expected[0]->downCodes), $model->downCodes);
        self::assertEquals(
            preg_replace('~\s{1,}~',' ',trim($expected[0]->getUpCodeString())),
            preg_replace('~\s{1,}~',' ',trim($model->getUpCodeString()))
        );
        self::assertEquals(
            preg_replace('~\s{1,}~',' ',trim($expected[0]->getDownCodeString())),
            preg_replace('~\s{1,}~',' ',trim($model->getDownCodeString()))
        );
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
                                        ->setSize(60)
                                        ->setRequired(true),
                (new Attribute('article'))->setPhpType('string')->setDbType('text'), // for Mysql default in text data type is not allowed
            ],
            'indexes'=> [
                'dummy_title_index' => DbIndex::make('dummy', ['title']),
                'dummy_article_hash_index' => DbIndex::make('dummy', ['article'], 'hash'),
                'dummy_article_key' => DbIndex::make('dummy', ['article'], null, true),
            ]
        ]);
        $codes = str_replace(PHP_EOL,
            PHP_EOL . MigrationRecordBuilder::INDENT,
            VarDumper::export([
                'id' => '$this->primaryKey()',
                'title' => '$this->string(60)->notNull()',
                'article' => '$this->text()->null()',
            ]));
        $expect = new MigrationModel($dbModel, true, null, [
            'dependencies' => [],
            'upCodes' => [
                '$this->createTable(\'{{%dummy}}\', [
                    \'id\' => $this->primaryKey(),
                    \'title\' => $this->string(60)->notNull(),
                    \'article\' => $this->text()->null(),
                ]);',
                "\$this->createIndex('dummy_title_index', '{{%dummy}}', 'title', false);",
                "\$this->createIndex('dummy_article_hash_index', '{{%dummy}}', 'article', 'hash');",
                "\$this->createIndex('dummy_article_key', '{{%dummy}}', 'article', true);",
            ],
            'downCodes' => [
                "\$this->dropIndex('dummy_article_key', '{{%dummy}}');",
                "\$this->dropIndex('dummy_article_hash_index', '{{%dummy}}');",
                "\$this->dropIndex('dummy_title_index', '{{%dummy}}');",
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

    public function simpleDbModelsProviderForNonMysqlDb():array
    {
        $dbModel = new DbModel([
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
            ],
            'indexes'=> [
                'dummy_title_index' => DbIndex::make('dummy', ['title']),
                'dummy_article_hash_index' => DbIndex::make('dummy', ['article'], 'hash'),
                'dummy_article_key' => DbIndex::make('dummy', ['article'], null, true),
            ]
        ]);
        $expect = new MigrationModel($dbModel, true, null, [
            'dependencies' => [],
            'upCodes' => [
                '$this->createTable(\'{{%dummy}}\', [
                    \'id\' => $this->primaryKey(),
                    \'title\' => $this->string(60)->notNull(),
                    \'article\' => $this->text()->null()->defaultValue(\'\'),
                ]);',
                "\$this->createIndex('dummy_title_index', '{{%dummy}}', 'title', false);",
                "\$this->createIndex('dummy_article_hash_index', '{{%dummy}}', 'article', 'hash');",
                "\$this->createIndex('dummy_article_key', '{{%dummy}}', 'article', true);",
            ],
            'downCodes' => [
                "\$this->dropIndex('dummy_article_key', '{{%dummy}}');",
                "\$this->dropIndex('dummy_article_hash_index', '{{%dummy}}');",
                "\$this->dropIndex('dummy_title_index', '{{%dummy}}');",
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

    /**
     * TODO refactor
     * @dataProvider simpleDbModelsProviderForNonMysqlDb
     * @param array|DbModel[]        $dbModels
     * @param array|MigrationModel[] $expected
     * @throws \Exception
     */
    public function testGenerateSimpleNonMysql(array $dbModels, array $expected):void
    {
        $this->prepareTempDir();
        // $this->mockApplication($this->mockDbSchemaAsEmpty());
        // $this->mockRealApplication();
        $this->mockApplication();
        foreach(['maria', 'pgsql'] as $database) {
            if ($database === 'maria') {
                $this->changeDbToMariadb();
            }
            if ($database === 'pgsql') {
                $this->changeDbToPgsql();
            }
            $generator = new MigrationsGenerator(new Config(), $dbModels, Yii::$app->{$database});
            // var_dump(Yii::$app->db); die;
            $models = $generator->buildMigrations();
            // var_dump($models); die;
            $model = \array_values($models)[0];
            self::assertInstanceOf(MigrationModel::class, $model);
            self::assertEquals($expected[0]->fileName, $model->fileName);
            self::assertEquals($expected[0]->dependencies, $model->dependencies);
            self::assertCount(count($expected[0]->upCodes), $model->upCodes);
            self::assertCount(count($expected[0]->downCodes), $model->downCodes);
            self::assertEquals(
                preg_replace('~\s{1,}~',' ',trim($expected[0]->getUpCodeString())),
                preg_replace('~\s{1,}~',' ',trim($model->getUpCodeString()))
            );
            self::assertEquals(
                preg_replace('~\s{1,}~',' ',trim($expected[0]->getDownCodeString())),
                preg_replace('~\s{1,}~',' ',trim($model->getDownCodeString()))
            );
        }
    }
}
