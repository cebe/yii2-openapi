<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib;

use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\PathItem;
use cebe\openapi\spec\Reference;
use cebe\yii2openapi\lib\items\RestAction;
use cebe\yii2openapi\lib\items\RouteData;
use yii\base\InvalidConfigException;
use yii\helpers\Inflector;
use function array_merge;

class UrlGenerator
{
    /**
     * @var \cebe\openapi\spec\OpenApi
     */
    protected $openApi;

    protected $knownModelClasses = [];

    /**
     * @var string
     */
    protected $modelNamespace;

    /**
     * @var array
     */
    protected $controllerMap = [];

    public function __construct(OpenApi $openApi, string $modelNamespace, array $controllerMap = [])
    {
        $this->openApi = $openApi;
        $this->modelNamespace = $modelNamespace;
        $this->controllerMap = $controllerMap;
    }

    /**
     * @return \cebe\yii2openapi\lib\items\RestAction[]|\cebe\yii2openapi\lib\items\FractalAction[]|array
     * @throws \yii\base\InvalidConfigException|\cebe\openapi\exceptions\UnresolvableReferenceException
     */
    public function generate():array
    {
        $urls = [];
        foreach ($this->openApi->paths as $path => $pathItem) {
            if ($path[0] !== '/') {
                throw new InvalidConfigException('Path must begin with /');
            }
            if ($pathItem === null) {
                continue;
            }
            $urls[] = $this->resolvePath($path, $pathItem);
        }
        return array_merge(...$urls);
    }

    protected function resolveActionType(RouteData $routeData, string $method):string
    {
        $actionTypes = [
            'get' => $routeData->resolveGetActionType(),
            'post' => 'create',
            'patch' => 'update',
            'put' => 'update',
            'delete' => 'delete',
        ];
        return $actionTypes[$method] ?? "http-$method";
    }

    /**
     * @param string                      $path
     * @param \cebe\openapi\spec\PathItem $pathItem
     * @return array|RestAction[]|\cebe\yii2openapi\lib\items\FractalAction[]
     * @throws \cebe\openapi\exceptions\UnresolvableReferenceException
     */
    protected function resolvePath(string $path, PathItem $pathItem):array
    {
        $urls = [];
        if ($pathItem instanceof Reference) {
            $pathItem = $pathItem->resolve();
        }
        $routeData = new RouteData($pathItem, $path);
        foreach ($pathItem->getOperations() as $method => $operation) {
            $urls[] = $this->prepareAction($method, $operation, $routeData);
        }
        return $urls;
    }

    /**
     * @param string                                $method
     * @param \cebe\openapi\spec\Operation          $operation
     * @param \cebe\yii2openapi\lib\items\RouteData $routeData
     * @return \cebe\yii2openapi\lib\items\RestAction
     */
    protected function prepareAction(string $method, Operation $operation, RouteData $routeData)
    {
        $actionType = $this->resolveActionType($routeData, $method);
        $modelClass = SchemaResponseResolver::guessModelClass($operation, $actionType);
        $responseWrapper = SchemaResponseResolver::findResponseWrapper($operation, $modelClass);
        // fallback to known model class on same URL
        if ($modelClass === null && isset($this->knownModelClasses[$routeData->path])) {
            $modelClass = $this->knownModelClasses[$routeData->path];
        } else {
            $this->knownModelClasses[$routeData->path] = $modelClass;
        }
        if ($routeData->isRelationship()) {
            $controllerId = $routeData->controller;
            $modelClass = Inflector::id2camel(Inflector::singularize($controllerId));
            $controllerId =  isset($this->controllerMap[$modelClass])
                ? Inflector::camel2id($this->controllerMap[$modelClass])
                : $controllerId;
        } elseif ($modelClass !== null) {
            $controllerId =  isset($this->controllerMap[$modelClass])
                ? Inflector::camel2id($this->controllerMap[$modelClass])
                : Inflector::camel2id($modelClass);
        } else {
            $controllerId = $routeData->controller;
        }

        return new RestAction([
            'id' => trim("$actionType{$routeData->action}", '-'),
            'controllerId' => $controllerId,
            'urlPath' => $routeData->path,
            'requestMethod' => strtoupper($method),
            'urlPattern' => $routeData->pattern,
            'idParam' => $routeData->idParam ?? $routeData->parentParam ?? null,
            'params' => $routeData->params,
            'modelName' => $modelClass,
            'modelFqn' => $modelClass !== null
                ? $this->modelNamespace . '\\' . Inflector::id2camel($modelClass, '_')
                : null,
            'responseWrapper' => $responseWrapper,
        ]);
    }
}
