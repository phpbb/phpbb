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

	echo "extension=$1.so" >> "$2"
}

php_ini_file=$(find_php_ini)

# redis
git clone git://github.com/nicolasff/phpredis.git redis
install_php_extension 'redis' "$php_ini_file"
