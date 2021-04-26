<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib;

use cebe\yii2openapi\lib\items\DbModel;
use cebe\yii2openapi\lib\items\MigrationModel;
use cebe\yii2openapi\lib\migrations\BaseMigrationBuilder;
use cebe\yii2openapi\lib\migrations\MysqlMigrationBuilder;
use cebe\yii2openapi\lib\migrations\PostgresMigrationBuilder;
use Exception;
use yii\base\Component;
use yii\db\Connection;
use yii\di\Instance;
use function in_array;
use function ksort;

class MigrationsGenerator extends Component
{
    /**
     * @var string|array|Connection the Yii database connection component for connecting to the database.
     */
    public $db = 'db';

    /**
     * @var MigrationModel[]
    **/
    private $migrations;
    /**
     * @var MigrationModel[]|bool[]
     **/
    private $sorted;

    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, Connection::class);
    }

    /**
     * @param array|\cebe\yii2openapi\lib\items\DbModel[] $models
     * @return array|\cebe\yii2openapi\lib\items\MigrationModel[]
     * @throws \Exception
     */
    public function generate(array $models):array
    {
        $junctions = [];
        foreach ($models as $model) {
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
    private function createBuilder(DbModel $model):BaseMigrationBuilder
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
    private function sortMigrationsByDeps():array
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
    private function sortByDependencyRecurse(MigrationModel $migration):void
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
