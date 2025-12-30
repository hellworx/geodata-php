# Makefile

.PHONY: build lint test

all: build lint test

build:
	composer install --no-interaction

lint:
	./vendor/bin/phpstan analyse -c phpstan.neon

test:
	./vendor/bin/phpunit .
