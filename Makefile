.PHONY: up down reset reset-hard test exploits logs shell mysql-shell build

up:
	docker compose up --build

down:
	docker compose down

# Fast restore: puts the DB back to the exact fixture state in seconds, no image rebuild.
# Say this one out loud at the top of the workshop.
reset:
	docker compose exec app php artisan migrate:fresh --seed --force

# Nuclear option for anyone who's truly wedged: wipes volumes too.
reset-hard:
	docker compose down -v
	docker compose up --build

build:
	docker compose build

logs:
	docker compose logs -f app

shell:
	docker compose exec app bash

mysql-shell:
	docker compose exec mysql mysql -u rampart -prampart rampart

test:
	docker compose exec app composer test

exploits:
	docker compose exec app composer test:exploits
