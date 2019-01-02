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

- [ ] generate Controllers + Actions
- [ ] generate Models
- [ ] generate Database migration

- [ ] update Database and models when API schema changes

## Install

    composer require cebe/php-openapi:~0.9@beta

## Requirements

- PHP 7.1 or higher


## Screenshots

Gii Generator Form:

![Gii Generator Form](doc/screenshot-form.png)

Generated files:

![Gii Generated Files](doc/screenshot-files.png)


# Support

Professional support, consulting as well as software development services are available:

https://www.cebe.cc/en/contact

Development of this library is sponsored by [cebe.:cloud: "Your Professional Deployment Platform"](https://cebe.cloud).
