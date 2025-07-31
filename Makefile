.PHONY: install update test clean setup help

# Цвета для вывода
GREEN=\033[0;32m
YELLOW=\033[1;33m
RED=\033[0;31m
NC=\033[0m # No Color

help: ## Показать справку
	@echo "$(GREEN)Доступные команды:$(NC)"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  $(YELLOW)%-15s$(NC) %s\n", $$1, $$2}'

install: ## Установить зависимости
	@echo "$(GREEN)Устанавливаем зависимости...$(NC)"
	composer install --no-dev --optimize-autoloader

install-dev: ## Установить зависимости для разработки
	@echo "$(GREEN)Устанавливаем зависимости для разработки...$(NC)"
	composer install

update: ## Обновить зависимости
	@echo "$(GREEN)Обновляем зависимости...$(NC)"
	composer update

test: ## Запустить тесты
	@echo "$(GREEN)Запускаем тесты...$(NC)"
	composer test

clean: ## Очистить кэш и временные файлы
	@echo "$(GREEN)Очищаем кэш...$(NC)"
	rm -rf vendor/
	rm -f composer.lock

setup: ## Настройка проекта
	@echo "$(GREEN)Настраиваем проект...$(NC)"
	@if [ ! -f .env ]; then \
		echo "$(YELLOW)Создаем .env файл из примера...$(NC)"; \
		cp env.example .env; \
		echo "$(GREEN)Не забудьте настроить .env файл!$(NC)"; \
	else \
		echo "$(GREEN).env файл уже существует$(NC)"; \
	fi
	composer install --no-dev --optimize-autoloader

setup-dev: ## Настройка проекта для разработки
	@echo "$(GREEN)Настраиваем проект для разработки...$(NC)"
	@if [ ! -f .env ]; then \
		echo "$(YELLOW)Создаем .env файл из примера...$(NC)"; \
		cp env.example .env; \
		echo "$(GREEN)Не забудьте настроить .env файл!$(NC)"; \
	else \
		echo "$(GREEN).env файл уже существует$(NC)"; \
	fi
	composer install

optimize: ## Оптимизировать автозагрузчик для продакшена
	@echo "$(GREEN)Оптимизируем автозагрузчик...$(NC)"
	composer dump-autoload --optimize --no-dev

check: ## Проверить код на ошибки
	@echo "$(GREEN)Проверяем код...$(NC)"
	@find . -name "*.php" -not -path "./vendor/*" -exec php -l {} \;

format: ## Форматировать код (если установлен PHP CS Fixer)
	@echo "$(GREEN)Форматируем код...$(NC)"
	@if command -v php-cs-fixer >/dev/null 2>&1; then \
		php-cs-fixer fix .; \
	else \
		echo "$(YELLOW)PHP CS Fixer не установлен. Установите: composer require --dev friendsofphp/php-cs-fixer$(NC)"; \
	fi

security: ## Проверить зависимости на уязвимости
	@echo "$(GREEN)Проверяем безопасность...$(NC)"
	@if command -v composer audit >/dev/null 2>&1; then \
		composer audit; \
	else \
		echo "$(YELLOW)Composer audit недоступен в вашей версии Composer$(NC)"; \
	fi 