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
sudo apt-get install -y nginx realpath

sudo service nginx stop

DIR=$(dirname "$0")
USER=$(whoami)
PHPBB_ROOT_PATH=$(realpath "$DIR/../phpBB")
NGINX_SITE_CONF="/etc/nginx/sites-enabled/default"
NGINX_CONF="/etc/nginx/nginx.conf"
APP_SOCK=$(realpath "$DIR")/php-app.sock

# php-fpm
PHP_FPM_BIN="$HOME/.phpenv/versions/$TRAVIS_PHP_VERSION/sbin/php-fpm"
PHP_FPM_CONF="$DIR/php-fpm.conf"

echo "
	[global]

	[travis]
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
cat $DIR/../phpBB/docs/nginx.sample.conf \
| sed "s/root \/path\/to\/phpbb/root $(echo $PHPBB_ROOT_PATH | sed -e 's/\\/\\\\/g' -e 's/\//\\\//g' -e 's/&/\\\&/g')/g" \
| sed -e '1,/The actual board domain/d' \
| sed -e '/If running php as fastcgi/,$d' \
| sed -e "s/fastcgi_pass php;/fastcgi_pass unix:$(echo $APP_SOCK | sed -e 's/\\/\\\\/g' -e 's/\//\\\//g' -e 's/&/\\\&/g');/g" \
| sed -e 's/#listen 80/listen 80/' \
| sudo tee $NGINX_SITE_CONF
sudo sed -i "s/user www-data;/user $USER;/g" $NGINX_CONF

sudo service nginx start
