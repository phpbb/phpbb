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

if [ "$TRAVIS_PHP_VERSION" == "5.3" -a "$DB" == "mysqli" ]
then
	travis/setup-exiftool.sh
	travis/setup-unbuffer.sh
fi

if [ "$DB" == "mariadb" ]
then
	travis/setup-mariadb.sh
fi

if [ "$TRAVIS_PHP_VERSION" != "hhvm" ]
then
	travis/setup-php-extensions.sh
fi

if [ `php -r "echo (int) version_compare(PHP_VERSION, '5.3.19', '>=');"` == "1" ]
then
	travis/setup-webserver.sh
	travis/install-phpbb-test-dependencies.sh
fi

cd phpBB
php ../composer.phar install --dev --no-interaction --prefer-source
cd ..
