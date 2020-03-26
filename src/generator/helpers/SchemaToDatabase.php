<?php

namespace cebe\yii2openapi\generator\helpers;

use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Schema;
use yii\base\Component;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * Convert OpenAPI description into a database schema.
 *
 * There are two options:
 *
 * 1. let the generator guess which schemas need a database table
 *    for storing their data and which do not.
 * 2. Explicitly define schemas which represent a database table by adding the
 *    `x-table` property to the schema.
 *
 * The [[]]
 *
 */
class SchemaToDatabase extends Component
{
    /**
     * @var array List of model names to exclude.
     */
    public $excludeModels = [];
    /**
     * @var array Generate database models only for Schemas that have the `x-table` annotation.
     */
    public $generateModelsOnlyXTable = false;


    public function generateModels(OpenApi $openApi)
    {
        $models = [];
        foreach ($openApi->components->schemas as $schemaName => $schema) {
            if ($schema instanceof Reference) {
                $schema = $schema->resolve();
            }

            // only generate tables for schemas of type object and those who have defined properties
            if ((empty($schema->type) || $schema->type === 'object') && empty($schema->properties)) {
                continue;
            }
            if (!empty($schema->type) && $schema->type !== 'object') {
                continue;
            }
            // do not generate tables for composite schemas
            if ($schema->allOf || $schema->anyOf || $schema->multipleOf || $schema->oneOf) {
                continue;
            }
            // skip excluded model names
            if (in_array($schemaName, $this->excludeModels)) {
                continue;
            }

            list($attributes, $relations) = $this->generateAttributesAndRelations($schemaName, $schema);

            $models[$schemaName] = new DbModel([
                'name' => $schemaName,
                'tableName' => '{{%' . ($schema->{'x-table'} ?? $this->generateTableName($schemaName)) . '}}',
                'isDbModel' => true,
                'description' => $schema->description,
                'attributes' => $attributes,
                'relations' => $relations,
            ]);

            if ($this->generateModelsOnlyXTable && empty($schema->{'x-table'})) {
                unset($models[$schemaName]['tableName']);
                $models[$schemaName]['isDbModel'] = false;
            }
        }

        // TODO generate hasMany relations and inverse relations

        return $models;
    }

    /**
     * Auto generate table name from model name.
     * @param string $modelName
     * @return string
     */
    protected function generateTableName($schemaName)
    {
        return Inflector::camel2id(StringHelper::basename(Inflector::pluralize($schemaName)), '_');
    }

    protected function generateAttributesAndRelations($schemaName, Schema $schema)
    {
        $attributes = [];
        $relations = [];
        foreach ($schema->properties as $name => $property) {

            if ($property instanceof Reference) {
                $refPointer = $property->getJsonReference()->getJsonPointer()->getPointer();
                $resolvedProperty = $property->resolve();
                $dbName = "{$name}_id";
                $dbType = 'integer'; // for a foreign key
                if (strpos($refPointer, '/components/schemas/') === 0) {
                    // relation
                    $type = substr($refPointer, 20);
                    $relations[$name] = [
                        'class' => $type,
                        'method' => 'hasOne',
                        'link' => ['id' => $dbName], // TODO pk may not be 'id'
                    ];
                } else {
                    $type = $this->getSchemaType($resolvedProperty);
                }
            } else {
                $resolvedProperty = $property;
                $type = $this->getSchemaType($property);
                $dbName = $name;
                $dbType = $this->getDbType($name, $property);
            }

            // relation
            if (is_array($type)) {
                $relations[$name] = [
                    'class' => $type[1],
                    'method' => 'hasMany',
                    'link' => [Inflector::camel2id($schemaName, '_') . '_id' => 'id'], // TODO pk may not be 'id'
                ];
                $type = $type[0];
            }

            $attributes[$name] = [
                'name' => $name,
                'type' => $type,
                'dbType' => $dbType,
                'dbName' => $dbName,
                'required' => false,
                'readOnly' => $resolvedProperty->readOnly ?? false,
                'description' => $resolvedProperty->description,
                'faker' => $this->guessModelFaker($name, $type, $resolvedProperty),
            ];
        }
        if (!empty($schema->required)) {
            foreach ($schema->required as $property) {
                if (!isset($attributes[$property])) {
                    continue;
                }
                $attributes[$property]['required'] = true;
            }
        }
        return [$attributes, $relations];
    }


    /**
     * @param Schema $schema
     * @return string|array
     */
    protected function getSchemaType($schema)
    {
        switch ($schema->type) {
            case 'integer':
                return 'int';
            case 'boolean':
                return 'bool';
            case 'number': // can be double and float
                return 'float';
            case 'array':
                if (isset($schema->items) && $schema->items instanceof Reference) {
                    $ref = $schema->items->getJsonReference()->getJsonPointer()->getPointer();
                    if (strpos($ref, '/components/schemas/') === 0) {
                        return [substr($ref, 20) . '[]', substr($ref, 20)];
                    }
                }
            // no break here
            default:
                return $schema->type;
        }
    }

