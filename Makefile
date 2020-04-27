include .env

install: .env
	@docker-compose down
	@docker-compose build
	@docker-compose up -d
	@docker-compose exec service_php composer global require hirak/prestissimo
	@docker-compose exec service_php composer install

uninstall: .env
	@docker-compose down
	@docker system prune -af

restart: .env
	@docker-compose down
	@docker-compose up -d

compose: .env
	@docker-compose exec service_php composer global require hirak/prestissimo
	@docker-compose exec service_php composer install
