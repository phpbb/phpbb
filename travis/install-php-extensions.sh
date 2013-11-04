#!/bin/bash
#
# @copyright (c) 2013 phpBB Group
# @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
#
set -e

function add_ext_to_php_ini
{
	echo "extension=$1.so" >> `php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||"`
}

function load_apc
{
	echo 'Enable apc extension'
	add_ext_to_php_ini 'apc'
	echo "apc.enable_cli=1" >> `php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||"`
}

# redis
git clone git://github.com/nicolasff/phpredis.git
cd phpredis
phpize
./configure
make
make install
cd ..
echo 'Enable redis extension'
add_ext_to_php_ini 'redis'

if [ `php -r "echo (int) version_compare(PHP_VERSION, '5.5', '<');"` = "1" ]; then load_apc; fi
