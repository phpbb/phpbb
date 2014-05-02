#!/bin/bash
#
# @copyright (c) 2014 phpBB Group
# @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
#
set -e
set -x

DB=$1
TRAVIS_PHP_VERSION=$2

if [ "$DB" == "mariadb" ]
then
	travis/setup-mariadb.sh
fi

if [ "$TRAVIS_PHP_VERSION" == "hhvm" ]
then
	travis/setup-php-extensions.sh
fi

if [ `php -r "echo (int) version_compare(PHP_VERSION, '5.3.19', '>=');"` == "1" ]
then
	travis/setup-webserver.sh
fi

cd phpBB
php ../composer.phar install --dev --no-interaction --prefer-source
cd ..
