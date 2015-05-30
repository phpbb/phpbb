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
NGINX_CONF="/etc/nginx/sites-enabled/default"
APP_SOCK=$(realpath "$DIR")/php-app.sock

if [ "$TRAVIS_PHP_VERSION" = 'hhvm' ]
then
	HHVM_LOG=$(realpath "$DIR")/hhvm.log

    sudo service hhvm stop
	sudo hhvm \
		--mode daemon \
		--user "$USER" \
		-vServer.Type=fastcgi \
		-vServer.FileSocket="$APP_SOCK" \
		-vLog.File="$HHVM_LOG"
else
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
fi

# nginx
echo "
	server {
		listen	80;
		root	$PHPBB_ROOT_PATH/;
		index	index.php index.html;

		location ~ \.php {
			include							fastcgi_params;
			fastcgi_split_path_info			^(.+\.php)(/.*)$;
			fastcgi_param PATH_INFO			\$fastcgi_path_info;
			fastcgi_param SCRIPT_FILENAME	\$document_root\$fastcgi_script_name;
			fastcgi_pass					unix:$APP_SOCK;
		}
	}
" | sudo tee $NGINX_CONF > /dev/null

sudo service nginx start
