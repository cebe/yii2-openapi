<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\generators;

use cebe\openapi\spec\Operation;
use cebe\yii2openapi\lib\items\FractalAction;
use cebe\yii2openapi\lib\items\RouteData;
use cebe\yii2openapi\lib\openapi\ResponseSchema;
use Yii;
use yii\base\BaseObject;
use yii\helpers\Inflector;

class JsonActionGenerator extends RestActionGenerator
{

    /**
     * @param string                                $method
     * @param \cebe\openapi\spec\Operation          $operation
     * @param \cebe\yii2openapi\lib\items\RouteData $routeData
     * @return \cebe\yii2openapi\lib\items\RestAction|object
     * @throws \yii\base\InvalidConfigException
     * @throws \cebe\openapi\exceptions\UnresolvableReferenceException
     */
    protected function prepareAction(string $method, Operation $operation, RouteData $routeData):BaseObject
    {
        $actionType = $this->resolveActionType($routeData, $method);
        $modelClass = ResponseSchema::guessModelClass($operation, $actionType);
        $expectedRelations = in_array($actionType, ['list', 'view'])
            ? ResponseSchema::guessResponseRelations($operation)
            : [];
        // fallback to known model class on same URL
        if ($modelClass === null && isset($this->knownModelClasses[$routeData->path])) {
            $modelClass = $this->knownModelClasses[$routeData->path];
        } else {
            $this->knownModelClasses[$routeData->path] = $modelClass;
        }
        if ($routeData->isRelationship()) {
            $relatedClass = $modelClass;
            $transformerClass = $modelClass !== null
                ? $this->config->transformerNamespace . '\\' . Inflector::id2camel($modelClass, '_') . 'Transformer'
                : null;
            $controllerId = $routeData->controller;
            $modelClass = Inflector::id2camel(Inflector::singularize($controllerId));
            if (isset($this->config->controllerModelMap[$modelClass])) {
                $controllerId = Inflector::camel2id($this->config->controllerModelMap[$modelClass]);
            }
        } else {
            $relatedClass = null;
            if ($modelClass === null || !$routeData->isModelBasedType()) {
                $controllerId = $routeData->controller;
            } elseif (isset($this->config->controllerModelMap[$modelClass])) {
                $controllerId = Inflector::camel2id($this->config->controllerModelMap[$modelClass]);
            } else {
                $controllerId = Inflector::camel2id($modelClass);
            }
            $transformerClass = $modelClass !== null
                ? $this->config->transformerNamespace . '\\' . Inflector::id2camel($modelClass, '_') . 'Transformer'
                : null;
        }

        if ($routeData->type === RouteData::TYPE_RESOURCE_OPERATION && !$modelClass) {
            $modelClass = Inflector::id2camel(Inflector::singularize($controllerId));
            if (isset($this->config->controllerModelMap[$modelClass])) {
                $controllerId = Inflector::camel2id($this->config->controllerModelMap[$modelClass]);
            }
        }

        return Yii::createObject(FractalAction::class, [
            [
                'singularResourceKey' => $this->config->singularResourceKeys,
                'type' => $routeData->type,
                'id' => $routeData->isNonCrudAction() ? trim("{$actionType}-{$routeData->action}", '-')
                    : "$actionType{$routeData->action}",
                'controllerId' => $controllerId,
                'urlPath' => $routeData->path,
                'requestMethod' => strtoupper($method),
                'urlPattern' => $routeData->pattern,
                'idParam' => $routeData->idParam ?? null,
                'parentIdParam' => $routeData->parentParam ?? null,
                'params' => $routeData->params,
                'modelName' => $modelClass,
                'relatedModel' => $relatedClass,
                'modelFqn' => $modelClass !== null
                    ? $this->config->modelNamespace . '\\' . Inflector::id2camel($modelClass, '_')
                    : null,
                'transformerFqn' => $transformerClass,
                'expectedRelations' => $expectedRelations,
                'prefix' => $routeData->getPrefix(),
                'prefixSettings' => $routeData->getPrefixSettings(),
            ],
        ]);
    }
}
