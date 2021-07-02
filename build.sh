#!/bin/bash

docker-compose up -d --build --force-recreate
cp .env.example .env
docker exec -it blocks-php composer install
docker exec -it blocks-php php artisan key:generate
docker exec -it blocks-db mysql -u root -pabc123 blocks < blocks.sql