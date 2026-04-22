.PHONY: test lint setup import

lint:
	vendor/bin/phpstan analyse

test:
	vendor/bin/phpunit

check: lint test

setup:
	composer install
	docker compose up -d
	php bin/console doctrine:database:create --if-not-exists
	php bin/console doctrine:migrations:migrate --no-interaction