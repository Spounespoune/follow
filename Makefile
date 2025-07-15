up:
	docker compose up -d
	symfony server:start

down:
	docker compose down
	symfony server:stop

restart:
	docker compose down
	docker compose up -d

logs:
	docker compose logs -f

clean:
	docker compose down -v

remove-images:
	docker rmi $(docker images -a -q)

status:
	docker compose ps -a

csfix:
	./vendor/bin/php-cs-fixer fix src

phpstan:
	./vendor/bin/phpstan analyse
