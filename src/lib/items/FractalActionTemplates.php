<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\items;

use insolita\fractal\actions\CreateAction;
use insolita\fractal\actions\CreateRelationshipAction;
use insolita\fractal\actions\DeleteAction;
use insolita\fractal\actions\DeleteRelationshipAction;
use insolita\fractal\actions\ListAction;
use insolita\fractal\actions\UpdateAction;
use insolita\fractal\actions\UpdateRelationshipAction;
use insolita\fractal\actions\ViewAction;
use insolita\fractal\actions\ViewRelationshipAction;
use Yii;
use function dirname;
use function file_exists;
use function method_exists;

class FractalActionTemplates
{

    /**
     * @var \cebe\yii2openapi\lib\items\FractalAction
     */
    private $action;

    public function __construct(FractalAction $action)
    {
        $this->action = $action;
    }

    public function hasTemplate(): bool
    {
        $method = $this->action->templateId.'Template';
        return method_exists($this, $method);
    }

    public function hasImplementation(): bool
    {
        $template = dirname(__DIR__)."/action_templates/{$this->action->templateId}.php";
        return file_exists($template);
    }

    public function getTemplate(): ?string
    {
        $method = $this->action->templateId.'Template';
        return method_exists($this, $method) ? $this->$method(): null;
    }

    public function getImplementation(): ?string
    {
        $template = dirname(__DIR__)."/action_templates/{$this->action->templateId}.php";
        return file_exists($template) ? Yii::$app->view->renderPhpFile($template, ['action'=>$this->action]): null;
    }

    protected function viewResourceTemplate():string
    {
        $className = '\\'.ViewAction::class.'::class';
        return <<<"PHP"
'{$this->action->id}' => [
                'class' => {$className},
                'checkAccess' => [\$this, 'checkAccess'],
                'transformer' => \\{$this->action->transformerFqn}::class,
                'modelClass' => \\{$this->action->modelFqn}::class,
                'resourceKey' => '{$this->action->getResourceKey()}',
                'findModel' => null
            ]
PHP;
    }
    protected function createCollectionTemplate():string
    {
        $className = '\\'.CreateAction::class.'::class';
        return <<<"PHP"
'{$this->action->id}' => [
                'class' => {$className},
                'checkAccess' => [\$this, 'checkAccess'],
                'transformer' => \\{$this->action->transformerFqn}::class,
                'modelClass' => \\{$this->action->modelFqn}::class,
                'resourceKey' => '{$this->action->getResourceKey()}',
                'allowedRelations'=>[],
                'viewRoute' => 'view',
                'scenario' => 'default'
            ]
PHP;
    }
    protected function updateResourceTemplate():string
    {
        $resourceKey = $this->action->getResourceKey();
        $className = '\\'.UpdateAction::class.'::class';
        return <<<"PHP"
'{$this->action->id}' => [
                'class' => {$className},
                'checkAccess' => [\$this, 'checkAccess'],
                'transformer' => \\{$this->action->transformerFqn}::class,
                'modelClass' => \\{$this->action->modelFqn}::class,
                'resourceKey' => '{$resourceKey}',
                'findModel' => null,
                'allowedRelations'=>[],
                'scenario' => 'default'
            ]
PHP;
    }
    protected function deleteResourceTemplate():string
    {
        $className = '\\'.DeleteAction::class.'::class';
        return <<<"PHP"
'{$this->action->id}' => [
                'class' => {$className},
                'checkAccess' => [\$this, 'checkAccess'],
                'modelClass' => \\{$this->action->modelFqn}::class,
                'findModel' => null
          ]
PHP;
    }
    protected function viewResourceForTemplate():string
    {
        $resourceKey = $this->action->getResourceKey();
        $className =  '\\'.ViewAction::class.'::class';
        return <<<"PHP"
'{$this->action->id}' => [
                'class' => {$className},
                'checkAccess' => [\$this, 'checkAccess'],
                'transformer' => \\{$this->action->transformerFqn}::class,
                'modelClass' => \\{$this->action->modelFqn}::class,
                'resourceKey' => '{$resourceKey}',
                'parentIdParam' => '{$this->action->parentIdParam}',
                'parentIdAttribute' => '{$this->action->parentIdAttribute}',
                'findModelFor' => null
            ]
PHP;
    }
    protected function createCollectionForTemplate():string
    {
        $resourceKey = $this->action->getResourceKey();
        $className =  '\\'.CreateAction::class.'::class';
        return <<<"PHP"
'{$this->action->id}' => [
                'class' => {$className},
                'checkAccess' => [\$this, 'checkAccess'],
                'transformer' => \\{$this->action->transformerFqn}::class,
                'modelClass' => \\{$this->action->modelFqn}::class,
                'resourceKey' => '{$resourceKey}',
                'parentIdParam' => '{$this->action->parentIdParam}',
                'parentIdAttribute' => '{$this->action->parentIdAttribute}',
                'findModelFor' => null,
                'scenario' => 'default',
                'viewRoute' => 'view'
            ]
PHP;
    }
    protected function updateResourceForTemplate():string
    {
        $resourceKey = $this->action->getResourceKey();
        $className =  '\\'.UpdateAction::class.'::class';
        return <<<"PHP"
'{$this->action->id}' => [
                'class' => {$className},
                'checkAccess' => [\$this, 'checkAccess'],
                'transformer' => {$this->action->transformerFqn}::class,
                'modelClass' => {$this->action->modelFqn}::class,
                'resourceKey' => '{$resourceKey}',
                'parentIdParam' => '{$this->action->parentIdParam}',
                'parentIdAttribute' => '{$this->action->parentIdAttribute}',
                'findModelFor' => null,
                'scenario' => 'default'
            ]
PHP;
    }
    protected function deleteResourceForTemplate():string
    {
        $className = '\\'.DeleteAction::class.'::class';
        return <<<"PHP"
'{$this->action->id}' => [
                'class' => {$className},
                'checkAccess' => [\$this, 'checkAccess'],
                'modelClass' => {$this->action->modelFqn}::class,
                'parentIdParam' => '{$this->action->parentIdParam}',
                'parentIdAttribute' => '{$this->action->parentIdAttribute}',
                'findModelFor' => null
            ]
PHP;
    }

