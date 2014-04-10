#!/bin/bash
#
# @copyright (c) 2014 phpBB Group
# @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
#
set -e
set -x

if [ "$TRAVIS_PHP_VERSION" = 'hhvm' ]
then
	# Add PPA providing dependencies for recent HHVM on Ubuntu 12.04.
	sudo add-apt-repository -y ppa:mapnik/boost
fi

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
	# Upgrade to a recent stable version of HHVM
	sudo apt-get -o Dpkg::Options::="--force-confnew" install -y hhvm-nightly

	# MySQLi is broken in HHVM 3.0.0~precise and still does not work for us in
	# 2014.03.28~saucy, i.e. needs more work. Use MySQL extension for now.
	sed -i "s/mysqli/mysql/" "$DIR/phpunit-mysql-travis.xml"

	HHVM_LOG=$(realpath "$DIR")/hhvm.log

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
