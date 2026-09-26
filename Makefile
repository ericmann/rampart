.PHONY: up down reset reset-hard test exploits logs shell mysql-shell build dist load pull

# Uses whatever image is already present (loaded from rampart-images.tar.gz, or built by
# a prior `make build`) — never forces a rebuild, so this needs no network once images
# are in place. First run on a machine with nothing built/loaded yet still builds
# normally (and needs network for that one time) since Compose builds an image that
# doesn't exist yet.
up:
	docker compose up

down:
	docker compose down

# Fast restore: puts the DB back to the exact fixture state in seconds, no image rebuild.
# Say this one out loud at the top of the workshop.
reset:
	docker compose exec app php artisan migrate:fresh --seed --force

# Nuclear option for anyone who's truly wedged: wipes volumes too. Does NOT rebuild
# images, so it stays offline-safe — run `make build` first if you actually need fresh
# images.
reset-hard:
	docker compose down -v
	docker compose up

# Stock base images the compose file needs. `pull_policy: never` on every service means
# `docker compose up` will never fetch these itself, so anything that needs network anyway
# (build/dist) pulls them explicitly first. Skips images already present.
BASE_IMAGES := mysql:8.4 redis:7-alpine
pull:
	@for img in $(BASE_IMAGES); do \
		docker image inspect $$img >/dev/null 2>&1 || docker pull $$img; \
	done

build: pull
	docker compose build

# Bundles every image this app needs (built app image, mock service, plus the stock
# mysql/redis base images) into one file for a USB stick — a room full of laptops on
# conference wifi cannot all `docker compose build`/`up` at once.
dist: build
	docker save rampart-app rampart-metadata-mock $(BASE_IMAGES) | gzip > rampart-images.tar.gz
	@echo "Wrote rampart-images.tar.gz — copy this file to distribute; load it with 'make load'."

# Loads images built with `make dist` so `docker compose up` needs no network at all.
load:
	gunzip -c rampart-images.tar.gz | docker load

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
