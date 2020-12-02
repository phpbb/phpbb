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

DB=$1
TRAVIS_PHP_VERSION=$2
NOTESTS=$3
MYSQL8=$4

if [ "$NOTESTS" == '1' ]
then
	travis/setup-exiftool.sh
	travis/setup-unbuffer.sh
fi

if [ "$DB" == "mariadb" ]
then
	ls -l /var/run | grep -i mysqld

	if [ ! -f "/var/run/mysqld/mysqld.sock" ]; then
		ln -s /srv/docker/sockets/mariadb/mysqld.sock /var/run/mysqld/mysqld.sock
	fi
fi

if [ "$MYSQL8" == '1' ]
then
	travis/setup-mysql8.sh
fi

if [ "$NOTESTS" != '1' ]
then
	travis/setup-webserver.sh
fi

cd phpBB
php ../composer.phar install --dev --no-interaction
if [[ "$TRAVIS_PHP_VERSION" =~ ^nightly$ || "$TRAVIS_PHP_VERSION" =~ ^8 ]]
then
	php ../composer.phar remove phpunit/dbunit --dev --update-with-dependencies \
	&& php ../composer.phar require symfony/yaml:~4.4 misantron/dbunit:~5.0 phpunit/phpunit:^9.3 --dev --update-with-all-dependencies --ignore-platform-reqs
fi
cd ..
