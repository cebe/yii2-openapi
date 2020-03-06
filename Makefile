PHPARGS=-dmemory_limit=64M
#PHPARGS=-dmemory_limit=64M -dzend_extension=xdebug.so -dxdebug.remote_enable=1 -dxdebug.remote_host=127.0.0.1 -dxdebug.remote_autostart=1
#PHPARGS=-dmemory_limit=64M -dxdebug.remote_enable=1

all:

check-style:
	vendor/bin/php-cs-fixer fix src/ --diff --dry-run

fix-style:
	vendor/bin/indent --tabs composer.json
	vendor/bin/indent --spaces .php_cs.dist
	vendor/bin/php-cs-fixer fix src/ --diff

install:
	composer install --prefer-dist --no-interaction

test:
	php $(PHPARGS) vendor/bin/phpunit

.PHONY: all check-style fix-style install test

