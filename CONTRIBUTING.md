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



