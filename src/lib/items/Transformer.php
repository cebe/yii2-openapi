<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\items;

use yii\base\BaseObject;

/**
 * @property-read string                                                $name
 * @property-read array                                                 $defaultRelations
 * @property-read string                                                $modelFQN
 * @property-read string                                                $fQN
 * @property-read array|\cebe\yii2openapi\lib\items\AttributeRelation[] $relations
 * @property-read array                                                 $availableRelations
 */
class Transformer extends BaseObject
{
    /**@var \cebe\yii2openapi\lib\items\DbModel**/
    public $dbModel;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $modelNamespace;

    public function __construct(DbModel $dbModel, string $namespace, string $modelNamespace)
    {
        $this->dbModel = $dbModel;
        $this->namespace = $namespace;
        $this->modelNamespace = $modelNamespace;
        parent::__construct([]);
    }

    public function getName(): string
    {
        return $this->dbModel->getClassName().'Transformer';
    }

    public function getFQN(): string
    {
        return $this->namespace.'\\'.$this->getName();
    }

    public function getModelFQN(): string
    {
        return $this->modelNamespace.'\\'.$this->dbModel->getClassName();
    }

    public function shouldIncludeRelations(): bool
    {
        return !empty($this->availableRelations);
    }

    /**
     * @return array|\cebe\yii2openapi\lib\items\AttributeRelation[]
     */
    public function getRelations(): array
    {
        return $this->dbModel->relations;
    }

    public function getDefaultRelations(): array
    {
        return [];
    }

    public function getAvailableRelations(): array
    {
        return \array_keys($this->dbModel->relations);
    }
}
