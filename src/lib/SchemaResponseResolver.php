<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib;

use cebe\openapi\spec\MediaType;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Schema;
use cebe\openapi\SpecObjectInterface;
use function array_keys;
use function explode;

class SchemaResponseResolver
{
    private const REQUEST_BODY_ACTIONS = ['create', 'update', 'delete'];
    private const RESPONSE_BODY_ACTIONS = [
        'create',
        'update',
        'delete',
        'view',
        'list',
    ];

    protected static function isObjectSchema($schema): bool
    {
        return !isset($schema->type) || $schema->type === null || $schema->type === 'object';
    }

    protected static function isArraySchemaWithRefItems($schema): bool
    {
        return $schema->type === 'array' && isset($schema->items) && $schema->items instanceof Reference;
    }

    protected static function hasAttributesReference($schema):bool
    {
        return isset($schema->properties['attributes']) && $schema->properties['attributes'] instanceof Reference;
    }

    protected static function schemaNameByRef($schemaOrReference): ?string
    {
//        if($schemaOrReference instanceof Reference){
//            $schemaOrReference->resolve();
//        }
        $ref = $schemaOrReference->getJsonReference()->getJsonPointer()->getPointer();
        return strpos($ref, '/components/schemas/') === 0 ? substr($ref, 20) : null;
    }

    public static function guessResponseRelations(Operation $operation):array
    {
        if (!isset($operation->responses)) {
            return [];
        }
        foreach ($operation->responses as $code => $successResponse) {
            if (((string)$code)[0] !== '2') {
                continue;
            }
            if ($successResponse instanceof Reference) {
                $successResponse = $successResponse->resolve();
            }
            foreach ($successResponse->content as $contentType => $content) {
                $responseSchema =
                    $content->schema instanceof Reference ? $content->schema->resolve() : $content->schema;
                if (self::isObjectSchema($responseSchema) && isset($responseSchema->properties['data'])) {
                    $dataSchema = $responseSchema->properties['data'];
                    if ($dataSchema instanceof Reference) {
                        $dataSchema = $dataSchema->resolve();
                    }
                    if (self::isArraySchemaWithRefItems($dataSchema)) {
                        $ref = $dataSchema->items->resolve();
                        if (!isset($ref->properties['relationships'])) {
                            continue;
                        }
                        $relationSchema = $ref->properties['relationships'];
                        if ($relationSchema instanceof Reference) {
                            $relationSchema->resolve();
                        }
                        return isset($relationSchema->properties) ? array_keys($relationSchema->properties) : [];
                    }
                    if (self::isObjectSchema($dataSchema)) {
                        $ref = $dataSchema;
                        if (!isset($ref->properties['relationships'])) {
                            continue;
                        }
                        $relationSchema = $ref->properties['relationships'];
                        if ($relationSchema instanceof Reference) {
                            $relationSchema->resolve();
                        }
                        return isset($relationSchema->properties) ? array_keys($relationSchema->properties) : [];
                    }
                    return [];
                }
            }
        }
        return [];
    }

    public static function guessModelClass(Operation $operation, $actionType):?string
    {
        // first, check request body
        $requestBody = $operation->requestBody;
        if ($requestBody !== null && in_array($actionType, static::REQUEST_BODY_ACTIONS, true)) {
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
        if (
            !isset($operation->responses)
            || !in_array($actionType, self::RESPONSE_BODY_ACTIONS, true)
            || !in_array(explode('-for-', $actionType)[0], self::RESPONSE_BODY_ACTIONS, true)
        ) {
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

    public static function guessModelClassFromJsonResource(SpecObjectInterface $property): array
    {
        $schema = $property instanceof Reference? $property->resolve() : $property;

        if (self::isObjectSchema($schema) && self::hasAttributesReference($schema)) {
            $name = self::schemaNameByRef($schema->properties['attributes']);
            if ($name !== null) {
                return [$name, '', '', 'object'];
            }
            return [null, null, null, null];
        }
        if (self::isArraySchemaWithRefItems($property)) {
            $ref = $property->items->resolve();
            if (!self::hasAttributesReference($ref)) {
                return [null, null, null, null];
            }
            $name = self::schemaNameByRef($ref->properties['attributes']);
            if ($name !== null) {
                return [$name, '', '', 'array'];
            }
        }
        return [null, null, null, null];
    }

    public static function guessModelClassFromContent(MediaType $content):array
    {
        /** @var $referencedSchema Schema */
        if ($content->schema instanceof Reference) {
            $referencedSchema = $content->schema->resolve();
            if (self::isObjectSchema($referencedSchema)) {
                $schemaName = self::schemaNameByRef($content->schema);
                if ($schemaName !== null) {
                    return [$schemaName, '', '', 'object'];
                }
            }
            if (self::isArraySchemaWithRefItems($referencedSchema)) {
                $schemaName = self::schemaNameByRef($referencedSchema->items);
                if ($schemaName !== null) {
                    return [$schemaName, '', '', 'array'];
                }
            }
            return [null, null, null, null];
        }
        $referencedSchema = $content->schema;
        if ($referencedSchema === null) {
            return [null, null, null, null];
        }
        if (self::isArraySchemaWithRefItems($referencedSchema)) {
            $schemaName = self::schemaNameByRef($referencedSchema->items);
            if ($schemaName !== null) {
                return [$schemaName, '', '', 'array'];
            }
        }
        if (self::isObjectSchema($referencedSchema)) {
            foreach ($referencedSchema->properties as $propertyName => $property) {
                if ($propertyName === 'data') {
                    //JsonApi resource
                    return self::guessModelClassFromJsonResource($property);
                }

                if ($property instanceof Reference) {
                    $property->resolve();
                }
                // Model data is wrapped
                if (self::isObjectSchema($property)) {
                    $schemaName = self::schemaNameByRef($property);
                    if ($schemaName !== null) {
                        return [$schemaName, $propertyName, null, 'object'];
                    }
                }
                // an array of Model data is wrapped
                if (self::isArraySchemaWithRefItems($property)) {
                    $schemaName = self::schemaNameByRef($property->items);
                    if ($schemaName !== null) {
                        return [$schemaName, null, $propertyName, 'array'];
                    }
                }
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
    public static function findResponseWrapper(Operation $operation, $modelClass):?array
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

    public static function guessModelByRef($reference):?string
    {
        if (!$reference instanceof Reference) {
            return null;
        }
        $ref = $reference->resolve();
        if ($ref->type !== null && $ref->type !== 'object') {
            return null;
        }
        $pointer = $reference->getJsonReference()->getJsonPointer()->getPointer();
        if (!strpos($pointer, '/components/schemas/') === 0) {
            return null;
        }
        return substr($pointer, 20);
    }
}
