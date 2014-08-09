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
