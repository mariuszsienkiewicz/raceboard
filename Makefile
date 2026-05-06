.PHONY: test lint setup import

lint:
	vendor/bin/phpstan analyse

test:
	vendor/bin/phpunit

cs:
	vendor/bin/php-cs-fixer fix --dry-run --diff

cs-fix:
	vendor/bin/php-cs-fixer fix

check: lint cs test

setup:
	composer install
	docker compose up -d
	php bin/console doctrine:database:create --if-not-exists
	php bin/console doctrine:migrations:migrate --no-interaction