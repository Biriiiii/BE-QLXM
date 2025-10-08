web: vendor/bin/heroku-php-apache2 public/
release: php artisan migrate --force && php artisan storage:link && chmod -R 755 storage bootstrap/cache