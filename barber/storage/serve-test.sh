#!/usr/bin/env bash
cd "$(dirname "$0")/.." || exit 1
exec php artisan serve --host=127.0.0.1 --port=8011
