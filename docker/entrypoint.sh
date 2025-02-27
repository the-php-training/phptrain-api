#!/usr/bin/env bash
service nginx start
php-fpm
printenv /var/www/.env
service nginx restart
php /var/www/artisan config:clear