# yii2-openapi

REST API application generator for Yii2, openapi 3.0 YAML -> Yii2.

Base on [Gii, the Yii Framework Code Generator](https://www.yiiframework.com/extension/yiisoft/yii2-gii).

[![Latest Stable Version](https://poser.pugx.org/cebe/yii2-openapi/v/stable)](https://packagist.org/packages/cebe/yii2-openapi)
[![Latest Alpha Version](https://poser.pugx.org/cebe/yii2-openapi/v/unstable)](https://packagist.org/packages/cebe/yii2-openapi)
[![Total Downloads](https://poser.pugx.org/cebe/yii2-openapi/downloads)](https://packagist.org/packages/cebe/yii2-openapi)
[![License](https://poser.pugx.org/cebe/yii2-openapi/license)](https://packagist.org/packages/cebe/yii2-openapi)
![yii2-openapi](https://github.com/cebe/yii2-openapi/workflows/yii2-openapi/badge.svg?branch=wip)

## what should this do?

Input: [OpenAPI 3.0 YAML or JSON](https://github.com/OAI/OpenAPI-Specification#the-openapi-specification) (via [cebe/php-openapi](https://github.com/cebe/php-openapi))

Output: Controllers, Models, database schema

## Features

This library is currently work in progress, current features are checked here when ready:

- [x] generate Controllers + Actions
- [x] generate Models
- [x] generate Database migration
- [x] provide Dummy API via Faker
- [x] update Database and models when API schema changes

## Requirements

- PHP 7.1 or higher (works fine with PHP 8)


## Install

    composer require cebe/yii2-openapi:^2.0@alpha

## Usage

You can use this package in your existing application or start a new project using the
[yii2-app-api](https://github.com/cebe/yii2-app-api) application template.
For usage of the template, see instructions in the template repo readme.

In your existing Yii application config (works for console as well as web):

```php
<?php
$config = [
    // ... this is your application config ...
];

if (YII_ENV_DEV) {
    // enable Gii module
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => yii\gii\Module::class,
        'generators' => [
            // add ApiGenerator to Gii module
            'api' => \cebe\yii2openapi\generator\ApiGenerator::class,
        ],
    ];
}

return $config;
```

To use the web generator, open `index.php?r=gii` and select the `REST API Generator`.

On console you can run the generator with `./yii gii/api --openApiPath=@app/openapi.yaml`. Where `@app/openapi.yaml` should be the absolute path to your OpenAPI spec file. This can be JSON as well as YAML (see also [cebe/php-openapi](https://github.com/cebe/php-openapi/) for supported formats).

Run `./yii gii/api --help` for all options.


## OpenAPI extensions

This library understands the following extensions to the OpenAPI spec:

### `x-faker`

You may specify custom PHP code for generating fake data for a property:

```yaml
    Post:
      properties:
        id:
          type: integer
        tags:
          type: array
          items:
            type: string
          example: ['one', 'two']
          x-faker: "$faker->randomElements(['one', 'two', 'three', 'four'])"
```

### `x-table`

Specify the table name for a Schema that defines a model which is stored in the database.
You can generate non-db model based on \yii\base\Model without migrations by setting `x-table: false`

### `x-pk`

Explicitly specify primary key name for table, if it is different from "id" 

```yaml
    Post:
      x-table: posts
      x-pk: uid
      properties:
        uid:
           type: integer
        title:
           type: string
```

### `x-db-type`

Explicitly specify the database type for a column. (MUST contains only db type! (json, jsonb, uuid, varchar etc))
If x-db-type sets as false, property will be processed as virtual;
It will be added in model as public property, but skipped for migrations generation

### `x-indexes`
Specify table indexes

```yaml
    Post:
      x-table: posts
      x-indexes:
          - 'visible,publish_date'
          - 'unique:title' #for unique attributes also unique validation check will be added
          - 'gist:metadata' #for postgres will generate index using GIST index type
      properties:
        id:
           type: integer
           x-db-type: INTEGER PRIMARY KEY AUTO_INCREMENT
        title:
           type: string
        visible:
            type: boolean
        publish_date:
            type: string
            format: date
        metadata:
           type: object
           x-db-type: JSON
           default: '{}' 
```

### Many-to-Many relation definition

There are two ways for define many-to-many relations:

#### Simple many-to-many without junction model

   - property name for many-to-many relation should be equal lower-cased, pluralized related schema name
     
   - referenced schema should contains mirrored reference to current schema
     
   - migration for junction table can be generated automatically - table name should be [pluralized, lower-cased
 schema_name1]2[pluralized, lower-cased schema name2], in alphabetical order;
 For example, for schemas Post and Tag - table should be posts2tags, for schemas Post and Attachement - table should
  be attachments2posts
  
```
Post:
  properties:
  ...
    tags:
      type: array
      items:
        $ref: '#/components/schemas/Tag'

Tag:
  properties:
  ...
    posts:
      type: array
      items:
        $ref: '#/components/schemas/Post'
```
  
#### Many-to-many with junction model 

This way allowed creating multiple many-to-many relations between to models 

- define junction schema with all necessary attributes. There are only one important requirement - the junction
 schema name
 must be started with prefix 'junction_' (This prefix will be used internally only and
 will be trimmed before table and model generation)
 
```
# Model TeamMembers with table team_members will be generated with columns team_id, user_id and role
junction_TeamMembers:
   team:
      $ref: '#/components/schemas/Team'
   user:
      $ref: '#/components/schemas/User'
   role:
     type: string
```
- Both many-to-many related schemas must have properties with reference to "junction_*" schema. These properties will be
 used as relation names 

```
Team:
  properties:
  ...
     team_members:
       type: array
       items:
         $ref: '#/components/schemas/junction_TeamMembers'

User:
  properties:
  ...
    memberships: #You absolutely free with naming for relationship attributes
      type: array
      items:
        $ref: '#/components/schemas/junction_TeamMembers'
```
  
 - see both examples here [tests/specs/many2many.yaml](tests/specs/many2many.yaml)
 

## Things to keep in mind

### Adding columns to existing tables

When adding new fields in the API models, new migrations will be generated to add these fields to the table.
For a project that is already in production, it should be considered to adjust the generated migration to add default
values for existing data records.

One case where this is important is the addition of a new column with `NOT NULL` contraint, which does not provide a default value.
Such a migration will fail when the table is not empty:

```php
$this->addColumn('{{%company}}', 'name', $this->string(128)->notNull());
```

Fail on a PostgreSQL database with 

> add column name string(128) NOT NULL to table {{%company}} ...Exception: SQLSTATE[23502]: Not null violation: 7 ERROR:  column "name" contains null values

The solution would be to create the column, allowing NULL, set the value to a default and add the null constraint later.

```php
$this->addColumn('{{%company}}', 'name', $this->string(128)->null());
$this->update('{{%company}}', ['name' => 'No name']);
$this->alterColumn('{{%company}}', 'name', $this->string(128)->notNull());
```


## Screenshots

Gii Generator Form:

![Gii Generator Form](doc/screenshot-form.png)

Generated files:

![Gii Generated Files](doc/screenshot-files.png)

# Support

**Need help with your API project?**

Professional support, consulting as well as software development services are available:

https://www.cebe.cc/en/contact

Development of this library is sponsored by [cebe.:cloud: "Your Professional Deployment Platform"](https://cebe.cloud).
