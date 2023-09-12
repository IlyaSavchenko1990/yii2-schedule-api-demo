#!/bin/bash

docker-compose up -d --build \
&& docker exec php composer install \
&& docker exec php yii migrate --interactive=0