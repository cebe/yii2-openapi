# yii2-openapi

> **Note: The status of this code is experimental. Use at your own risk.**

REST API application generator for Yii2, openapi 3.0 YAML -> Yii2.

Base on [Gii, the Yii Framework Code Generator](https://www.yiiframework.com/extension/yiisoft/yii2-gii).

[![Latest Stable Version](https://poser.pugx.org/cebe/yii2-openapi/v/stable)](https://packagist.org/packages/cebe/yii2-openapi)
[![License](https://poser.pugx.org/cebe/yii2-openapi/license)](https://packagist.org/packages/cebe/yii2-openapi)
[![Build Status](https://travis-ci.org/cebe/yii2-openapi.svg?branch=master)](https://travis-ci.org/cebe/yii2-openapi)

## what should this do?

Input: [OpenAPI 3.0 YAML or JSON](https://github.com/OAI/OpenAPI-Specification#the-openapi-specification) (via [cebe/php-openapi](https://github.com/cebe/php-openapi))

Output: Controllers, Models, database schema

## Features

This library is currently work in progress, current features are checked here when ready:

- [x] generate Controllers + Actions
- [x] generate Models
- [x] generate Database migration
- [x] provide Dummy API via Faker

- [ ] update Database and models when API schema changes

## Requirements

- PHP 7.1 or higher


## Install

    composer require cebe/yii2-openapi:@dev

## Usage

In your Yii application config (works for console as well as web):

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


## Screenshots

Gii Generator Form:

![Gii Generator Form](doc/screenshot-form.png)

Generated files:

![Gii Generated Files](doc/screenshot-files.png)


# Support

Professional support, consulting as well as software development services are available:

https://www.cebe.cc/en/contact

Development of this library is sponsored by [cebe.:cloud: "Your Professional Deployment Platform"](https://cebe.cloud).
