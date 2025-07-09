# Variables
PHP=php
COMPOSER=COMPOSER
EXEC=docker compose exec
RUN=docker compose run --rm

# Core
setup:
	cp .config/.env.example .env
	cp .config/.htaccess.example .htaccess

install-dependencies:
	npm install
	composer install

update-dependencies:
	npm update
	composer update

update-project:
	npm update
	composer update
	./console auto-upgrade

build:
	docker compose build

build-up:
	docker compose up --build -d

up:
	docker compose up -d

down:
	docker compose down
