<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib;

use cebe\openapi\spec\MediaType;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\PathItem;
use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Schema;
use cebe\yii2openapi\lib\items\RouteData;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use function in_array;

class UrlGenerator
{
    /**
     * @var \cebe\openapi\spec\OpenApi
     */
    private $openApi;

    private $knownModelClasses = [];

    private $urlRules = [];

    /**
     * @var string
     */
    private $modelNamespace;

    public function __construct(OpenApi $openApi, string $modelNamespace)
    {
        $this->openApi = $openApi;
        $this->modelNamespace = $modelNamespace;
    }

    /**
     * @return \cebe\yii2openapi\lib\items\RouteData[]|array
     * @throws \yii\base\InvalidConfigException
     */
    public function generate():array
    {
        if (!$this->urlRules) {
            foreach ($this->openApi->paths as $path => $pathItem) {
                if ($path[0] !== '/') {
                    throw new InvalidConfigException('Path must begin with /');
                }
                if ($pathItem === null) {
                    continue;
                }
                $urls = $this->resolvePath($path, $pathItem);
                $this->urlRules = array_merge($this->urlRules, $urls);
            }
        }
        return $this->urlRules;
    }

    /**
     * @param string                      $path
     * @param \cebe\openapi\spec\PathItem $pathItem
     * @return array|RouteData[]
     * @throws \cebe\openapi\exceptions\UnresolvableReferenceException
     */
    public function resolvePath(string $path, PathItem $pathItem):array
    {
        $urls = [];
        if ($pathItem instanceof Reference) {
            $pathItem = $pathItem->resolve();
        }
        $parts = explode('/', trim($path, '/'));
        [$pattern, $action, $controller, $hasParams, $lastPartIsParam, $idParam, $actionParams] =
            $this->resolvePathParts($pathItem, $parts);

        foreach ($pathItem->getOperations() as $method => $operation) {
            switch ($method) {
                case 'get':
                    $actionName = ($hasParams ? ( $lastPartIsParam? 'view': 'list') : 'list');
                    break;
                case 'post':
                    $actionName = 'create';
                    break;
                case 'patch':
                case 'put':
                    $actionName = 'update';
                    break;
                case 'delete':
                    $actionName = 'delete';
                    break;
                default:
                    $actionName = "http-$method";
                    break;
            }
            $modelClass = self::guessModelClass($operation, $actionName);
            $responseWrapper = self::findResponseWrapper($operation, $modelClass);
            // fallback to known model class on same URL
            if ($modelClass === null && isset($this->knownModelClasses[$path])) {
                $modelClass = $this->knownModelClasses[$path];
            } else {
                $this->knownModelClasses[$path] = $modelClass;
            }
            $controllerId = $modelClass!==null? Inflector::camel2id($modelClass, '-'): $controller;
            $urls[] = new RouteData([
                'path' => $path,
                'method' => strtoupper($method),
                'pattern' => $pattern,
                'controllerId' => $controllerId,
                'actionId'=>"$actionName$action",
                'actionParams' => $actionParams,
                'idParam' => $idParam,
                'modelClass' => $modelClass !== null ? $this->modelNamespace . '\\' . $modelClass : null,
                'responseWrapper' => $responseWrapper,
            ]);
        }
        return $urls;
    }

    public static function guessModelClass(Operation $operation, $actionName):?string
    {
        // first, check request body
        $requestBody = $operation->requestBody;
        if ($requestBody !== null && in_array($actionName, ['create', 'update', 'delete'])) {
            if ($requestBody instanceof Reference) {
                $requestBody = $requestBody->resolve();
            }
            foreach ($requestBody->content as $contentType => $content) {
                [$modelClass,] = self::guessModelClassFromContent($content);
                if ($modelClass !== null) {
                    return $modelClass;
                }
            }
        }
        // then, check response body
        if (!isset($operation->responses) || !in_array($actionName, ['create', 'update', 'delete', 'view', 'list'])) {
            return null;
        }

        foreach ($operation->responses as $code => $successResponse) {
            if (((string)$code)[0] !== '2') {
                continue;
            }
            if ($successResponse instanceof Reference) {
                $successResponse = $successResponse->resolve();
            }
            foreach ($successResponse->content as $contentType => $content) {
                [$modelClass,] = self::guessModelClassFromContent($content);
                if ($modelClass !== null) {
                    return $modelClass;
                }
            }
        }

        return null;
    }

