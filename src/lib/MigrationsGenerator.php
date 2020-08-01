<?php

namespace cebe\yii2openapi\lib;

use cebe\yii2openapi\lib\items\MigrationModel;
use Exception;
use yii\base\Component;
use yii\db\Connection;
use yii\di\Instance;
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
     * @var MigrationModel[]
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
        foreach ($models as $model) {
            $migration = (new MigrationBuilder($this->db, $model))->build();
            if ($migration->notEmpty()) {
                $this->migrations[$model->tableAlias] = $migration;
            }
        }
        return $this->sortMigrationsByDeps();
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