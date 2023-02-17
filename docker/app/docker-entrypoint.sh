#!/bin/bash
set -e

# app specific setup here
./bin/console doctrine:migrations:migrate --no-interaction --no-ansi --allow-no-migration
./bin/console assets:install --symlink --relative

apache2-foreground