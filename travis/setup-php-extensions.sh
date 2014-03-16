#!/bin/bash
#
# @copyright (c) 2013 phpBB Group
# @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
#
set -e
set -x

function find_php_ini
{
	echo $(php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||")
}

# $1 - PHP extension name
# $2 - PHP ini file path
function register_php_extension
{
	echo "extension=$1.so" >> "$2"
}

# $1 - PHP extension name
# $2 - PHP ini file path
function install_php_extension
{
	echo "Installing $1 PHP extension"

	# See http://www.php.net/manual/en/install.pecl.phpize.php
	cd "$1"
	phpize
	./configure
	make
	make install
	cd ..

	register_php_extension "$1" "$2"
}

php_ini_file=$(find_php_ini)

# disable broken opcache on PHP 5.5.7 and 5.5.8
if [ `php -r "echo (int) version_compare(PHP_VERSION, '5.5.9', '<');"` == "1" ]
then
	sed -i '/opcache.so/d' "$php_ini_file"
fi

# apc
if [ `php -r "echo (int) version_compare(PHP_VERSION, '5.5.0-dev', '<');"` == "1" ]
then
	echo 'Enabling APC PHP extension'
	register_php_extension 'apc' "$php_ini_file"
	echo 'apc.enable_cli=1' >> "$php_ini_file"
fi

# redis
# Disabled redis for now as it causes travis to fail
# git clone git://github.com/nicolasff/phpredis.git redis
# install_php_extension 'redis' "$php_ini_file"
