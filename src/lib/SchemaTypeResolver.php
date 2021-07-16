<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib;

use cebe\openapi\spec\Schema;
use yii\db\Schema as YiiDbSchema;
use yii\helpers\StringHelper;
use function in_array;
use function strtolower;

class SchemaTypeResolver
{
    public static function schemaToPhpType(Schema $property):string
    {
        $customDbType = isset($property->{CustomSpecAttr::DB_TYPE})
            ? strtolower($property->{CustomSpecAttr::DB_TYPE}) : null;
        if ($customDbType !== null
            && (in_array($customDbType, ['json', 'jsonb'], true) || StringHelper::endsWith($customDbType, '[]'))
        ) {
            return 'array';
        }
        switch ($property->type) {
            case 'integer':
                return 'int';
            case 'boolean':
                return 'bool';
            case 'number': // can be double and float
                return $property->format && $property->format === 'double' ? 'double' : 'float';
//            case 'array':
//                return $property->type;
            default:
                return $property->type ?? 'string';
        }
    }

    public static function referenceToDbType(Schema $property):string
    {
        $format = $property->format ?? null;
        if ($property->type === 'integer') {
            return $format === 'int64' ? YiiDbSchema::TYPE_BIGINT : YiiDbSchema::TYPE_INTEGER;
        }
        return $property->type;
    }

    public static function schemaToDbType(Schema $property, bool $isPrimary = false):string
    {
        if (isset($property->{CustomSpecAttr::DB_TYPE}) && $property->{CustomSpecAttr::DB_TYPE} !== false) {
            $customDbType = strtolower($property->{CustomSpecAttr::DB_TYPE});
            if ($customDbType === 'varchar') {
                return YiiDbSchema::TYPE_STRING;
            }
            if ($customDbType !== null) {
                return $customDbType;
            }
        }
        $format = $property->format ?? null;
        if ($isPrimary && $property->type === 'integer') {
            return $format === 'int64' ? YiiDbSchema::TYPE_BIGPK : YiiDbSchema::TYPE_PK;
        }

        switch ($property->type) {
            case 'boolean':
                return $property->type;
            case 'number': // can be double and float
                return $format ?? 'float';
            case 'integer':
                if ($format === 'int64') {
                    return YiiDbSchema::TYPE_BIGINT;
                }
                if ($format === 'int32') {
                    return YiiDbSchema::TYPE_INTEGER;
                }
                return YiiDbSchema::TYPE_INTEGER;
            case 'string':
                if (in_array($format, ['date', 'time', 'binary'])) {
                    return $format;
                }
                if ($property->maxLength && $property->maxLength < 2049) {
                    //What if we want to restrict length of text column?
                    return YiiDbSchema::TYPE_STRING;
                }
                if ($format === 'date-time' || $format === 'datetime') {
                    return YiiDbSchema::TYPE_DATETIME;
                }
                if (in_array($format, ['email', 'url', 'phone', 'password'])) {
                    return YiiDbSchema::TYPE_STRING;
                }
                if (isset($property->enum) && !empty($property->enum)) {
                    return YiiDbSchema::TYPE_STRING;
                }
                return YiiDbSchema::TYPE_TEXT;
            case 'object': {
                return YiiDbSchema::TYPE_JSON;
            }
//            case 'array':
//                Need schema example for this case if it possible
//                return $this->typeForArray();
            default:
                return YiiDbSchema::TYPE_TEXT;
        }
    }
}
