PHPARGS=-dmemory_limit=64M
#PHPARGS=-dmemory_limit=64M -dzend_extension=xdebug.so -dxdebug.remote_enable=1 -dxdebug.remote_host=127.0.0.1 -dxdebug.remote_autostart=1
#PHPARGS=-dmemory_limit=64M -dxdebug.remote_enable=1

all:

check-style:
	vendor/bin/php-cs-fixer fix --diff --dry-run

check-style-from-host:
	docker-compose run --rm php sh -c 'vendor/bin/php-cs-fixer fix --diff --dry-run'

fix-style:
	vendor/bin/indent --tabs composer.json
	vendor/bin/indent --spaces .php_cs.dist
	vendor/bin/php-cs-fixer fix src/ --diff

install:
	composer install --prefer-dist --no-interaction

test:
	php $(PHPARGS) vendor/bin/phpunit

clean_all:
	docker-compose down
	sudo rm -rf tests/tmp/*

clean:
	sudo rm -rf tests/tmp/app/*
	sudo rm -rf tests/tmp/docker_app/*

up:
	docker-compose up -d

cli:
	docker-compose exec php bash

migrate:
	docker-compose run --rm php sh -c 'mkdir -p "tests/tmp/app"'
	docker-compose run --rm php sh -c 'mkdir -p "tests/tmp/docker_app"'
	docker-compose run --rm php sh -c 'cd /app/tests && ./yii migrate  --interactive=0'

installdocker:
	docker-compose run --rm php composer install && chmod +x tests/yii

testdocker:
	docker-compose run --rm php sh -c 'vendor/bin/phpunit'

.PHONY: all check-style fix-style install test clean clean_all up cli installdocker migrate testdocker


# Docs:

# outside docker
#     clean_all
#     clean (in both)
#     up
#     cli
#     migrate
#     installdocker
#     testdocker

# inside docker
#     check-style
#     fix-style
#     install
#     test
#     clean (in both)
