PFX := $(shell brew --prefix)

# Executables
COMPOSER := $(PFX)/bin/composer
SYMFONY := $(PFX)/bin/symfony
YARN := $(PFX)/bin/yarn
SASS := $(PFX)/bin/sass
PHP := $(PFX)/bin/php
BREW := $(PFX)/bin/brew
GIT := $(PFX)/bin/git
SOLR := $(PFX)/bin/solr

# Aliases
CONSOLE := $(PHP) bin/console

# Vendor executables
PHPUNIT := ./vendor/bin/phpunit
PHPSTAN := ./vendor/bin/phpstan
PHPCSF := ./vendor/bin/php-cs-fixer
TWIGCS := ./vendor/bin/twigcs

# Misc Makefile stuff
.DEFAULT_GOAL = help
.PHONY:

# Silence output slightly
# .SILENT:

# Useful URLs
PROJECT := amplify
PROJECT_TEST := $(PROJECT)_test

LOCAL := http://localhost/$(PROJECT)/public

SOLR := http://localhost:8983/solr/\#/$(PROJECT)/core-overview
SOLR_TEST := http://localhost:8983/solr/\#/$(PROJECT_TEST)/core-overview

## -- Help
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9._-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## -- General targets
open: ## Open the project home page in a browser
	open $(LOCAL)

clean.git: ## Force clean the git metadata
	$(GIT) reflog expire --expire=now --all
	$(GIT) gc --aggressive --prune=now --quiet

clean: ## Clean up any dev files
	rm -rf var/cache/dev/* data/dev/*
	rm -f var/log/dev-*.log

## -- Composer targets

composer.install: ## Installs vendors from composer.lock
	$(COMPOSER) install

composer.update: ## Updates vendors
	$(COMPOSER) update

composer.autoload: ## Update autoloading metadata
	$(COMPOSER) dump-autoload

## -- Cache targets

cc: ## Clear the symfony cache
	$(CONSOLE) cache:clear
	$(CONSOLE) cache:warmup

cc.purge: ## Remove cache and log files
	rm -rf var/cache/*/*
	rm -f var/log/*

## -- Assets etc.

assets: ## Link assets into /public
	$(CONSOLE) assets:install --symlink

yarn: ## Install yarn assets
	$(YARN) install

yarn.upgrade: ## Upgrade the yarn assets
	$(YARN) upgrade

sass: ## Recompile the SASS assets
	$(SASS) public/scss:public/css

sass.watch: ## Start the SASS watcher
	$(SASS) --watch public/scss:public/css

## Database cleaning

db: ## Create the database if it does not already exist
	$(CONSOLE) --env=dev doctrine:database:create --if-not-exists --quiet
	$(CONSOLE) --env=dev doctrine:schema:drop --force --quiet
	$(CONSOLE) --env=dev doctrine:schema:create --quiet
	$(CONSOLE) --env=dev doctrine:schema:validate --quiet

reset: cc.purge solr.clear ## Drop the database and recreate it with fixtures
	$(CONSOLE) doctrine:cache:clear-metadata --quiet
	$(CONSOLE) --env=dev doctrine:fixtures:load --quiet --no-interaction --group=dev --purger=fk_purger

## -- Container debug targets

dump.params: ## List all of the nines container parameters
	$(CONSOLE) debug:container --parameters | grep '^\s*nines'

dump.env: ## Show all environment variables in the container
	$(CONSOLE) debug:container --env-vars

dump.autowire: ## Show autowireable services
	$(CONSOLE) debug:autowiring nines --all

dump.twig: ## Show all twig configuration
	$(CONSOLE) debug:twig

## -- Solr search and indexing targets

solr.setup: ## Create the SOLR core for indexing
	-solr create -c nines_demo

solr.delete: ## Remove the SOLR core
	-solr delete -c nines_demo

solr.clear: ## Clear the content from the SOLR core
	$(CONSOLE) nines:solr:clear

solr.index: ## Index the content in to the SOLR core
	$(CONSOLE) nines:solr:index --clear

solr.open: ## Open the local SOLR core in a web browser
	open $(SOLR)

## -- Useful development services

mailhog.start: ## Start the email catcher
	$(BREW) services start mailhog
	open http://localhost:8025

mailhog.stop: ## Stop the email catcher
	$(BREW) services stop mailhog

## -- Test targets

test.solr.setup: ## Create a test SOLR core
	-solr create -c $(PROJECT_TEST)

test.solr.delete: ## Delete the stest SOLR core
	-solr delete -c $(PROJECT_TEST)

test.solr.clear: ## Clear the content from the test SOLR core
	$(CONSOLE) --env=test nines:solr:clear

test.solr.index: ## Index the content into the test SOLR core
	$(CONSOLE) --env=test nines:solr:index --clear

test.solr.open: ## Open the test SOLR core in a web browser
	open $(SOLR)

test.clean: ## Clean up any test files
	rm -rf var/cache/test/* data/test/*
	rm -f var/log/test-*.log

test.db: ## Create the test database if it does not already exist
	$(CONSOLE) --env=test doctrine:database:create --if-not-exists --quiet
	$(CONSOLE) --env=test doctrine:schema:drop --force --quiet
	$(CONSOLE) --env=test doctrine:schema:create --quiet
	$(CONSOLE) --env=test doctrine:schema:validate --quiet

test.reset: ## Create a test database and load the fixtures in it
	$(CONSOLE) --env=test doctrine:cache:clear-metadata --quiet
	$(CONSOLE) --env=test doctrine:fixtures:load --quiet --no-interaction --group=dev --purger=fk_purger

test.run:
	$(PHPUNIT) $(path)

test: test.clean test.reset test.run ## Run all tests. Use optional path=/path/to/tests to limit target

test.cover: test.clean test.reset ## Generate a test cover report
	$(PHP) -d zend_extension=xdebug.so -d xdebug.mode=coverage $(PHPUNIT) -c phpunit.coverage.xml $(path)
	open $(LOCAL)/dev/coverage/index.html

## -- Coding standards fixing

fix: ## Fix the code with the CS rules
	$(PHPCSF) fix $(path)

fix.cc: ## Remove the PHP CS Cache file
	rm -f var/cache/php_cs.cache

fix.all: fix.cc fix ## Ignore the CS cache and fix the code with the CS rules

fix.list: ## Check the code against the CS rules
	$(PHPCSF) fix --dry-run -v $(path)

## -- Coding standards checking

lint-all: stan.cc stan lint twiglint twigcs yamllint

symlint: yamllint twiglint ## Run the symfony linting checks
	$(SYMFONY) security:check --quiet
	$(CONSOLE) lint:container --quiet
	$(CONSOLE) doctrine:schema:validate --quiet --skip-sync -vvv --no-interaction

twiglint: ## Check the twig templates for syntax errors
	$(CONSOLE) lint:twig templates lib/Nines

twigcs: ## Check the twig templates against the coding standards
	$(TWIGCS) templates lib/Nines/*/templates

yamllint:
	$(CONSOLE) lint:yaml templates lib/Nines

stan: ## Run static analysis
	$(PHPSTAN) --memory-limit=1G analyze $(path)

stan.cc: ## Clear the static analysis cache
	$(PHPSTAN) clear-result-cache

stan.baseline: ## Generate a new phpstan baseline file
	$(PHPSTAN) analyze --generate-baseline $(path)
