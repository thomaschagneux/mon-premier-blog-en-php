# Makefile for PHP project

# Variables
PHP = php
COMPOSER = composer

# Default target
.DEFAULT_GOAL := help

# Help target to display available commands
help:
	@echo "Usage: make [target]"
	@echo
	@echo "Targets:"
	@echo "	install		Install project dependencies"
	@echo "	start		Start the PHP development server"
	@echo "	clean		Clean up generated files"
	@echo "	help		Display this help message"
	@echo "	test		Use phpStan code analyse"

# Install project dependencies
install:
	$(COMPOSER) install

# Start the PHP development server
start:
	$(PHP) -S localhost:8000 -t public

# Clean up generated files
clean:
	rm -rf vendor
	rm -rf composer.lock
test:
	vendor/bin/phpstan analyse

insert_data:
	$(PHP) scripts/insert_data.php
create_db:
	$(PHP) scripts/create_db.php
create_tables:
	$(PHP) scripts/create_tables.php
drop_db:
	$(PHP) scripts/drop_db.php

reset: drop_db create_db create_tables insert_data
	@echo "Database has been reset."