    private static function guessModelClassFromContent(MediaType $content):array
    {
        /** @var $referencedSchema Schema */
        if ($content->schema instanceof Reference) {
            $referencedSchema = $content->schema->resolve();
            // Model data is directly returned
            if ($referencedSchema->type === null || $referencedSchema->type === 'object') {
                $ref = $content->schema->getJsonReference()->getJsonPointer()->getPointer();
                if (strpos($ref, '/components/schemas/') === 0) {
                    return [substr($ref, 20), '', '', 'object'];
                }
            }
            // an array of Model data is directly returned
            if ($referencedSchema->type === 'array' && $referencedSchema->items instanceof Reference) {
                $ref = $referencedSchema->items->getJsonReference()->getJsonPointer()->getPointer();
                if (strpos($ref, '/components/schemas/') === 0) {
                    return [substr($ref, 20), '', '', 'array'];
                }
            }
        } else {
            $referencedSchema = $content->schema;
        }
        if ($referencedSchema === null) {
            return [null, null, null, null];
        }
        if ($referencedSchema->type === null || $referencedSchema->type === 'object') {
            foreach ($referencedSchema->properties as $propertyName => $property) {
                if ($property instanceof Reference) {
                    $referencedModelSchema = $property->resolve();
                    if ($referencedModelSchema->type === null || $referencedModelSchema->type === 'object') {
                        // Model data is wrapped
                        $ref = $property->getJsonReference()->getJsonPointer()->getPointer();
                        if (strpos($ref, '/components/schemas/') === 0) {
                            return [substr($ref, 20), $propertyName, null, 'object'];
                        }
                    } elseif ($referencedModelSchema->type === 'array'
                        && $referencedModelSchema->items instanceof Reference) {
                        // an array of Model data is wrapped
                        $ref = $referencedModelSchema->items->getJsonReference()->getJsonPointer()->getPointer();
                        if (strpos($ref, '/components/schemas/') === 0) {
                            return [substr($ref, 20), null, $propertyName, 'array'];
                        }
                    }
                } elseif ($property->type === 'array' && $property->items instanceof Reference) {
                    // an array of Model data is wrapped
                    $ref = $property->items->getJsonReference()->getJsonPointer()->getPointer();
                    if (strpos($ref, '/components/schemas/') === 0) {
                        return [substr($ref, 20), null, $propertyName, 'array'];
                    }
                }
            }
        }
        if ($referencedSchema->type === 'array' && $referencedSchema->items instanceof Reference) {
            $ref = $referencedSchema->items->getJsonReference()->getJsonPointer()->getPointer();
            if (strpos($ref, '/components/schemas/') === 0) {
                return [substr($ref, 20), '', '', 'array'];
            }
        }
        return [null, null, null, null];
    }

    /**
     * Figure out whether response item is wrapped in response.
     * @param Operation $operation
     * @param           $modelClass
     * @return null|array
     */
    private static function findResponseWrapper(Operation $operation, $modelClass):?array
    {
        if (!isset($operation->responses)) {
            return null;
        }
        foreach ($operation->responses as $code => $successResponse) {
            if (((string)$code)[0] !== '2') {
                continue;
            }
            if ($successResponse instanceof Reference) {
                $successResponse = $successResponse->resolve();
            }
            foreach ($successResponse->content as $contentType => $content) {
                [$detectedModelClass, $itemWrapper, $itemsWrapper, $type] = self::guessModelClassFromContent($content);
                if (($itemWrapper !== null || $itemsWrapper !== null) && $detectedModelClass === $modelClass) {
                    return ['item' => $itemWrapper, 'list' => $itemsWrapper, 'type' => $type];
                }
            }
        }
        return null;
    }

    private function resolvePathParts(PathItem $pathItem, array $pathParts):array
    {
        $actionParams = [];
        $patternParts = $pathParts;
        $action = '';
        $controllerParts = [];
        $hasParams = false;
        $idParam = null;
        $pathParameters = ArrayHelper::index($pathItem->parameters, 'name');
        foreach ($pathParts as $p => $part) {
            if (preg_match('/\{(.*)\}/', $part, $m)) {
                $hasParams = true;
                $paramName = $m[1];
                if (\array_key_exists($paramName, $pathParameters)) {
                    $actionParams[$paramName] = [
                       // 'description'=>$pathParameters[$paramName]->description,
                       // 'required'=> $pathParameters[$paramName]->required,
                        'type'=> $pathParameters[$paramName]->schema->type ?? null,
                    ];
                } else {
                    $actionParams[$paramName] = null;
                }

                if ($idParam === null && preg_match('/\bid\b/i', Inflector::camel2id($paramName))) {
                    $idParam = $paramName;
                }
                $type = $actionParams[$paramName]['type'] ?? null;
                //check minimum/maximum for routes like <year:\d{4}> ?
                if ($type === 'integer') {
                    $patternParts[$p] = '<' . $paramName . ':\d+>';
                } elseif ($type === 'string') {
                    $patternParts[$p] = '<' . $paramName . ':[\w-]+>';
                } else {
                    $patternParts[$p] = '<' . $paramName . '>';
                }
            } elseif(!$hasParams) {
                $controllerParts[] = Inflector::camel2id(Inflector::singularize($part));
                $action = Inflector::camel2id(Inflector::singularize($part));
            }
        }
        $lastPartIsParam = preg_match('/\{(.*)\}/', $pathParts[count($pathParts)-1]);

        $pattern = implode('/', $patternParts);
        $action = empty($action)||\count($pathParts)< 3 ? '' : '-for-' . $action;
        $controller = empty($controllerParts)? 'default': \implode('-', $controllerParts);
        return [$pattern, $action, $controller, $hasParams, $lastPartIsParam, $idParam, $actionParams];
    }
}