    /**
     * @param string $name
     * @param Schema $schema
     * @return string
     */
    protected function getDbType($name, $schema)
    {
        if ($name === 'id') {
            return 'pk';
        }

        switch ($schema->type) {
            case 'string':
                if (isset($schema->maxLength)) {
                    return 'string(' . ((int) $schema->maxLength) . ')';
                }
                return 'text';
            case 'integer':
            case 'boolean':
                return $schema->type;
            case 'number': // can be double and float
                return $schema->format ?? 'float';
//            case 'array':
            // TODO array might refer to has_many relation
//                if (isset($schema->items) && $schema->items instanceof Reference) {
//                    $ref = $schema->items->getJsonReference()->getJsonPointer()->getPointer();
//                    if (strpos($ref, '/components/schemas/') === 0) {
//                        return substr($ref, 20) . '[]';
//                    }
//                }
//                // no break here
            default:
                return 'text';
        }
    }


    /**
     * Guess faker for attribute.
     * @param string $name
     * @param string $type
     * @return string|null the faker PHP code or null.
     * @link https://github.com/fzaninotto/Faker#formatters
     */
    private function guessModelFaker($name, $type, Schema $property)
    {
        if (isset($property->{'x-faker'})) {
            return $property->{'x-faker'};
        }

        $min = $max = null;
        if (isset($property->minimum)) {
            $min = $property->minimum;
            if ($property->exclusiveMinimum) {
                $min++;
            }
        }
        if (isset($property->maximum)) {
            $max = $property->maximum;
            if ($property->exclusiveMaximum) {
                $max++;
            }
        }

        switch ($type) {
            case 'string':
                if ($property->format === 'date') {
                    return '$faker->iso8601';
                }
                if (!empty($property->enum) && is_array($property->enum)) {
                    return '$faker->randomElement('.var_export($property->enum, true).')';
                }
                if ($name === 'title' && isset($property->maxLength) && $property->maxLength < 10) {
                    return '$faker->title';
                }

                $patterns = [
                    '~_id$~' => '$uniqueFaker->numberBetween(0, 1000000)',
                    '~uuid$~' => '$uniqueFaker->uuid',
                    '~firstname~i' => '$faker->firstName',
                    '~(last|sur)name~i' => '$faker->lastName',
                    '~(username|login)~i' => '$faker->userName',
                    '~(company|employer)~i' => '$faker->company',
                    '~(city|town)~i' => '$faker->city',
                    '~(post|zip)code~i' => '$faker->postcode',
                    '~streetaddress~i' => '$faker->streetAddress',
                    '~address~i' => '$faker->address',
                    '~street~i' => '$faker->streetName',
                    '~state~i' => '$faker->state',
                    '~county~i' => 'sprintf("%s County", $faker->city)',
                    '~country~i' => '$faker->countryCode',
                    '~lang~i' => '$faker->languageCode',
                    '~locale~i' => '$faker->locale',
                    '~currency~i' => '$faker->currencyCode',
                    '~hash~i' => '$faker->sha256',
                    '~e?mail~i' => '$faker->safeEmail',
                    '~timestamp~i' => '$faker->unixTime',
                    '~.*ed_at$~i' => '$faker->dateTimeThisCentury(\'Y-m-d H:i:s\')', // created_at, updated_at, ...
                    '~(phone|fax|mobile|telnumber)~i' => '$faker->e164PhoneNumber',
                    '~(^lat|coord)~i' => '$faker->latitude',
                    '~^lon~i' => '$faker->longitude',
                    '~title~i' => '$faker->sentence',
                    '~(body|summary|article|content|descr|comment|detail)~i' => '$faker->paragraphs(6, true)',
                    '~(url|site|website)~i' => '$faker->url',
                ];
                foreach ($patterns as $pattern => $faker) {
                    if (preg_match($pattern, $name)) {
                        if (isset($property->maxLength)) {
                            return 'substr(' . $faker . ', 0, ' . $property->maxLength . ')';
                        } else {
                            return $faker;
                        }
                    }
                }

                // TODO maybe also consider OpenAPI examples here

                if (isset($property->maxLength)) {
                    return 'substr($faker->text(' . $property->maxLength . '), 0, ' . $property->maxLength . ')';
                }
                return '$faker->sentence';
            case 'int':
                $fakerVariable = preg_match('~_?id$~', $name) ? 'uniqueFaker' : 'faker';

                if ($min !== null && $max !== null) {
                    return "\${$fakerVariable}->numberBetween($min, $max)";
                } elseif ($min !== null) {
                    return "\${$fakerVariable}->numberBetween($min, PHP_INT_MAX)";
                } elseif ($max !== null) {
                    return "\${$fakerVariable}->numberBetween(0, $max)";
                }

                $patterns = [
                    '~timestamp~i' => '$faker->unixTime',
                    '~.*ed_at$~i' => '$faker->unixTime', // created_at, updated_at, ...
                ];
                foreach ($patterns as $pattern => $faker) {
                    if (preg_match($pattern, $name)) {
                        return $faker;
                    }
                }

                return "\${$fakerVariable}->numberBetween(0, PHP_INT_MAX)";
            case 'bool':
                return '$faker->boolean';
            case 'float':
                if ($min !== null && $max !== null) {
                    return "\$faker->randomFloat(null, $min, $max)";
                } elseif ($min !== null) {
                    return "\$faker->randomFloat(null, $min)";
                } elseif ($max !== null) {
                    return "\$faker->randomFloat(null, 0, $max)";
                }
                return '$faker->randomFloat()';
        }


        return null;
    }

}
