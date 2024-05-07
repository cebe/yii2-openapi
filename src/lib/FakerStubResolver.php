<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

/** @noinspection InterfacesAsConstructorDependenciesInspection */
/** @noinspection PhpUndefinedFieldInspection */
namespace cebe\yii2openapi\lib;

use cebe\yii2openapi\lib\items\Attribute;
use cebe\yii2openapi\lib\openapi\PropertySchema;
use yii\helpers\VarDumper;
use function str_replace;
use const PHP_EOL;

/**
 * Guess faker for attribute
 * @link https://github.com/fzaninotto/Faker#formatters
 **/
class FakerStubResolver
{
    public const MAX_INT = 1000000;
    /**
     * @var \cebe\yii2openapi\lib\items\Attribute
     */
    private $attribute;

    /**
     * @var \cebe\yii2openapi\lib\openapi\PropertySchema
     */
    private $property;

    /** @var Config */
    private $config;

    public function __construct(Attribute $attribute, PropertySchema $property, ?Config $config = null)
    {
        $this->attribute = $attribute;
        $this->property = $property;
        $this->config = $config;
    }

    public function resolve():?string
    {
        if ($this->property->xFaker === false) {
            $this->attribute->setFakerStub(null);
            return null;
        }
        if ($this->property->hasAttr(CustomSpecAttr::FAKER)) {
            $fakerVal = $this->property->getAttr(CustomSpecAttr::FAKER);
            if ($fakerVal === false) {
                $this->attribute->setFakerStub(null);
                return null;
            }
            return $fakerVal;
        }

        if ($this->attribute->isReadOnly() && $this->attribute->isVirtual()) {
            return null;
        }

        // column name ends with `_id`
        if (substr($this->attribute->columnName, -3) === '_id' || !empty($this->attribute->fkColName)) {
            $config = $this->config;
            if (!$config) {
                $config = new Config;
            }
            $mn = $config->modelNamespace;
            return '$faker->randomElement(\\'.$mn
                    . ($mn ? '\\' : '')
                    . ucfirst($this->attribute->reference).'::find()->select("id")->column())';
        }

        $limits = $this->attribute->limits;
        switch ($this->attribute->phpType) {
            case 'bool':
                return '$faker->boolean';
            case 'int':
            case 'integer':
                return $this->fakeForInt($limits['min'], $limits['max']);
            case 'string':
                return $this->fakeForString();
            case 'float':
            case 'double':
                return $this->fakeForFloat($limits['min'], $limits['max']);
            case 'array':
                return $this->fakeForArray();
            default:
                return null;
        }
    }

