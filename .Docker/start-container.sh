#!/bin/sh

if [[ $XDEBUG_MODE == 'off' ]]; then
  rm /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
fi

php-fpm