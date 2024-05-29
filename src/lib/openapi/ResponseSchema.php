<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\openapi;

use cebe\openapi\exceptions\UnresolvableReferenceException;
use cebe\openapi\spec\MediaType;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Schema;
use cebe\openapi\SpecObjectInterface;
use cebe\yii2openapi\lib\items\JunctionSchemas;
use function array_keys;
use function explode;
use function str_replace;

class ResponseSchema
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
        return isset($schema->items) && $schema->items instanceof Reference &&
            (isset($schema->type) && $schema->type === 'array');
    }

    protected static function hasAttributesReference($schema): bool
    {
        return isset($schema->properties['attributes']) && $schema->properties['attributes'] instanceof Reference;
    }

    protected static function schemaNameByRef($schemaOrReference): ?string
    {
//        if($schemaOrReference instanceof Reference){
//            $schemaOrReference->resolve();
//        }
        if (!$schemaOrReference instanceof Reference) { # https://github.com/cebe/yii2-openapi/issues/175
            return null;
        }
        $ref = $schemaOrReference->getJsonReference()->getJsonPointer()->getPointer();
        $name = strpos($ref, '/components/schemas/') === 0 ? substr($ref, 20) : null;
        return str_replace(JunctionSchemas::PREFIX, '', $name);
    }

    /**
     * @param Operation $operation
     * @return array
     * @throws UnresolvableReferenceException
     */
    public static function guessResponseRelations(Operation $operation): array
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
            foreach ($successResponse->content as $content) {
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
                            $relationSchema = $relationSchema->resolve();
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
                            $relationSchema = $relationSchema->resolve();
                        }
                        return isset($relationSchema->properties) ? array_keys($relationSchema->properties) : [];
                    }
                    return [];
                }
            }
        }
        return [];
    }

    /**
     * @param Operation $operation
     * @param                              $actionType
     * @return string|null
     * @throws UnresolvableReferenceException
     */
    public static function guessModelClass(Operation $operation, $actionType): ?string
    {
        // first, check request body
        $requestBody = $operation->requestBody;
        if ($requestBody !== null && in_array($actionType, static::REQUEST_BODY_ACTIONS, true)) {
            if ($requestBody instanceof Reference) {
                $requestBody = $requestBody->resolve();
            }

            foreach ($requestBody->content as $content) {
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

            foreach ($successResponse->content as $content) {
                [$modelClass,] = self::guessModelClassFromContent($content);
                if ($modelClass !== null) {
                    return $modelClass;
                }
            }
        }

        return null;
    }

    /**
     * @param SpecObjectInterface $property
     * @return array|null[]
     * @throws UnresolvableReferenceException
     */
    public static function guessModelClassFromJsonResource(SpecObjectInterface $property): array
    {
        $schema = $property instanceof Reference ? $property->resolve() : $property;
        if (self::isObjectSchema($schema)) {
            if (self::hasAttributesReference($schema)) {
                $name = self::schemaNameByRef($schema->properties['attributes']);
                if ($name !== null) {
                    return [$name, '', '', 'object'];
                }
                return [null, null, null, null];
            } else { # https://github.com/cebe/yii2-openapi/issues/172
                $name = self::schemaNameByRef($property);
                if ($name !== null) {
                    return [$name, '', '', 'object'];
                }
            }
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

    /**
     * @param MediaType $content
     * @return array|null[]
     * @throws UnresolvableReferenceException
     */
    public static function guessModelClassFromContent(MediaType $content): array
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
     * @throws UnresolvableReferenceException
     */
    public static function findResponseWrapper(Operation $operation, $modelClass): ?array
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
            foreach ($successResponse->content as $content) {
                [$detectedModelClass, $itemWrapper, $itemsWrapper, $type] = self::guessModelClassFromContent($content);
                if (($itemWrapper !== null || $itemsWrapper !== null) && $detectedModelClass === $modelClass) {
                    return ['item' => $itemWrapper, 'list' => $itemsWrapper, 'type' => $type];
                }
            }
        }
        return null;
    }
}
