SHELL=bash

help: ## This help.
	@printf "\033[32m---------------------------------------------------------------------------\n  DashFX API tech test\n---------------------------------------------------------------------------\033[0m\n"
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

.DEFAULT_GOAL := help
.PHONY: tests

# basic vars
image-name :=dfx-api-tt
php-image  :=php:8-cli
php-port   :=10101
uid        :=$(shell id -u)
gid        :=$(shell id -g)

# define our reusable docker run commands
define DOCKER_RUN_PHP
docker run -it --rm \
	--name "$(image-name)" \
	--network=host \
	-u "$(uid):$(gid)" \
	-v "$(PWD):/app" \
	-w /app \
	"$(php-image)"
endef

define DOCKER_RUN_PHP_SERVE
docker run -it --rm -d \
	--name "$(image-name)-serve" \
	-u "$(uid):$(gid)" \
	-v "$(PWD):/app" \
	-p "$(php-port):$(php-port)" \
	-w /app \
	"$(php-image)"
endef

define DOCKER_RUN_PHP_XDEBUG
docker run -it --rm \
	--name "$(image-name)-xdebug" \
	--network=host\
	-u "$(uid):$(gid)" \
	-e PHP_IDE_CONFIG="serverName=$(image-name)" \
	-v "$(PWD):/app" \
	-v "$(PWD)/xdebug.ini:/usr/local/etc/php/conf.d/xdebug.ini" \
	-w /app \
	mileschou/xdebug:8.0
endef

define DOCKER_RUN_COMPOSER
docker run --rm -it \
	--name "$(image-name)-composer" \
	-u "$(uid):$(gid)" \
	-v "$(PWD):/app" \
	-v "/tmp:/tmp" \
	-w /app \
	composer
endef

broker-up: ## Spins up a fake broker API http://localhost:10101
	@echo -en "The broker API found in scripts/dashfx-fake-api.php is now running on: http://localhost:$(php-port)\n\nThis runs in the background, to stop it run the following:\n\nmake broker-down\n\n"
	@$(DOCKER_RUN_PHP_SERVE) php -S 0.0.0.0:$(php-port) scripts/fake-broker-api.php 2>&1 >/dev/stdout

broker-down: ## stops the broker api endpoint
	docker stop $(image-name)-serve

broker-logs: ## displays the logs from the fake broker api
	@docker logs -f $(image-name)-serve

run: ## runs the DashFx integration
ifneq ("$(wildcard vendor)", "")
	@$(DOCKER_RUN_PHP) php scripts/run.php
else
	@echo -e "\nFirst run detected! No vendor/ folder found, running composer update...\n"
	make composer
	make run
endif

tests: ## runs the unit tests
ifneq ("$(wildcard vendor)", "")
	$(DOCKER_RUN_PHP) vendor/bin/phpunit --testdox
else
	@echo -e "\nFirst run detected! No vendor/ folder found, running composer update...\n"
	make composer
	make tests
endif

composer: ## Runs `composer update` on CWD, specify other commands via cmd=
ifdef cmd
	$(DOCKER_RUN_COMPOSER) $(cmd)
else
	$(DOCKER_RUN_COMPOSER) update
endif

shell: ## Launch a shell into the docker container
	$(DOCKER_RUN_PHP) /bin/bash

xdebug: ## Launch a php container with xdebug (port 10000)
	@$(DOCKER_RUN_PHP_XDEBUG) php run.php $(onlyThisDay)

xdebug-shell: ## Launch a php container with xdebug in a shell (port 10000)
	@echo -e "=== Xdebug Launch Instructions ===\nAt the prompt type:\nphp run.php [day]\n\n"
	@$(DOCKER_RUN_PHP_XDEBUG) /bin/bash

cleanup: ## remove all docker images
	docker rm $$(docker ps -a | grep '$(image-name)' | awk '{print $$1}') --force

cs-fix: ## run php-cs-fixer
	$(DOCKER_RUN_COMPOSER) cs-fixer
