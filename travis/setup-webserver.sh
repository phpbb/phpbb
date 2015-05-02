#!/bin/bash
#
# @copyright (c) 2014 phpBB Group
# @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
			fastcgi_pass	unix:$APP_SOCK;
			include			fastcgi_params;
		}
	}
" | sudo tee $NGINX_CONF > /dev/null

sudo service nginx start
