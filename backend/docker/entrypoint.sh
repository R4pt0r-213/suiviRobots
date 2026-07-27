#!/bin/sh
set -eu

composer install --no-interaction --prefer-dist
php bin/console doctrine:schema:update --force --no-interaction
php bin/console app:seed --no-interaction
exec php -S 0.0.0.0:8000 -t public public/index.php
