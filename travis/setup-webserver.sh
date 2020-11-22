#!/bin/bash
#
# This file is part of the phpBB Forum Software package.
#
# @copyright (c) phpBB Limited <https://www.phpbb.com>
# @license GNU General Public License, version 2 (GPL-2.0)
#
# For full copyright and license information, please see
# the docs/CREDITS.txt file.
#
set -e
set -x

sudo apt-get update
sudo apt-get install -y nginx coreutils

sudo service nginx stop

DIR=$(dirname "$0")
USER=$(whoami)
PHPBB_ROOT_PATH=$(realpath "$DIR/../phpBB")
NGINX_SITE_CONF="/etc/nginx/sites-enabled/default"
NGINX_CONF="/etc/nginx/nginx.conf"
APP_SOCK=$(realpath "$DIR")/php-app.sock
NGINX_PHP_CONF="$DIR/nginx-php.conf"

# php-fpm
PHP_FPM_BIN="/usr/sbin/php-fpm$CI_PHP_VERSION"
PHP_FPM_CONF="$DIR/php-fpm.conf"

echo "
	[global]

	[ci]
	user = $USER
	group = $USER
	listen = $APP_SOCK
	listen.mode = 0666
	pm = static
	pm.max_children = 2

	php_admin_value[memory_limit] = 128M
" > $PHP_FPM_CONF

sudo $PHP_FPM_BIN \
	--fpm-config "$DIR/php-fpm.conf"

# nginx
sudo sed -i "s/user www-data;/user $USER;/g" $NGINX_CONF
sudo cp "$DIR/../phpBB/docs/nginx.sample.conf" "$NGINX_SITE_CONF"
sudo sed -i \
	-e "s/example\.com/localhost/g" \
	-e "s|root /path/to/phpbb;|root $PHPBB_ROOT_PATH;|g" \
	$NGINX_SITE_CONF

# Generate FastCGI configuration for Nginx
echo "
upstream php {
	server unix:$APP_SOCK;
}
" > $NGINX_PHP_CONF

sudo mv "$NGINX_PHP_CONF" /etc/nginx/conf.d/php.conf

sudo nginx -T
sudo service nginx start
