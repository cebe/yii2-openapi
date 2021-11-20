<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\generators;

use cebe\yii2openapi\lib\CodeFiles;
use cebe\yii2openapi\lib\Config;
use cebe\yii2openapi\lib\items\DbModel;
use cebe\yii2openapi\lib\items\MigrationModel;
use cebe\yii2openapi\lib\migrations\BaseMigrationBuilder;
use cebe\yii2openapi\lib\migrations\MysqlMigrationBuilder;
use cebe\yii2openapi\lib\migrations\PostgresMigrationBuilder;
use Exception;
use Yii;
use yii\db\Connection;
use yii\gii\CodeFile;
use const YII_ENV_TEST;

class MigrationsGenerator
{
    /**
     * @var \cebe\yii2openapi\lib\Config
     */
    protected $config;

    /**
     * @var array|\cebe\yii2openapi\lib\items\DbModel[]
     */
    protected $models;

    /**
     * @var CodeFiles $files
     **/
    protected $files;

    /**
     * @var \yii\db\Connection
     */
    protected $db;

    /**
     * @var MigrationModel[]
     **/
    protected $migrations;
    /**
     * @var MigrationModel[]|bool[]
     **/
    protected $sorted;

    public function __construct(Config $config, array $models, Connection $db)
    {
        $this->config = $config;
        $this->models = array_filter($models, static function ($model) {
            return !$model->isNotDb;
        });
        $this->files = new CodeFiles([]);
        $this->db = $db;
    }

    /**
     * @throws \yii\base\NotSupportedException
     * @throws \yii\base\InvalidConfigException
     */
    public function generate():CodeFiles
    {
        if (!$this->config->generateMigrations) {
            return $this->files;
        }
        $migrationModels = $this->buildMigrations();
        $migrationPath = Yii::getAlias($this->config->migrationPath);
        $migrationNamespace = $this->config->migrationNamespace;
        $isTransactional = Yii::$app->db->getDriverName() === 'pgsql';//Probably some another yet

        // TODO start $i by looking at all files, otherwise only one generation per hours causes correct order!!!

        $i = 0;
        foreach ($migrationModels as $migration) {
            // migration files get invalidated directly after generating,
            // if they contain a timestamp use fixed time here instead
            do {
                $date = YII_ENV_TEST ? '200000_00' : '';
                $className = $migration->makeClassNameByTime($i, $migrationNamespace, $date);
                $i++;
            } while (file_exists(Yii::getAlias("$migrationPath/$className.php")));

            $this->files->add(new CodeFile(
                Yii::getAlias("$migrationPath/$className.php"),
                $this->config->render(
                    'migration.php',
                    [
                        'isTransactional' => $isTransactional,
                        'namespace' => $migrationNamespace,
                        'migration' => $migration,
                    ]
                )
            ));
        }
        return $this->files;
    }

    /**
     * @return array|\cebe\yii2openapi\lib\items\MigrationModel[]
     * @throws \yii\base\NotSupportedException
     * @throws \Exception
     */
    public function buildMigrations(): array
    {
        $junctions = [];
        foreach ($this->models as $model) {
            $migration = $this->createBuilder($model)->build();
            if ($migration->notEmpty()) {
                $this->migrations[$model->tableAlias] = $migration;
            }
            foreach ($model->many2many as $relation) {
                if ($relation->hasViaModel === true || in_array($relation->viaTableName, $junctions, true)) {
                    continue;
                }
                $migration = $this->createBuilder($model)->buildJunction($relation);
                if ($migration->notEmpty()) {
                    $this->migrations[$relation->viaTableAlias] = $migration;
                }
                $junctions[] = $relation->viaTableName;
            }
        }
        return !empty($this->migrations) ? $this->sortMigrationsByDeps() : [];
    }

    /**
     * @throws \yii\base\NotSupportedException
     */
    protected function createBuilder(DbModel $model):BaseMigrationBuilder
    {
        if ($this->db->getDriverName() === 'pgsql') {
            return new PostgresMigrationBuilder($this->db, $model);
        }
        return new MysqlMigrationBuilder($this->db, $model);
    }

    /**
     * @return array|MigrationModel[]
     * @throws \Exception
     */
    protected function sortMigrationsByDeps():array
    {
        $this->sorted = [];
        ksort($this->migrations);
        foreach ($this->migrations as $migration) {
            //echo "adding {$migration->tableAlias}\n";
            $this->sortByDependencyRecurse($migration);
        }
        return $this->sorted;
    }

    /**
     * @param \cebe\yii2openapi\lib\items\MigrationModel $migration
     * @throws \Exception
     */
    protected function sortByDependencyRecurse(MigrationModel $migration):void
    {
        if (!isset($this->sorted[$migration->tableAlias])) {
            $this->sorted[$migration->tableAlias] = false;
            foreach ($migration->dependencies as $dependency) {
                if (!isset($this->migrations[$dependency])) {
                    //echo "skipping dep $dependency\n";
                    continue;
                }
                //echo "adding dep $dependency\n";
                $this->sortByDependencyRecurse($this->migrations[$dependency]);
            }
            unset($this->sorted[$migration->tableAlias]);//necessary for provide valid order
            $this->sorted[$migration->tableAlias] = $migration;
        } elseif ($this->sorted[$migration->tableAlias] === false) {
            throw new Exception("A circular dependency is detected for table '{$migration->tableAlias}'.");
        }
    }
}