    protected function listCollectionTemplate():string
    {
        $className = '\\'.ListAction::class.'::class';
        $resourceKey = $this->action->getResourceKey();
        return <<<"PHP"
'{$this->action->id}' => [
                'class' => {$className},
                'checkAccess' => [\$this, 'checkAccess'],
                'transformer' => \\{$this->action->transformerFqn}::class,
                'modelClass' => \\{$this->action->modelFqn}::class,
                'resourceKey' => '{$resourceKey}',
                'dataFilter' => null,
                'prepareDataProvider' => null
            ]
PHP;
    }
    protected function listCollectionForTemplate():string
    {
        $className = '\\'.ListAction::class.'::class';
        $resourceKey = $this->action->getResourceKey();
        return <<<"PHP"
'{$this->action->id}' => [
                'class' => {$className},
                'checkAccess' => [\$this, 'checkAccess'],
                'transformer' => \\{$this->action->transformerFqn}::class,
                'modelClass' => \\{$this->action->modelFqn}::class,
                'resourceKey' => '{$resourceKey}',
                'parentIdParam' => '{$this->action->parentIdParam}',
                'parentIdAttribute' => '{$this->action->parentIdAttribute}',
                'dataFilter' => null,
                'prepareDataProvider' => null
            ]
PHP;
    }

    protected function listRelationshipTemplate(): string
    {
        $className = '\\'.ViewRelationshipAction::class.'::class';
        $resourceKey = $this->action->getResourceKey();
        return <<<"PHP"
'{$this->action->id}' => [
                'class' => {$className},
                'checkAccess' => [\$this, 'checkAccess'],
                'transformer' => \\{$this->action->transformerFqn}::class,
                'modelClass' => \\{$this->action->modelFqn}::class,
                'resourceKey' => '{$resourceKey}',
                'relationName' => '{$this->action->getRelationName()}'
            ]
PHP;
    }

    //Same as listRelationship template, but for hasOne relation
    protected function viewRelationshipTemplate(): string
    {
        $className = '\\'.ViewRelationshipAction::class.'::class';
        $resourceKey = $this->action->getResourceKey();
        return <<<"PHP"
'{$this->action->id}' => [
                'class' => {$className},
                'checkAccess' => [\$this, 'checkAccess'],
                'transformer' => \\{$this->action->transformerFqn}::class,
                'modelClass' => \\{$this->action->modelFqn}::class,
                'resourceKey' => '{$resourceKey}',
                'relationName' => '{$this->action->getRelationName()}'
            ]
PHP;
    }

    protected function createRelationshipTemplate(): string
    {
        $className = '\\'.CreateRelationshipAction::class.'::class';
        $resourceKey = $this->action->getResourceKey();
        return <<<"PHP"
'{$this->action->id}' => [
                'class' => {$className},
                'checkAccess' => [\$this, 'checkAccess'],
                'transformer' => \\{$this->action->transformerFqn}::class,
                'modelClass' => \\{$this->action->modelFqn}::class,
                'resourceKey' => '{$resourceKey}',
                'pkType' => 'integer',
                'relationName' => '{$this->action->getRelationName()}'
            ]
PHP;
    }

    protected function updateRelationshipTemplate(): string
    {
        $className = '\\'.UpdateRelationshipAction::class.'::class';
        return <<<"PHP"
'{$this->action->id}' => [
                'class' => {$className},
                'checkAccess' => [\$this, 'checkAccess'],
                'modelClass' => \\{$this->action->modelFqn}::class,
                'pkType' => 'integer',
                'unlinkOnly' => true,
                'relationName' => '{$this->action->getRelationName()}'
            ]
PHP;
    }

    protected function deleteRelationshipTemplate(): string
    {
        $className = '\\'.DeleteRelationshipAction::class.'::class';
        return <<<"PHP"
'{$this->action->id}' => [
                'class' => {$className},
                'checkAccess' => [\$this, 'checkAccess'],
                'modelClass' => \\{$this->action->modelFqn}::class,
                'pkType' => 'integer',
                'unlinkOnly' => true,
                'relationName' => '{$this->action->getRelationName()}'
            ]
PHP;
    }
}
