#!/bin/bash

apt-get update

apt-get install -y git curl php5-cli php-pear php5-dev php5-xdebug libpcre3-dev

# Mailparse extension
pecl install mailparse
echo "extension=mailparse.so" > /etc/php5/cli/conf.d/mailparse.ini

# Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
