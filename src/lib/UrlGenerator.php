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
use cebe\yii2openapi\lib\items\UrlRule;
use Exception;
use yii\base\InvalidConfigException;
use yii\helpers\Inflector;

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
     * @return \cebe\yii2openapi\lib\items\UrlRule[]|array
     * @throws \yii\base\InvalidConfigException
     */
    public function generate():array
    {
        $this->urlRules = [];
        foreach ($this->openApi->paths as $path => $pathItem) {
            if ($path[0] !== '/') {
                throw new InvalidConfigException('Path must begin with /');
            }
            if ($pathItem === null) {
                continue;
            }
            $this->resolvePath($path, $pathItem);
        }
        return $this->urlRules;
    }

    protected function resolvePath(string $path, PathItem $pathItem):void
    {
        if ($pathItem instanceof Reference) {
            $pathItem = $pathItem->resolve();
        }

        $parts = explode('/', trim($path, '/'));
        $controller = [];
        $action = [];
        $params = false;
        $actionParams = [];
        $idParam = null;
        foreach ($parts as $p => $part) {
            if (preg_match('/\{(.*)\}/', $part, $m)) {
                $params = true;
                $parts[$p] = '<' . $m[1] . '>';
                if (isset($pathItem->parameters[$m[1]])) {
                    $actionParams[$m[1]] = $pathItem->parameters[$m[1]];
                } else {
                    $actionParams[$m[1]] = null;
                }
                if ($idParam === null && preg_match('/\bid\b/i', Inflector::camel2id($m[1]))) {
                    $idParam = $m[1];
                }
                // TODO add regex to param based on openAPI type
            } elseif ($params) {
                $action[] = $part;
            } else {
                $controller[] = Inflector::camel2id(Inflector::singularize($part));
            }
        }
        $pattern = implode('/', $parts);
        $controller = implode('-', $controller);
        if (empty($controller)) {
            $controller = 'default';
        }
        $action = empty($action) ? '' : '-' . implode('-', $action);
        foreach ($pathItem->getOperations() as $method => $operation) {
            switch ($method) {
                case 'get':
                    $a = $params ? 'view' : 'index';
                    break;
                case 'post':
                    $a = 'create';
                    break;
                case 'patch':
                case 'put':
                    $a = 'update';
                    break;
                case 'delete':
                    $a = 'delete';
                    break;
                default:
                    $a = "http-$method";
                    break;
            }
            $modelClass = $this->guessModelClass($operation, $a);
            $responseWrapper = $this->findResponseWrapper($operation, $a, $modelClass);
            // fallback to known model class on same URL
            if ($modelClass === null && isset($this->knownModelClasses[$path])) {
                $modelClass = $this->knownModelClasses[$path];
            } else {
                $this->knownModelClasses[$path] = $modelClass;
            }
            $this->urlRules[] = new UrlRule([
                'path' => $path,
                'method' => strtoupper($method),
                'pattern' => $pattern,
                'route' => "$controller/$a$action",
                'actionParams' => $actionParams,
                'idParam' => $idParam,
                'openApiOperation' => $operation,
                'modelClass' => $modelClass !== null ? $this->modelNamespace . '\\' . $modelClass : null,
                'responseWrapper' => $responseWrapper,
            ]);
        }
    }

    private function guessModelClass(Operation $operation, $actionName)
    {
        switch ($actionName) {
            case 'create':
            case 'update':
            case 'delete':

                // first, check request body

                $requestBody = $operation->requestBody;
                if ($requestBody !== null) {
                    if ($requestBody instanceof Reference) {
                        $requestBody = $requestBody->resolve();
                    }
                    foreach ($requestBody->content as $contentType => $content) {
                        [$modelClass,] = $this->guessModelClassFromContent($content);
                        if ($modelClass !== null) {
                            return $modelClass;
                        }
                    }
                }

            // no break, check response body if guess did not find model in request body
            case 'view':
            case 'index':

                // then, check response body

                if (!isset($operation->responses)) {
                    break;
                }
                foreach ($operation->responses as $code => $successResponse) {
                    if (((string)$code)[0] !== '2') {
                        continue;
                    }
                    if ($successResponse instanceof Reference) {
                        $successResponse = $successResponse->resolve();
                    }
                    foreach ($successResponse->content as $contentType => $content) {
                        [$modelClass,] = $this->guessModelClassFromContent($content);
                        if ($modelClass !== null) {
                            return $modelClass;
                        }
                    }
                }

                break;
        }
    }

    private function guessModelClassFromContent(MediaType $content):array
    {
        /** @var $referencedSchema Schema */
        if ($content->schema instanceof Reference) {
            $referencedSchema = $content->schema->resolve();
            // Model data is directly returned
            if ($referencedSchema->type === null || $referencedSchema->type === 'object') {
                $ref = $content->schema->getJsonReference()->getJsonPointer()->getPointer();
                if (strpos($ref, '/components/schemas/') === 0) {
                    return [substr($ref, 20), '', ''];
                }
            }
            // an array of Model data is directly returned
            if ($referencedSchema->type === 'array' && $referencedSchema->items instanceof Reference) {
                $ref = $referencedSchema->items->getJsonReference()->getJsonPointer()->getPointer();
                if (strpos($ref, '/components/schemas/') === 0) {
                    return [substr($ref, 20), '', ''];
                }
            }
        } else {
            $referencedSchema = $content->schema;
        }
        if ($referencedSchema === null) {
            return [null, null, null];
        }
        if ($referencedSchema->type === null || $referencedSchema->type === 'object') {
            foreach ($referencedSchema->properties as $propertyName => $property) {
                if ($property instanceof Reference) {
                    $referencedModelSchema = $property->resolve();
                    if ($referencedModelSchema->type === null || $referencedModelSchema->type === 'object') {
                        // Model data is wrapped
                        $ref = $property->getJsonReference()->getJsonPointer()->getPointer();
                        if (strpos($ref, '/components/schemas/') === 0) {
                            return [substr($ref, 20), $propertyName, null];
                        }
                    } elseif ($referencedModelSchema->type === 'array'
                        && $referencedModelSchema->items instanceof Reference) {
                        // an array of Model data is wrapped
                        $ref = $referencedModelSchema->items->getJsonReference()->getJsonPointer()->getPointer();
                        if (strpos($ref, '/components/schemas/') === 0) {
                            return [substr($ref, 20), null, $propertyName];
                        }
                    }
                } elseif ($property->type === 'array' && $property->items instanceof Reference) {
                    // an array of Model data is wrapped
                    $ref = $property->items->getJsonReference()->getJsonPointer()->getPointer();
                    if (strpos($ref, '/components/schemas/') === 0) {
                        return [substr($ref, 20), null, $propertyName];
                    }
                }
            }
        }
        if ($referencedSchema->type === 'array' && $referencedSchema->items instanceof Reference) {
            $ref = $referencedSchema->items->getJsonReference()->getJsonPointer()->getPointer();
            if (strpos($ref, '/components/schemas/') === 0) {
                return [substr($ref, 20), '', ''];
            }
        }
        return [null, null, null];
    }

    /**
     * Figure out whether response item is wrapped in response.
     * @param Operation $operation
     * @param           $actionName
     * @param           $modelClass
     * @return null|array
     */
    private function findResponseWrapper(Operation $operation, $actionName, $modelClass):?array
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
                [$detectedModelClass, $itemWrapper, $itemsWrapper] = $this->guessModelClassFromContent($content);
                if (($itemWrapper !== null || $itemsWrapper !== null) && $detectedModelClass === $modelClass) {
                    return [$itemWrapper, $itemsWrapper];
                }
            }
        }
        return null;
    }
}
