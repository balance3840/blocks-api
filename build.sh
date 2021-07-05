#!/bin/bash

docker-compose up -d --build --force-recreate
# Copiando variables de entorno
cp .env.example .env
echo "Instalando dependencias de Composer"
docker exec blocks-php composer install
echo "Generando clave de artisan"
docker exec blocks-php php artisan key:generate
echo "Importando la base de datos"
docker exec blocks-db mysql -u root -pabc123 blocks < blocks.sql