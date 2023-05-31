Yii2-openapi Contribution Docs
==============================

To contribute or play around, steps to set up this project up locally are:

```bash
# in your CLI
git clone https://github.com/cebe/yii2-openapi.git
cd yii2-openapi
make clean_all
make up
make installdocker
make migrate

# to check everything is setup up correctly ensure all tests passes
make cli
./vendor/bin/phpunit

# create new branch from master and Happy contributing!
```

These commands are available to develop and check the tests. It is available inside the Docker container. To enter into bash shell of container, run `make cli` .

```bash
cd tests
./yii migrate-mysql/up
./yii migrate-mysql/down 4

./yii migrate-maria/up
./yii migrate-maria/down 4

./yii migrate-pgsql/up
./yii migrate-pgsql/down 4
```

To apply multiple migration with one command:

```bash
./yii migrate-mysql/up --interactive=0 && \
./yii migrate-mysql/down --interactive=0 4 && \
./yii migrate-maria/up --interactive=0 && \
./yii migrate-maria/down --interactive=0 4 && \
./yii migrate-pgsql/up --interactive=0 && \
./yii migrate-pgsql/down --interactive=0 4
```

PHPUnit run only one test by regex
----------------------------------

If a PHPUnit test file have 2 test method with names like `testEdit()` and `testEditExpression()` then by running `./vendor/bin/phpunit --filter XDbDefaultExpressionTest::testEdit` both tests will run. In order to run only one test `testEdit()`, run below command:

```bash
./vendor/bin/phpunit --filter '/XDbDefaultExpressionTest::testEdit$/'
```


Switching PHP versions
----------------------

You can switch the PHP version of the docker runtime by changing the `PHP_VERSION` environment variable in the `.env` file.

If you have no `.env` file yet, create it by copying `.env.dist` to `.env`.

After changing the PHP Version you need to run `make down up` to start the new container with new version.

Example:

```
$ echo "PHP_VERSION=7.4" > .env
$ make down up cli
Stopping yii2-openapi_php_1      ... done
Stopping yii2-openapi_maria_1    ... done
Stopping yii2-openapi_postgres_1 ... done
Stopping yii2-openapi_mysql_1    ... done
Removing yii2-openapi_php_1      ... done
Removing yii2-openapi_maria_1    ... done
Removing yii2-openapi_postgres_1 ... done
Removing yii2-openapi_mysql_1    ... done
Removing network yii2-openapi_default
Creating network "yii2-openapi_default" with driver "bridge"
Creating yii2-openapi_maria_1    ... done
Creating yii2-openapi_mysql_1    ... done
Creating yii2-openapi_postgres_1 ... done
Creating yii2-openapi_php_1      ... done
docker-compose exec php bash

root@f9928598f841:/app# php -v

PHP 7.4.27 (cli) (built: Jan 26 2022 18:08:44) ( NTS )
Copyright (c) The PHP Group
Zend Engine v3.4.0, Copyright (c) Zend Technologies
with Zend OPcache v7.4.27, Copyright (c), by Zend Technologies
with Xdebug v2.9.6, Copyright (c) 2002-2020, by Derick Rethans
```

Issues and solutions
--------------------

#### Issue when switching PHP version as mentioned above

```
root@f65bb59c3289:/app# ./vendor/bin/phpunit

Fatal error: Composer detected issues in your platform: Your Composer dependencies require a PHP version ">= 8.1.0". You are running 7.4.33. in /app/vendor/composer/platform_check.php on line 24
```

#### Solution

```bash
sudo rm -rf vendor
composer update
```


## Use PR of your own fork of this library in your project to check new changes

Say you have a fork of this library at https://github.com/SOHELAHMED7/yii2-openapi

You implemented new changes or fixed bugs and created PR on GitHub. It is not yet merged in upstream master branch.

You wanted to check this new changes in your own project which is using this lib cebe/yii2-openapi.

You can accomplish it by:


Add below to composer.json of your project file


```json
    "repositories": [
        {
            "type": "composer",
            "url": "https://asset-packagist.org"
        },
        {
            "type": "vcs",
            "url": "http://github.com/SOHELAHMED7/yii2-openapi"
        }
    ]
```

Then lets say you have PR https://github.com/SOHELAHMED7/yii2-openapi/pull/24.

And branch name is `143-if-data-type-is-not-changed-then-still-migrations-are-generated-for-timestamp-in-mysql`.

Run below command:

```bash
composer require cebe/yii2-openapi:dev-143-if-data-type-is-not-changed-then-still-migrations-are-generated-for-timestamp-in-mysql
```

Ensure to use upstream package name `cebe/yii2-openapi` instead of your fork (`sohelahmed7/yii2-openapi`) in composer command. And prefix branch name by `dev-`
