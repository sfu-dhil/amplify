# Silence output slightly
# .SILENT:

PHP ?= $(shell which php)
CONSOLE := $(PHP) bin/console
SYMFONY ?= $(shell which symfony)
PHPUNIT := ./vendor/bin/phpunit
PHPCSF := ./vendor/bin/php-cs-fixer
PHPSTAN := ./vendor/bin/phpstan
TWIGCS := ./vendor/bin/twigcs

CODE_DIRS := bin config migrations src templates tests

## -- Help
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9._-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | sed -e 's/^.*Makefile[^:]*://' | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'


## -- Tests

test.reset: ## Create a test database and load the fixtures in it
	rm -rf var/cache/test/* data/test/*
	rm -f var/log/test-*.log
	$(CONSOLE) --env=test doctrine:database:create  --quiet --if-not-exists
	$(CONSOLE) --env=test doctrine:schema:drop  --quiet --force
	$(CONSOLE) --env=test doctrine:schema:create  --quiet
	$(CONSOLE) --env=test doctrine:schema:validate  --quiet
	$(CONSOLE) --env=test doctrine:cache:clear-metadata --quiet
	$(CONSOLE) --env=test doctrine:fixtures:load --quiet --no-interaction --group=dev

test.run: ## Directly run tests. Use optional path=/path/to/tests to limit target
	$(PHPUNIT) $(path)

test: test.reset test.run ## Run all tests. Use optional path=/path/to/tests to limit target

test.cover: test.reset ## Generate a test cover report
	$(PHP) -dpcov.enabled=1 -dpcov.directory=. -dpcov.exclude="~vendor~" $(PHPUNIT) $(path) --coverage-html=coverage

test.cover.view: ## Open the test coverage html file
	open coverage/index.html


## -- Cache targets

cc: ## Clear the symfony cache
	$(CONSOLE) cache:clear
	$(CONSOLE) cache:warmup

cc.purge: ## Remove cache and log files
	rm -rf var/cache/*/*
	rm -f var/log/*


## -- Assets

assets: ## Link assets into /public
	$(CONSOLE) assets:install --symlink


## Database migrations

migrate: ## Run any migrations as required
	$(CONSOLE) doctrine:migrations:migrate --no-interaction --allow-no-migration

migrate.down: ## Undo one migration
	# This is arcane nonsense and only works in GNU Make
	$(eval CURRENT=$(shell $(CONSOLE) doctrine:migrations:current))
	$(CONSOLE) doctrine:migrations:execute '$(CURRENT)' --down

migrate.diff: ## Generate a migration by diffing the db and entities
	$(CONSOLE) doctrine:migrations:diff --no-interaction --quiet

migrate.status: ## Status of database migrations
	$(CONSOLE) doctrine:migrations:status

migrate.rollup: ## Roll up all migrations in to a schema definition
	rm -rf migrations/*
	$(CONSOLE) doctrine:migrations:dump-schema --no-interaction --quiet
	$(CONSOLE) doctrine:migrations:rollup --no-interaction --quiet
	$(PHPCSF) fix migrations

migrate.reset: ## Reset all migrations metadata
	$(CONSOLE) doctrine:migrations:version --delete --all --no-interaction --quiet
	$(CONSOLE) doctrine:migrations:version --add --all --no-interaction --quiet


## -- Container debug targets

dump.autowire: ## Show autowireable services
	$(CONSOLE) debug:autowiring --all

dump.container: ## Show container information
	$(CONSOLE) debug:container

dump.env: ## Show all environment variables in the container
	$(CONSOLE) debug:container --env-vars

dump.params: ## List all of the nines container parameters
	$(CONSOLE) debug:container --parameters

dump.router: ## Display rounting information
	$(CONSOLE) debug:router

dump.twig: ## Show all twig configuration
	$(CONSOLE) debug:twig


## -- Coding standards fixing

fix: ## Fix the code with the CS rules
	$(PHPCSF) fix $(path)

fix.cc: ## Remove the PHP CS Cache file
	rm -f var/cache/php_cs.cache

fix.all: fix.cc fix ## Ignore the CS cache and fix the code with the CS rules

fix.list: ## Check the code against the CS rules
	$(PHPCSF) fix --dry-run -v $(path)

## -- Coding standards checking

lint-all: stan.cc stan twiglint twigcs yamllint

symlint: yamllint twiglint ## Run the symfony linting checks
	$(SYMFONY) security:check --quiet
	$(CONSOLE) lint:container --quiet
	$(CONSOLE) doctrine:schema:validate --quiet --skip-sync -vvv --no-interaction

twiglint: ## Check the twig templates for syntax errors
	$(CONSOLE) lint:twig templates

twigcs: ## Check the twig templates against the coding standards
	$(TWIGCS) templates

yamllint:
	$(CONSOLE) lint:yaml templates

stan: ## Run static analysis
	$(PHPSTAN) --memory-limit=1G analyze $(CODE_DIRS)

stan.cc: ## Clear the static analysis cache
	$(PHPSTAN) clear-result-cache

stan.baseline: ## Generate a new phpstan baseline file
	$(PHPSTAN) --memory-limit=1G analyze --generate-baseline $(CODE_DIRS)

