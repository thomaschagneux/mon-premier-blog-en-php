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
	@echo "  install    Install project dependencies"
	@echo "  start      Start the PHP development server"
	@echo "  clean      Clean up generated files"
	@echo "  help       Display this help message"

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