    private function fakeForString():?string
    {
        $formats = [
            'date' => '$faker->dateTimeThisCentury->format(\'Y-m-d\')',
            'date-time' => '$faker->dateTimeThisYear(\'now\', \'UTC\')->format(\'Y-m-d H:i:s\')', // DATE_ATOM=>ISO-8601
            'email' => '$faker->safeEmail',

            // for x-db-type
            'datetime' => '$faker->dateTimeThisYear(\'now\', \'UTC\')->format(\'Y-m-d H:i:s\')', // DATE_ATOM=>ISO-8601
            'timestamp' => '$faker->dateTimeThisYear(\'now\', \'UTC\')->format(\'Y-m-d H:i:s\')', // DATE_ATOM=>ISO-8601
            'time' => '$faker->time(\'H:i:s\')',
            'year' => '$faker->year',
        ];
        $format = $this->property->getAttr('format');
        $format = $format === null ? $this->property->getAttr('x-db-type') : $format;
        if ($format && isset($formats[$format])) {
            return $formats[$format];
        }
        $enum = $this->property->getAttr('enum');
        if (!empty($enum) && is_array($enum)) {
            $items = str_replace([PHP_EOL, '  ',',]'], ['', '', ']'], VarDumper::export($enum));
            return '$faker->randomElement(' . $items . ')';
        }
        if ($this->attribute->columnName === 'title'
            && $this->attribute->size
            && (int)$this->attribute->size < 10) {
            return '$faker->title';
        }
        if ($this->attribute->primary || $this->attribute->isReference()) {
            $size = $this->attribute->size ?? 255;
            return 'substr($uniqueFaker->sha256, 0, ' . $size . ')';
        }

        $patterns = [
            '~_id$~' => '$uniqueFaker->numberBetween(0, 1000000)',
            '~uuid$~' => '$uniqueFaker->uuid',
            '~slug$~' => '$uniqueFaker->slug',
            '~firstname~i' => '$faker->firstName',
            '~password~i' => '$faker->password',
            '~(last|sur)name~i' => '$faker->lastName',
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
            '~(hash|token)~i' => '$faker->sha256',
            '~e?mail~i' => '$faker->safeEmail',
            '~timestamp~i' => '$faker->unixTime',
            '~.*At$~' => '$faker->dateTimeThisCentury->format(\'Y-m-d H:i:s\')', // createdAt, updatedAt, ...
            '~.*ed_at$~i' => '$faker->dateTimeThisCentury->format(\'Y-m-d H:i:s\')', // created_at, updated_at, ...
            '~(phone|fax|mobile|telnumber)~i' => '$faker->e164PhoneNumber',
            '~(^lat|coord)~i' => '$faker->latitude',
            '~^lon~i' => '$faker->longitude',
            '~title~i' => '$faker->sentence',
            '~(body|summary|article|content|descr|comment|detail)~i' => '$faker->paragraphs(6, true)',
            '~(url|site|website|href)~i' => '$faker->url',
            '~(username|login)~i' => '$faker->userName',
        ];
        $size = $this->attribute->size > 0 ? $this->attribute->size: null;
        foreach ($patterns as $pattern => $fake) {
            if (preg_match($pattern, $this->attribute->columnName)) {
                if ($size) {
                    return 'substr(' . $fake . ', 0, ' . $size . ')';
                }
                return $fake;
            }
        }

        // TODO maybe also consider OpenAPI examples here

        if ($size) {
            $method = 'text';
            if ($size < 5) {
                $method = 'word';
            }
            return 'substr($faker->'.$method.'(' . $size . '), 0, ' . $size . ')';
        }
        return '$faker->sentence';
    }

    private function fakeForInt(?int $min, ?int $max):?string
    {
        $fakerVariable = 'faker';
        if (preg_match('~_?id$~', $this->attribute->columnName)) {
            $fakerVariable = 'uniqueFaker';
        }
        if ($min !== null && $max !== null) {
            return "\${$fakerVariable}->numberBetween($min, $max)";
        }

        if ($min !== null) {
            return "\${$fakerVariable}->numberBetween($min, ".self::MAX_INT.")";
        }

        if ($max !== null) {
            return "\${$fakerVariable}->numberBetween(0, $max)";
        }

        $patterns = [
            '~timestamp~i' => '$faker->unixTime',
            '~.*At$~' => '$faker->unixTime', // createdAt, updatedAt, ...
            '~.*_date$~' => '$faker->unixTime', // creation_date, ...
            '~.*ed_at$~i' => '$faker->unixTime', // created_at, updated_at, ...
        ];
        foreach ($patterns as $pattern => $fake) {
            if (preg_match($pattern, $this->attribute->columnName)) {
                return $fake;
            }
        }
        return "\${$fakerVariable}->numberBetween(0, ".self::MAX_INT.")";
    }

    private function fakeForFloat(?int $min, ?int $max):?string
    {
        if ($min !== null && $max !== null) {
            return "\$faker->randomFloat(null, $min, $max)";
        }
        if ($min !== null) {
            return "\$faker->randomFloat(null, $min)";
        }
        if ($max !== null) {
            return "\$faker->randomFloat(null, 0, $max)";
        }
        return '$faker->randomFloat()';
    }

    private function fakeForArray():string
    {
        if ($this->attribute->required) {
            return '["a" => "b"]';
        }
        return '[]';
    }
}
